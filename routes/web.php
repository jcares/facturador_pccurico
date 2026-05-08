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

    // Data for Sales Chart (Last 6 Months)
    $stmt = $db->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as total 
        FROM invoices 
        WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH) 
        GROUP BY month 
        ORDER BY month ASC
    ");
    $salesDataRaw = $stmt->fetchAll();
    
    $salesMonths = [];
    $salesTotals = [];
    foreach ($salesDataRaw as $row) {
        // Convert 'YYYY-MM' to a more readable format, e.g., 'Mes YYYY'
        $dateObj = DateTime::createFromFormat('Y-m', $row['month']);
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $monthName = $meses[(int)$dateObj->format('n') - 1] . ' ' . $dateObj->format('Y');
        
        $salesMonths[] = $monthName;
        $salesTotals[] = (float) $row['total'];
    }

    // Data for Status Chart
    $stmt = $db->query("SELECT status, COUNT(id) as count FROM invoices GROUP BY status");
    $statusDataRaw = $stmt->fetchAll();
    
    $statusLabels = [];
    $statusCounts = [];
    $statusColors = [
        'draft' => '#94a3b8',
        'sent' => '#3b82f6',
        'paid' => '#10b981',
        'canceled' => '#ef4444'
    ];
    $statusBgColors = [];
    
    foreach ($statusDataRaw as $row) {
        $statusLabels[] = ucfirst($row['status']);
        $statusCounts[] = (int) $row['count'];
        $statusBgColors[] = $statusColors[$row['status']] ?? '#cbd5e1';
    }

    View::render('dashboard', [
        'title' => 'Panel de Control',
        'stats' => [
            'month_total' => $monthTotal,
            'total_docs' => $totalDocs,
            'total_clients' => $totalClients
        ],
        'charts' => [
            'sales' => [
                'labels' => json_encode($salesMonths),
                'data' => json_encode($salesTotals)
            ],
            'status' => [
                'labels' => json_encode($statusLabels),
                'data' => json_encode($statusCounts),
                'colors' => json_encode($statusBgColors)
            ]
        ]
    ]);
});
