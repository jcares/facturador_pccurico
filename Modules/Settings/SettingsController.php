<?php
namespace Modules\Settings;

use Core\Controller;
use Core\Database;
use Core\Security;
use Core\Logger;

class SettingsController extends Controller
{
    public function index()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM settings");
        $settingsRaw = $stmt->fetchAll();
        $settings = [];
        foreach($settingsRaw as $s) {
            $settings[$s['key']] = $s['value'];
        }

        $this->view('settings/index', [
            'title' => 'Configuración del Sistema',
            'settings' => $settings
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance();
            
            // Handle File Upload for Logo
            if (isset($_FILES['biz_logo']) && $_FILES['biz_logo']['error'] === UPLOAD_ERR_OK) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($_FILES['biz_logo']['type'], $allowedMimeTypes)) {
                    $uploadDir = \ROOT_PATH . '/public/uploads/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $fileName = 'logo_' . time() . '.' . pathinfo($_FILES['biz_logo']['name'], PATHINFO_EXTENSION);
                    $destPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['biz_logo']['tmp_name'], $destPath)) {
                        $_POST['biz_logo'] = $fileName;
                    }
                }
            }

            try {
                $db->beginTransaction();
                $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
                
                foreach ($_POST as $key => $value) {
                    if ($key === 'csrf_token') continue;
                    $stmt->execute([$key, $value]);
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
    }
}
