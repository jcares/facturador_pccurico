<?php
namespace Modules\Templates;

use Core\Controller;
use Core\Database;
use Core\View;
use Core\Logger;
use Core\Security;

class TemplateController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM document_templates ORDER BY type ASC, is_default DESC, name ASC");
        $templates = $stmt->fetchAll();

        $templates = array_map(function ($t) {
            $t['name'] = htmlspecialchars($t['name']);
            $t['type'] = htmlspecialchars($t['type']);
            return $t;
        }, $templates);

        View::render('templates/index', ['templates' => $templates, 'title' => 'Diseno de factura']);
    }

    public function visualEdit()
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(404);
            echo 'ID de plantilla invalido.';
            exit;
        }

        $stmt = $this->db->prepare("SELECT * FROM document_templates WHERE id = ?");
        $stmt->execute([$id]);
        $template = $stmt->fetch();

        if (!$template) {
            http_response_code(404);
            echo 'Plantilla no encontrada.';
            exit;
        }

        $config = json_decode($template['config_json'] ?? '{}', true) ?: [];
        $blocks = $this->ensureWebpayBlock($config['blocks'] ?? $this->defaultBlocks());
        $styles = $config['styles'] ?? ['primary_color' => '#3b82f6', 'font_family' => 'sans-serif'];

        ob_start();
        include __DIR__ . '/../../templates/templates/visual_edit.php';
        $content = ob_get_clean();

        View::renderRaw($content, ['title' => 'Editor Visual de Plantilla']);
    }

    public function previewVisual()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo no permitido.';
            return;
        }

        $blocks = $_POST['blocks'] ?? [];
        if (!is_array($blocks)) {
            http_response_code(400);
            echo 'Datos invalidos.';
            return;
        }

        foreach ($blocks as &$block) {
            $block['enabled'] = isset($block['enabled']);
            $block['id'] = htmlspecialchars($block['id'] ?? '');
            $block['label'] = htmlspecialchars($block['label'] ?? '');
        }

        $primaryColor = $_POST['primary_color'] ?? '#3b82f6';
        if (!preg_match('/^#[0-9a-f]{6}$/i', $primaryColor)) {
            $primaryColor = '#3b82f6';
        }

        $styles = [
            'primary_color' => $primaryColor,
            'font_family' => htmlspecialchars($_POST['font_family'] ?? 'sans-serif')
        ];

        $config = ['blocks' => $blocks, 'styles' => $styles];

        // Load real settings from DB so webpay image/text are available
        $realSettings = \Core\Config::getAll();

        $data = [
            'settings' => array_merge([
                'biz_name' => 'Comercializadora Demo SPA',
                'biz_rut' => '76.123.456-7',
                'biz_address' => 'Av. Providencia 1234, Santiago',
                'biz_logo' => ''
            ], $realSettings),
            'client' => [
                'name' => 'Juan Perez y Cia Ltda.',
                'rut' => '77.888.999-0',
                'address' => 'Calle Falsa 123, Valparaiso'
            ],
            'invoice' => [
                'number' => 10042,
                'created_at' => date('Y-m-d'),
                'subtotal' => 50000,
                'tax' => 9500,
                'total' => 59500,
                'notes' => "Gracias por su compra.\nCondiciones de pago a 30 dias.",
                'public_url' => 'https://facturador.pccurico.cl/view.php?token=demo'
            ],
            'items' => [
                ['sku' => 'ITM01', 'name' => 'Servicio de Consultoria', 'quantity' => 1, 'price' => 30000, 'subtotal' => 30000],
                ['sku' => 'ITM02', 'name' => 'Licencia de Software', 'quantity' => 2, 'price' => 10000, 'subtotal' => 20000],
            ]
        ];

        require_once __DIR__ . '/../../Core/VisualTemplateRenderer.php';
        echo \Core\VisualTemplateRenderer::render($config, $data);
    }

    public function saveVisual()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo no permitido.';
            return;
        }

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(404);
            echo 'ID de plantilla invalido.';
            exit;
        }

        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            Logger::error("Template CSRF Validation Failed");
            echo 'Token de seguridad invalido.';
            return;
        }

        $blocks = $_POST['blocks'] ?? [];
        if (!is_array($blocks)) {
            http_response_code(400);
            echo 'Datos invalidos.';
            return;
        }

        foreach ($blocks as &$block) {
            $block['enabled'] = isset($block['enabled']);
            $block['id'] = htmlspecialchars($block['id'] ?? '');
            $block['label'] = htmlspecialchars($block['label'] ?? '');
        }

        $primaryColor = $_POST['primary_color'] ?? '#3b82f6';
        if (!preg_match('/^#[0-9a-f]{6}$/i', $primaryColor)) {
            $primaryColor = '#3b82f6';
        }

        $styles = [
            'primary_color' => $primaryColor,
            'font_family' => htmlspecialchars($_POST['font_family'] ?? 'sans-serif')
        ];

        $config = ['blocks' => $blocks, 'styles' => $styles];
        $json = json_encode($config);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            Logger::error("Template JSON Encoding Failed: " . json_last_error_msg());
            echo 'Error al guardar la configuracion.';
            return;
        }

        try {
            $stmt = $this->db->prepare("UPDATE document_templates SET config_json = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$json, $id]);
            header('Location: templates.php?msg=Plantilla+Guardada');
            exit;
        } catch (\Exception $e) {
            Logger::error("Template Save Failed: " . $e->getMessage());
            http_response_code(500);
            echo 'Error al guardar la plantilla.';
            exit;
        }
    }

    private function defaultBlocks(): array
    {
        return [
            ['id' => 'company', 'label' => 'Datos de Empresa', 'position' => 1, 'enabled' => true, 'options' => ['show_logo' => true, 'show_rut' => true, 'logo_width' => 150, 'logo_x' => 0, 'logo_y' => 0]],
            ['id' => 'client', 'label' => 'Datos del Cliente', 'position' => 2, 'enabled' => true, 'options' => []],
            ['id' => 'header', 'label' => 'Titulo del Documento', 'position' => 3, 'enabled' => true, 'options' => ['show_number' => true, 'show_date' => true]],
            ['id' => 'items', 'label' => 'Tabla de Items', 'position' => 4, 'enabled' => true, 'options' => ['show_sku' => true, 'show_tax' => true]],
            ['id' => 'webpay_payment', 'label' => 'Pago Webpay', 'position' => 5, 'enabled' => false, 'options' => ['text' => 'Pagar con Webpay Plus', 'button_width' => 200]],
            ['id' => 'notes', 'label' => 'Notas Adicionales', 'position' => 6, 'enabled' => true, 'options' => ['text' => '']],
            ['id' => 'footer', 'label' => 'Pie de Pagina', 'position' => 7, 'enabled' => true, 'options' => ['text' => '']],
        ];
    }

    private function ensureWebpayBlock(array $blocks): array
    {
        foreach ($blocks as $block) {
            if (($block['id'] ?? '') === 'webpay_payment') {
                return $blocks;
            }
        }

        $blocks[] = [
            'id' => 'webpay_payment',
            'label' => 'Pago Webpay',
            'position' => count($blocks) + 1,
            'enabled' => false,
            'options' => ['text' => 'Pagar con Webpay Plus']
        ];

        return $blocks;
    }
}
