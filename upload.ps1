$ftpUser = "pacurico.cl"
$ftpPass = "Jc4717561984@@"
$ftpHost = "ftp.pccurico.cl"
$remoteBase = "/public_html"

$files = @(
    "app/views/settings/email.php",
    "app/views/settings_layout.php",
    "app/views/layout.php",
    "Modules/Templates/visual_edit.php",
    "public/assets/css/style.css",
    "public/assets/css/style_mobile.css"
)

foreach ($file in $files) {
    $localPath = "D:/Desarrollos/facturador_pccurico/$file"
    $remotePath = "ftp://$ftpHost$remoteBase/$file"
    
    if (Test-Path $localPath) {
        try {
            $client = New-Object System.Net.WebClient
            $client.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
            $client.UploadFile($remotePath, $localPath)
            Write-Host "OK: $file" -ForegroundColor Green
        }
        catch {
            Write-Host "ERR: $file" -ForegroundColor Red
            Write-Host $_.Exception.Message -ForegroundColor Yellow
        }
    }
    else {
        Write-Host "MISSING: $file" -ForegroundColor Yellow
    }
}

Write-Host "Upload complete!" -ForegroundColor Cyan
