<?php
namespace Modules\Settings;

use Core\Controller;
use Core\Database;
use Core\Security;
use Core\Logger;
use Core\Validator;

class SettingsController extends Controller
{
    private $allowedSettings = [
        'biz_name', 'biz_rut', 'biz_address', 'biz_phone', 'biz_email', 
        'biz_website', 'biz_logo', 'invoice_prefix', 'currency_symbol',
        'biz_giro', 'webpay_env', 'webpay_cc', 'webpay_key',
        'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure', 'smtp_from',
        'public_base_url'
    ];

    public function index()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM settings");
        $settingsRaw = $stmt->fetchAll();
        $settings = [];
        foreach($settingsRaw as $s) {
            $settings[$s['key']] = htmlspecialchars($s['value']);
        }

        $this->view('settings/index', [
            'title' => 'Configuración del Sistema',
            'settings' => $settings
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método no permitido.';
            return;
        }

        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            Logger::error("Settings CSRF Validation Failed");
            echo 'Token de seguridad inválido.';
            return;
        }

        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            if (isset($_FILES['biz_logo']) && $_FILES['biz_logo']['error'] === UPLOAD_ERR_OK) {
                $this->handleLogoUpload($db);
            }

            $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
            
            foreach ($this->allowedSettings as $key) {
                if (isset($_POST[$key]) && $key !== 'biz_logo') {
                    $value = trim($_POST[$key]);
                    
                    switch ($key) {
                        case 'biz_email':
                            if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                throw new \Exception('Email inválido.');
                            }
                            break;
                        case 'biz_phone':
                            $value = preg_replace('/[^0-9+\-\s]/', '', $value);
                            break;
                    }
                    
                    $value = htmlspecialchars($value);
                    $stmt->execute([$key, $value]);
                }
            }
            
            $db->commit();
            $this->redirect('settings.php?success=1');
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Logger::error("Settings Update Failed: " . $e->getMessage());
            $this->redirect('settings.php?error=1');
        }
    }

    private function handleLogoUpload($db)
    {
        $file = $_FILES['biz_logo'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new \Exception('Tipo de archivo no permitido para logo.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            throw new \Exception('Extensión de archivo no permitida.');
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
            $_POST['biz_logo'] = $fileName;
            chmod($destPath, 0644);
        } else {
            throw new \Exception('Error al guardar el archivo de logo.');
        }
    }
}
