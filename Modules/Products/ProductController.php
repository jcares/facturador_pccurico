<?php
namespace Modules\Products;

use Core\Controller;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $db = \Core\Database::getInstance();
        $categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
        
        $this->view('products/index', [
            'title' => 'Gestión de Productos',
            'products' => $products,
            'categories' => $categories
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Product::create($_POST);
            $this->redirect('products.php');
        }
    }
}
