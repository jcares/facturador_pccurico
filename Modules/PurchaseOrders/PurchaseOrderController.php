<?php
namespace Modules\PurchaseOrders;

use Core\Controller;
use Core\Security;
use Core\Auth;
use Modules\PurchaseOrders\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::all();
        $this->view('purchase_orders/index', [
            'title' => 'Órdenes de Compra',
            'orders' => $orders
        ]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'supplier_name' => Security::cleanString($_POST['supplier_name'] ?? ''),
                'supplier_rut' => Security::cleanString($_POST['supplier_rut'] ?? ''),
                'supplier_email' => Security::cleanString($_POST['supplier_email'] ?? ''),
                'supplier_phone' => Security::cleanString($_POST['supplier_phone'] ?? ''),
                'supplier_address' => Security::cleanString($_POST['supplier_address'] ?? ''),
                'number' => Security::cleanString($_POST['number'] ?? ''),
                'status' => Security::cleanString($_POST['status'] ?? 'draft'),
                'subtotal' => Security::cleanFloat($_POST['subtotal'] ?? 0),
                'tax' => Security::cleanFloat($_POST['tax'] ?? 0),
                'total' => Security::cleanFloat($_POST['total'] ?? 0),
                'currency' => Security::cleanString($_POST['currency'] ?? 'CLP'),
                'exchange_rate' => Security::cleanFloat($_POST['exchange_rate'] ?? 1),
                'due_date' => !empty($_POST['due_date']) ? Security::cleanString($_POST['due_date']) : null,
                'notes' => Security::cleanString($_POST['notes'] ?? ''),
                'created_by' => Auth::id()
            ];

            if (empty($data['supplier_name']) || empty($data['number'])) {
                $this->redirect('purchase_orders.php?error=empty_fields');
            }

            $id = PurchaseOrder::create($data);
            
            // Add items if provided
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $item) {
                    PurchaseOrder::addItem($id, [
                        'product_name' => Security::cleanString($item['product_name'] ?? ''),
                        'description' => Security::cleanString($item['description'] ?? ''),
                        'qty' => Security::cleanFloat($item['qty'] ?? 0),
                        'price' => Security::cleanFloat($item['price'] ?? 0),
                        'tax_rate' => Security::cleanFloat($item['tax_rate'] ?? 0.19),
                        'total' => Security::cleanFloat($item['total'] ?? 0)
                    ]);
                }
            }

            $this->redirect('purchase_orders.php?success=created');
        }

        $this->view('purchase_orders/create', [
            'title' => 'Crear Orden de Compra'
        ]);
    }

    public function edit($id = null)
    {
        $id = Security::cleanInt($id ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            $this->redirect('purchase_orders.php?error=invalid_id');
        }

        $order = PurchaseOrder::find($id);
        if (!$order) {
            $this->redirect('purchase_orders.php?error=not_found');
        }

        $items = PurchaseOrder::getItems($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'supplier_name' => Security::cleanString($_POST['supplier_name'] ?? ''),
                'supplier_rut' => Security::cleanString($_POST['supplier_rut'] ?? ''),
                'supplier_email' => Security::cleanString($_POST['supplier_email'] ?? ''),
                'supplier_phone' => Security::cleanString($_POST['supplier_phone'] ?? ''),
                'supplier_address' => Security::cleanString($_POST['supplier_address'] ?? ''),
                'number' => Security::cleanString($_POST['number'] ?? ''),
                'status' => Security::cleanString($_POST['status'] ?? 'draft'),
                'subtotal' => Security::cleanFloat($_POST['subtotal'] ?? 0),
                'tax' => Security::cleanFloat($_POST['tax'] ?? 0),
                'total' => Security::cleanFloat($_POST['total'] ?? 0),
                'currency' => Security::cleanString($_POST['currency'] ?? 'CLP'),
                'exchange_rate' => Security::cleanFloat($_POST['exchange_rate'] ?? 1),
                'due_date' => !empty($_POST['due_date']) ? Security::cleanString($_POST['due_date']) : null,
                'notes' => Security::cleanString($_POST['notes'] ?? '')
            ];

            if (empty($data['supplier_name']) || empty($data['number'])) {
                $this->redirect('purchase_orders.php?error=empty_fields');
            }

            PurchaseOrder::update($id, $data);
            $this->redirect('purchase_orders.php?success=updated');
        }

        $this->view('purchase_orders/edit', [
            'title' => 'Editar Orden de Compra',
            'order' => $order,
            'items' => $items
        ]);
    }

    public function show($id = null)
    {
        $id = Security::cleanInt($id ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            $this->redirect('purchase_orders.php?error=invalid_id');
        }

        $order = PurchaseOrder::find($id);
        if (!$order) {
            $this->redirect('purchase_orders.php?error=not_found');
        }

        $items = PurchaseOrder::getItems($id);

        $this->view('purchase_orders/show', [
            'title' => 'Ver Orden de Compra',
            'order' => $order,
            'items' => $items
        ]);
    }

    public function delete()
    {
        $id = Security::cleanInt($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('purchase_orders.php?error=invalid_id');
        }

        PurchaseOrder::delete($id);
        $this->redirect('purchase_orders.php?success=deleted');
    }
}