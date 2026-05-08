<?php
namespace Modules\Products;

use Core\Controller;
use Core\Security;
use Modules\Categories\Category;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $categories = Category::all();
        $rates = ['CLP' => 1.0, 'USD' => 1.0, 'UF' => 1.0];
        try {
            $rates = array_merge($rates, \Core\CurrencyService::getRates());
        } catch (\Exception $e) {
            \Core\Logger::error("Product Rates Load Failed: " . $e->getMessage());
        }
        $editing = isset($_GET['edit']) ? Product::find((int)$_GET['edit']) : null;

        $this->view('products/index', [
            'title' => 'Gestion de Productos',
            'products' => $products,
            'categories' => $categories,
            'rates' => $rates,
            'editing' => $editing
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = Security::cleanString($_POST['name'] ?? '', 255);
            $sku = Security::cleanString($_POST['sku'] ?? '', 100);
            $price = Security::cleanDecimal($_POST['price'] ?? 0);
            $currency = Security::cleanString($_POST['currency'] ?? 'CLP', 10);
            $currency = strtoupper($currency);
            if (!in_array($currency, ['CLP', 'USD', 'UF'], true)) {
                $currency = 'CLP';
            }
            $categoryId = !empty($_POST['category_id']) ? Security::cleanInt($_POST['category_id']) : null;
            $priceUnit = Security::cleanString($_POST['price_unit'] ?? 'unit', 20);
            if (!in_array($priceUnit, ['unit', 'meter'], true)) {
                $priceUnit = 'unit';
            }
            $taxRate = Security::cleanDecimal($_POST['tax_rate'] ?? 0.19, 0.19);
            $stock = Security::cleanDecimal($_POST['stock'] ?? 0);

            if (empty($name) || $price <= 0) {
                $this->redirect('products.php?error=invalid_data');
            }

            try {
                Product::create([
                    'name' => $name,
                    'sku' => $sku ?: null,
                    'price' => $price,
                    'currency' => $currency,
                    'category_id' => $categoryId,
                    'price_unit' => $priceUnit,
                    'tax_rate' => $taxRate,
                    'stock' => $stock
                ]);
                $this->redirect('products.php?success=created');
            } catch (\Exception $e) {
                \Core\Logger::error("Product Creation Failed: " . $e->getMessage());
                $this->redirect('products.php?error=db_error');
            }
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = Security::cleanInt($_POST['id'] ?? 0);
            $name = Security::cleanString($_POST['name'] ?? '', 255);
            $sku = Security::cleanString($_POST['sku'] ?? '', 100);
            $price = Security::cleanDecimal($_POST['price'] ?? 0);
            $currency = Security::cleanString($_POST['currency'] ?? 'CLP', 10);
            $currency = strtoupper($currency);
            if (!in_array($currency, ['CLP', 'USD', 'UF'], true)) {
                $currency = 'CLP';
            }
            $categoryId = !empty($_POST['category_id']) ? Security::cleanInt($_POST['category_id']) : null;
            $priceUnit = Security::cleanString($_POST['price_unit'] ?? 'unit', 20);
            if (!in_array($priceUnit, ['unit', 'meter'], true)) {
                $priceUnit = 'unit';
            }
            $taxRate = Security::cleanDecimal($_POST['tax_rate'] ?? 0.19, 0.19);
            $stock = Security::cleanDecimal($_POST['stock'] ?? 0);

            if ($id <= 0 || empty($name) || $price <= 0) {
                $this->redirect('products.php?error=invalid_data');
            }

            try {
                Product::update($id, [
                    'name' => $name,
                    'sku' => $sku ?: null,
                    'price' => $price,
                    'currency' => $currency,
                    'category_id' => $categoryId,
                    'price_unit' => $priceUnit,
                    'tax_rate' => $taxRate,
                    'stock' => $stock
                ]);
                $this->redirect('products.php?success=updated');
            } catch (\Exception $e) {
                \Core\Logger::error("Product Update Failed: " . $e->getMessage());
                $this->redirect('products.php?error=db_error');
            }
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo no permitido.';
            return;
        }

        $id = Security::cleanInt($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->redirect('products.php?error=invalid_id');
        }

        try {
            Product::delete($id);
            $this->redirect('products.php?success=deleted');
        } catch (\Exception $e) {
            \Core\Logger::error("Product Delete Failed: " . $e->getMessage());
            $this->redirect('products.php?error=delete_failed');
        }
    }
}
