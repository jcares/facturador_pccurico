<?php
namespace Modules\Clients;

use Core\Controller;
use Core\Security;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        $editing = null;
        if (isset($_GET['edit'])) {
            $editing = Client::find((int)$_GET['edit']);
        }

        $this->view('clients/index', [
            'title' => 'Directorio de Clientes',
            'clients' => $clients,
            'editing' => $editing
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rut = Security::cleanString($_POST['rut'] ?? '', 20);
            $businessName = Security::cleanString($_POST['business_name'] ?? '', 255);
            $contactName = Security::cleanString($_POST['contact_name'] ?? '', 255);
            $email = Security::cleanEmail($_POST['email'] ?? '') ?: '';
            $phone = Security::cleanString($_POST['phone'] ?? '', 50);
            $address = Security::cleanString($_POST['address'] ?? '', 500);

            if (empty($rut) || empty($businessName)) {
                $this->redirect('clients.php?error=missing_fields');
            }

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->redirect('clients.php?error=invalid_email');
            }

            try {
                Client::create([
                    'business_name' => $businessName,
                    'contact_name' => $contactName ?: null,
                    'rut' => $rut,
                    'email' => $email ?: null,
                    'phone' => $phone ?: null,
                    'address' => $address ?: null
                ]);
                $this->redirect('clients.php?success=created');
            } catch (\Exception $e) {
                \Core\Logger::error("Client Creation Failed: " . $e->getMessage());
                $this->redirect('clients.php?error=db_error');
            }
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = Security::cleanInt($_POST['id'] ?? 0);
            $rut = Security::cleanString($_POST['rut'] ?? '', 20);
            $businessName = Security::cleanString($_POST['business_name'] ?? '', 255);
            $contactName = Security::cleanString($_POST['contact_name'] ?? '', 255);
            $email = Security::cleanEmail($_POST['email'] ?? '') ?: '';
            $phone = Security::cleanString($_POST['phone'] ?? '', 50);
            $address = Security::cleanString($_POST['address'] ?? '', 500);

            if ($id <= 0 || empty($rut) || empty($businessName)) {
                $this->redirect('clients.php?error=missing_fields');
            }

            try {
                Client::update($id, [
                    'business_name' => $businessName,
                    'contact_name' => $contactName ?: null,
                    'rut' => $rut,
                    'email' => $email ?: null,
                    'phone' => $phone ?: null,
                    'address' => $address ?: null
                ]);
                $this->redirect('clients.php?success=updated');
            } catch (\Exception $e) {
                \Core\Logger::error("Client Update Failed: " . $e->getMessage());
                $this->redirect('clients.php?error=db_error');
            }
        }
    }

    public function delete()
    {
        $id = Security::cleanInt($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            $this->redirect('clients.php?error=invalid_id');
        }

        try {
            Client::delete($id);
            $this->redirect('clients.php?success=deleted');
        } catch (\Exception $e) {
            \Core\Logger::error("Client Delete Failed: " . $e->getMessage());
            $this->redirect('clients.php?error=db_error');
        }
    }
}
