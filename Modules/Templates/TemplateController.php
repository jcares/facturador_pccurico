<?php
namespace Modules\Templates;

use Core\Controller;
use Core\Database;
use Core\View;

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
        View::render('templates/index', ['templates' => $templates, 'title' => 'Gestión de Plantillas']);
    }

    public function visualEdit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: templates.php');
            exit;
        }

        $stmt = $this->db->prepare("SELECT * FROM document_templates WHERE id = ?");
        $stmt->execute([$id]);
        $template = $stmt->fetch();

        if (!$template) {
            header('Location: templates.php');
            exit;
        }

        // Parse config or generate default
        $config = json_decode($template['config_json'] ?? '{}', true) ?: [];
        $blocks = $config['blocks'] ?? [
            ['id' => 'company', 'label' => 'Datos de Empresa', 'position' => 1, 'enabled' => true, 'options' => ['show_logo' => true, 'show_rut' => true, 'logo_width' => 150, 'logo_x' => 0, 'logo_y' => 0]],
            ['id' => 'client', 'label' => 'Datos del Cliente', 'position' => 2, 'enabled' => true, 'options' => []],
            ['id' => 'header', 'label' => 'Título del Documento', 'position' => 3, 'enabled' => true, 'options' => ['show_number' => true, 'show_date' => true]],
            ['id' => 'items', 'label' => 'Tabla de Ítems', 'position' => 4, 'enabled' => true, 'options' => ['show_sku' => true, 'show_tax' => true]],
            ['id' => 'notes', 'label' => 'Notas Adicionales', 'position' => 5, 'enabled' => true, 'options' => ['text' => '']],
            ['id' => 'footer', 'label' => 'Pie de Página', 'position' => 6, 'enabled' => true, 'options' => ['text' => '']],
        ];
        $styles = $config['styles'] ?? ['primary_color' => '#3b82f6', 'font_family' => 'sans-serif'];

        // Include the actual view raw without the dashboard layout
        ob_start();
        include __DIR__ . '/../../templates/templates/visual_edit.php';
        $content = ob_get_clean();
        echo $content;
    }

    public function previewVisual()
    {
        $blocks = $_POST['blocks'] ?? [];
        foreach ($blocks as &$block) {
            $block['enabled'] = isset($block['enabled']);
        }
        $styles = [
            'primary_color' => $_POST['primary_color'] ?? '#3b82f6',
            'font_family' => $_POST['font_family'] ?? 'sans-serif'
        ];

        $config = ['blocks' => $blocks, 'styles' => $styles];

        // Mock data for preview
        $data = [
            'settings' => [
                'biz_name' => 'Comercializadora Demo SPA',
                'biz_rut' => '76.123.456-7',
                'biz_address' => 'Av. Providencia 1234, Santiago',
                'biz_logo' => '' // Empty triggers fallback to assets/img/logo.png
            ],
            'client' => [
                'name' => 'Juan Pérez y Cía Ltda.',
                'rut' => '77.888.999-0',
                'address' => 'Calle Falsa 123, Valparaíso'
            ],
            'invoice' => [
                'number' => 10042,
                'created_at' => date('Y-m-d'),
                'subtotal' => 50000,
                'tax' => 9500,
                'total' => 59500,
                'notes' => "Gracias por su compra.\nCondiciones de pago a 30 días."
            ],
            'items' => [
                ['sku' => 'ITM01', 'name' => 'Servicio de Consultoría', 'quantity' => 1, 'price' => 30000, 'subtotal' => 30000],
                ['sku' => 'ITM02', 'name' => 'Licencia de Software', 'quantity' => 2, 'price' => 10000, 'subtotal' => 20000],
            ]
        ];

        require_once __DIR__ . '/../../Core/VisualTemplateRenderer.php';
        echo \Core\VisualTemplateRenderer::render($config, $data);
    }

    public function saveVisual()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: templates.php');
            exit;
        }

        $blocks = $_POST['blocks'] ?? [];
        foreach ($blocks as &$block) {
            $block['enabled'] = isset($block['enabled']);
        }
        $styles = [
            'primary_color' => $_POST['primary_color'] ?? '#3b82f6',
            'font_family' => $_POST['font_family'] ?? 'sans-serif'
        ];

        $config = ['blocks' => $blocks, 'styles' => $styles];
        $json = json_encode($config);

        $stmt = $this->db->prepare("UPDATE document_templates SET config_json = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$json, $id]);

        header('Location: templates.php?msg=Plantilla+Guardada');
        exit;
    }
}
