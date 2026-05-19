# Simple FTP Upload using WebClient
$ftpServer = "ftp.pccurico.cl"
$ftpUser = "pacurico.cl"  
$ftpPass = "Jc4717561984@@"

$filesToUpload = @(
    "app\views\settings\email.php",
    "app\views\settings_layout.php", 
    "app\views\layout.php",
    "Modules\Templates\visual_edit.php",
    "public\assets\css\style.css",
    "public\assets\css\style_mobile.css"
)

foreach ($file in $filesToUpload) {
    $localPath = Join-Path $PSScriptRoot $file
    if (Test-Path $localPath) {
        $remotePath = "ftp://$ftpServer/$($file.Replace('\','/'))"
        try {
            $client = New-Object System.Net.WebClient
            $client.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
            $client.UploadFile($remotePath, $localPath)
            Write-Host ("OK: $file") -ForegroundColor Green
        }
        catch {
            Write-Host ("ERR: $file - " + $_.Exception.Message) -ForegroundColor Red
        }
    }
    else {
        Write-Host ("MISSING: $file") -ForegroundColor Yellow
    }
}

Write-Host "Done!"
