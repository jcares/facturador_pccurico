<?php
namespace Modules\Clients;

use Core\Controller;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        $this->view('clients/index', [
            'title' => 'Directorio de Clientes',
            'clients' => $clients
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                Client::create($_POST);
            } catch (\Exception $e) {
                // Handle duplicate RUT or other DB errors silently for now, or redirect with error
            }
            $this->redirect('clients.php');
        }
    }
}
