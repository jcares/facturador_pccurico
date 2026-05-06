<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

if (!\Core\Auth::check()) {
    header('Location: login.php');
    exit;
}

\Core\View::render('recurring_invoices/index', [
    'title' => 'Facturas Recurrentes',
    'recurringInvoices' => \Modules\Invoices\RecurringInvoice::all(),
]);
