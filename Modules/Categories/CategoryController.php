<?php
namespace Modules\Categories;

use Core\Controller;
use Core\Security;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::getWithProductCount();
        $parentCategories = Category::parents();
        $editing = isset($_GET['edit']) ? Category::find((int)$_GET['edit']) : null;

        $this->view('categories/index', [
            'title' => 'Gestion de Categorias',
            'categories' => $categories,
            'parentCategories' => $parentCategories,
            'editing' => $editing
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo no permitido.';
            return;
        }

        $name = Security::cleanString($_POST['name'] ?? '', 255);
        $parentId = !empty($_POST['parent_id']) ? Security::cleanInt($_POST['parent_id']) : null;

        if (empty($name)) {
            $this->redirect('categories.php?error=invalid_name');
        }

        try {
            Category::create(['name' => $name, 'parent_id' => $parentId]);
            $this->redirect('categories.php?success=created');
        } catch (\Exception $e) {
            \Core\Logger::error("Category Creation Failed: " . $e->getMessage());
            $this->redirect('categories.php?error=db_error');
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo no permitido.';
            return;
        }

        $id = Security::cleanInt($_POST['id'] ?? 0);
        $name = Security::cleanString($_POST['name'] ?? '', 255);
        $parentId = !empty($_POST['parent_id']) ? Security::cleanInt($_POST['parent_id']) : null;

        if ($id <= 0 || empty($name)) {
            $this->redirect('categories.php?error=invalid_data');
        }

        try {
            Category::update($id, ['name' => $name, 'parent_id' => $parentId]);
            $this->redirect('categories.php?success=updated');
        } catch (\Exception $e) {
            \Core\Logger::error("Category Update Failed: " . $e->getMessage());
            $this->redirect('categories.php?error=db_error');
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
            $this->redirect('categories.php?error=invalid_id');
        }

        try {
            Category::delete($id);
            $this->redirect('categories.php?success=deleted');
        } catch (\Exception $e) {
            \Core\Logger::error("Category Delete Failed: " . $e->getMessage());
            $this->redirect('categories.php?error=' . (str_contains($e->getMessage(), 'usada por productos') ? 'in_use' : 'delete_failed'));
        }
    }
}
