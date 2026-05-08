<?php
namespace Modules\Tasks;

use Core\Controller;
use Core\Security;
use Core\Auth;
use Modules\Tasks\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        $this->view('tasks/index', [
            'title' => 'Gestión de Tareas',
            'tasks' => $tasks
        ]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => Security::cleanString($_POST['title'] ?? ''),
                'description' => Security::cleanString($_POST['description'] ?? ''),
                'status' => Security::cleanString($_POST['status'] ?? 'pending'),
                'priority' => Security::cleanString($_POST['priority'] ?? 'medium'),
                'due_date' => !empty($_POST['due_date']) ? Security::cleanString($_POST['due_date']) : null,
                'assigned_to' => !empty($_POST['assigned_to']) ? Security::cleanInt($_POST['assigned_to']) : null,
                'created_by' => Auth::id()
            ];

            if (empty($data['title'])) {
                $this->redirect('tasks.php?error=empty_title');
            }

            $id = Task::create($data);
            $this->redirect('tasks.php?success=created');
        }

        $this->view('tasks/create', [
            'title' => 'Crear Nueva Tarea'
        ]);
    }

    public function edit($id = null)
    {
        $id = Security::cleanInt($id ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            $this->redirect('tasks.php?error=invalid_id');
        }

        $task = Task::find($id);
        if (!$task) {
            $this->redirect('tasks.php?error=not_found');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => Security::cleanString($_POST['title'] ?? ''),
                'description' => Security::cleanString($_POST['description'] ?? ''),
                'status' => Security::cleanString($_POST['status'] ?? 'pending'),
                'priority' => Security::cleanString($_POST['priority'] ?? 'medium'),
                'due_date' => !empty($_POST['due_date']) ? Security::cleanString($_POST['due_date']) : null,
                'assigned_to' => !empty($_POST['assigned_to']) ? Security::cleanInt($_POST['assigned_to']) : null
            ];

            if (empty($data['title'])) {
                $this->redirect('tasks.php?error=empty_title');
            }

            Task::update($id, $data);
            $this->redirect('tasks.php?success=updated');
        }

        $this->view('tasks/edit', [
            'title' => 'Editar Tarea',
            'task' => $task
        ]);
    }

    public function delete()
    {
        $id = Security::cleanInt($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('tasks.php?error=invalid_id');
        }

        Task::delete($id);
        $this->redirect('tasks.php?success=deleted');
    }
}