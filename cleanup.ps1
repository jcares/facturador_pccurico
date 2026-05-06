$files = @(
    'Y:\facturador.pccurico.cl\clients.php',
    'Y:\facturador.pccurico.cl\products.php',
    'Y:\facturador.pccurico.cl\payments.php',
    'Y:\facturador.pccurico.cl\settings.php',
    'Y:\facturador.pccurico.cl\invoices.php',
    'Y:\facturador.pccurico.cl\reports.php',
    'Y:\facturador.pccurico.cl\login.php',
    'Y:\facturador.pccurico.cl\logout.php',
    'Y:\facturador.pccurico.cl\index.php',
    'Y:\facturador.pccurico.cl\install.php',
    'Y:\facturador.pccurico.cl\cron.php',
    'Y:\facturador.pccurico.cl\view.php',
    'Y:\facturador.pccurico.cl\webpay_init.php',
    'Y:\facturador.pccurico.cl\webpay_return.php',
    'Y:\facturador.pccurico.cl\update_schema.php',
    'Y:\facturador.pccurico.cl\read_log.php',
    'Y:\facturador.pccurico.cl\biblia.md',
    'Y:\facturador.pccurico.cl\bitacora.md',
    'Y:\facturador.pccurico.cl\exclude.txt'
)

foreach ($file in $files) {
    if (Test-Path $file) {
        Remove-Item $file -Force -ErrorAction SilentlyContinue
        Write-Host "Deleted: $(Split-Path $file -Leaf)"
    }
}
Write-Host "Root files cleanup done."
