<?php

use Core\Auth;
use Core\Database;
use Core\View;

$router->add('dashboard', function () {
    if (!Auth::check()) {
        header('Location: login.php');
        exit;
    }

    $db = Database::getInstance();

    $stmt = $db->query("SELECT SUM(total) as month_total FROM invoices WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $monthTotal = $stmt->fetch()['month_total'] ?? 0;

    $stmt = $db->query("SELECT COUNT(id) as total_docs FROM invoices");
    $totalDocs = $stmt->fetch()['total_docs'] ?? 0;

    $stmt = $db->query("SELECT COUNT(id) as total_clients FROM clients");
    $totalClients = $stmt->fetch()['total_clients'] ?? 0;

    View::render('dashboard', [
        'title' => 'Panel de Control',
        'stats' => [
            'month_total' => $monthTotal,
            'total_docs' => $totalDocs,
            'total_clients' => $totalClients
        ]
    ]);
});
