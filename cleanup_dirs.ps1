# Delete duplicate directories
$dirs = @(
    'Y:\facturador.pccurico.cl\assets_old',
    'Y:\facturador.pccurico.cl\routes',
    'Y:\facturador.pccurico.cl\api',
    'Y:\facturador.pccurico.cl\uploads',
    'Y:\facturador.pccurico.cl\public\assets_old',
    'Y:\facturador.pccurico.cl\public\api'
)

foreach ($dir in $dirs) {
    if (Test-Path $dir) {
        try {
            Remove-Item $dir -Recurse -Force -ErrorAction Stop
            Write-Host "Deleted dir: $(Split-Path $dir -Leaf)"
        } catch {
            Write-Host "SKIP (locked): $(Split-Path $dir -Leaf)"
        }
    }
}
Write-Host "Directory cleanup done."
