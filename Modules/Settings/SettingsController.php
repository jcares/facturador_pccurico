<?php
namespace Modules\Settings;

use Core\Controller;
use Core\Database;
use Core\Security;
use Core\View;
use Core\Logger;

class SettingsController extends Controller
{
    private $allowedSettings = [
         'biz_name', 'biz_rut', 'biz_address', 'biz_phone', 'biz_email',
         'biz_website', 'biz_logo', 'invoice_prefix', 'currency_symbol',
         'biz_giro', 'webpay_env', 'webpay_cc', 'webpay_key',
         'webpay_button_text', 'webpay_button_image', 'buy_order_format',
         'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure', 'smtp_from',
         'public_base_url', 'locale_country', 'locale_language', 'locale_timezone',
         'default_currency', 'default_tax_rate', 'product_default_unit',
         'email_subject_template', 'email_body_template', 'email_attach_pdf',
         'email_include_webpay_button'
     ];

    private $sections = [
        'basic' => [
            'title' => 'Configuracion Basica',
            'items' => [
                'company' => ['label' => 'Detalles de la Empresa', 'icon' => 'building-2', 'href' => 'company.php'],
                'user' => ['label' => 'Detalles de Usuario', 'icon' => 'user-cog'],
                'localization' => ['label' => 'Localizacion', 'icon' => 'globe-2', 'href' => 'localization.php'],
                'payments' => ['label' => 'Configuracion de Pagos', 'icon' => 'credit-card'],
                'transbank' => ['label' => 'Transbank', 'icon' => 'landmark', 'href' => 'settings.php?section=transbank'],
                'taxes' => ['label' => 'Configuracion de Impuestos', 'icon' => 'percent', 'href' => 'taxes.php'],
                'product' => ['label' => 'Configuracion del Producto', 'icon' => 'package', 'href' => 'product_settings.php'],
                'email' => ['label' => 'Configuracion del Correo', 'icon' => 'mail', 'href' => 'email_settings.php'],
                'email-templates' => ['label' => 'Plantillas & Recordatorios', 'icon' => 'send', 'href' => 'email_templates.php'],
            ],
        ],
        'advanced' => [
            'title' => 'Configuracion Avanzada',
            'items' => [
                'invoice-design' => ['label' => 'Diseno de factura', 'icon' => 'palette', 'href' => 'templates.php'],
                'client-portal' => ['label' => 'Portal de Cliente', 'icon' => 'monitor', 'href' => 'client_portal.php'],
                'system-logs' => ['label' => 'Registros del sistema', 'icon' => 'logs', 'href' => 'tools.php?action=log'],
                'tools' => ['label' => 'Herramientas | Importar/Exportar', 'icon' => 'tool-case', 'href' => 'tools.php?action=export'],
            ],
        ],
    ];

    private function getSettingSections(): array
    {
        return [
            [
                'key' => 'company', 'label' => 'Detalles de la Empresa',
                'icon' => 'building-2', 'color' => 'var(--primary)',
                'href' => 'settings.php?section=company',
            ],
            [
                'key' => 'localization', 'label' => 'Localización',
                'icon' => 'globe-2', 'color' => '#f59e0b',
                'href' => 'settings.php?section=localization',
            ],
            [
                'key' => 'transbank', 'label' => 'Pagos (Webpay)',
                'icon' => 'credit-card', 'color' => '#e0245e',
                'href' => 'settings.php?section=transbank',
            ],
            [
                'key' => 'taxes', 'label' => 'Impuestos',
                'icon' => 'percent', 'color' => '#10b981',
                'href' => 'settings.php?section=taxes',
            ],
            [
                'key' => 'product', 'label' => 'Configuracion del Producto',
                'icon' => 'package', 'color' => '#3b82f6',
                'href' => 'settings.php?section=product',
            ],
            [
                'key' => 'email', 'label' => 'Configuración de Correo',
                'icon' => 'mail', 'color' => '#22d3ee',
                'href' => 'settings.php?section=email',
            ],
            [
                'key' => 'email-templates', 'label' => 'Plantillas & Recordatorios',
                'icon' => 'send', 'color' => '#a78bfa',
                'href' => 'settings.php?section=email-templates',
            ],
            [
                'key' => 'invoice-design', 'label' => 'Diseño de Factura',
                'icon' => 'palette', 'color' => '#ec4899',
                'href' => 'templates.php',
            ],
            [
                'key' => 'system-logs', 'label' => 'Registros del Sistema',
                'icon' => 'scroll', 'color' => '#ef4444',
                'href' => 'tools.php?action=log',
            ],
            [
                'key' => 'tools', 'label' => 'Herramientas',
                'icon' => 'tool', 'color' => '#14b8a6',
                'href' => 'tools.php',
            ],
            [
                'key' => 'client-portal', 'label' => 'Portal de Cliente',
                'icon' => 'monitor', 'color' => '#f97316',
                'href' => 'client_portal.php',
            ],
        ];
    }

    public function index()
    {
        $section = Security::cleanString($_GET['section'] ?? 'company', 60);

        if ($section === '' || $section === 'settings') {
            $section = 'company';
        }

        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM settings");
        $settingsRaw = $stmt->fetchAll();
        $settings = [];
        foreach ($settingsRaw as $s) {
            $settings[$s['key']] = $s['value'];
        }

        View::renderSettings('settings/index', [
            'title' => $this->sectionLabel($section),
            'settings' => $settings,
            'section' => $section,
            'sections' => $this->sections,
            'sectionMeta' => $this->sectionMeta($section)
        ]);
    }

    public function test_email()
    {
        $testTo = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$testTo) {
            echo json_encode(['success' => false, 'message' => 'Email invalido.']);
            return;
        }

        try {
            $settings = \Core\Config::getAll();
            $subject = "Prueba de configuracion - " . ($settings['biz_name'] ?? 'Facturador');
            $body = "<h2>Prueba de Correo Exitosa</h2><p>Tu configuracion SMTP funciona correctamente.</p>";
            
            $result = \Core\Mailer::sendReminder($testTo, $subject, $body);
            echo json_encode(['success' => $result, 'message' => $result ? 'Correo enviado con exito.' : 'Error al enviar correo.']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function test_transbank()
    {
        header('Content-Type: application/json');
        try {
            $settings = \Core\Config::getAll();
            $tbk = new \Core\TransbankService();
            $status = $tbk->checkHealth();
            echo json_encode(['success' => true, 'message' => 'Conexion con Transbank exitosa.']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo no permitido.';
            return;
        }

        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            Logger::error("Settings CSRF Validation Failed");
            echo 'Token de seguridad invalido.';
            return;
        }

        $db = Database::getInstance();
        $section = Security::cleanString($_POST['section'] ?? 'company', 60);

        try {
            $db->beginTransaction();

            if (isset($_FILES['biz_logo']) && $_FILES['biz_logo']['error'] === UPLOAD_ERR_OK) {
                $this->handleLogoUpload($db);
            }

            if (isset($_FILES['webpay_button_image']) && $_FILES['webpay_button_image']['error'] === UPLOAD_ERR_OK) {
                $this->handleImageUpload($db, 'webpay_button_image', $_FILES['webpay_button_image']);
            }

            $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

            foreach ($this->allowedSettings as $key) {
                if (isset($_POST[$key]) && $key !== 'biz_logo' && $key !== 'webpay_button_image') {
                    $value = trim((string)$_POST[$key]);

                    switch ($key) {
                        case 'biz_email':
                        case 'smtp_from':
                            if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                throw new \Exception('Email invalido.');
                            }
                            break;
                        case 'biz_phone':
                            $value = preg_replace('/[^0-9+\-\s]/', '', $value);
                            break;
                        case 'webpay_env':
                            $value = in_array($value, ['integration', 'production'], true) ? $value : 'integration';
                            break;
                        case 'email_attach_pdf':
                        case 'email_include_webpay_button':
                            $value = $value === '1' ? '1' : '0';
                            break;
                    }

                    $stmt->execute([$key, $value]);
                }
            }

            foreach (['email_attach_pdf', 'email_include_webpay_button'] as $checkboxKey) {
                if ($section === 'email-templates' && !isset($_POST[$checkboxKey]) && in_array($checkboxKey, $this->allowedSettings, true)) {
                    $stmt->execute([$checkboxKey, '0']);
                }
            }

            $db->commit();
            $this->redirect('settings.php?section=' . urlencode($section) . '&success=1');
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Logger::error("Settings Update Failed: " . $e->getMessage());
            $this->redirect('settings.php?section=' . urlencode($section) . '&error=1');
        }
    }

    private function handleLogoUpload($db)
    {
        $file = $_FILES['biz_logo'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \Exception('Tipo de archivo no permitido para logo.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            throw new \Exception('Extension de archivo no permitida.');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \Exception('El archivo supera 5MB.');
        }

        $uploadDir = \ROOT_PATH . '/public/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'logo_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            chmod($destPath, 0644);
            $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES ('biz_logo', ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
            $stmt->execute([$fileName]);
        } else {
            throw new \Exception('Error al guardar el archivo de logo.');
        }
    }

    private function handleImageUpload($db, $settingKey, $file)
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \Exception('Tipo de archivo no permitido.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            throw new \Exception('Extension de archivo no permitida.');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \Exception('El archivo supera 5MB.');
        }

        $uploadDir = \ROOT_PATH . '/public/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = $settingKey . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            chmod($destPath, 0644);
            $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
            $stmt->execute([$settingKey, $fileName]);
        } else {
            throw new \Exception('Error al guardar el archivo.');
        }
    }

    private function sectionLabel($section)
    {
        $meta = $this->sectionMeta($section);
        return ($meta['label'] ?? 'Configuracion') . ' | Configuracion';
    }

    private function sectionMeta($section)
    {
        foreach ($this->sections as $group) {
            if (isset($group['items'][$section])) {
                return $group['items'][$section];
            }
        }

        return $this->sections['basic']['items']['company'];
    }
}