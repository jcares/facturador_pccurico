# FTP Upload Script for facturador_pccurico
$ftpServer = "ftp.pccurico.cl"
$ftpUser = "pacurico.cl"
$ftpPass = "Jc4717561984@@"

$filesToUpload = @(
    "app\views\settings\email.php",
    "app\views\settings_layout.php",
    "app\views\layout.php",
    "app\views\clients\index.php",
    "Modules\Templates\visual_edit.php",
    "Modules\Templates\TemplateController.php",
    "public\assets\css\style.css",
    "public\assets\css\style_mobile.css"
)

foreach ($file in $filesToUpload) {
    $localPath = Join-Path $PSScriptRoot $file
    if (Test-Path $localPath) {
        $uri = "ftp://$ftpServer/$file".Replace("\", "/")
        try {
            $request = [System.Net.FtpWebRequest]::Create($uri)
            $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
            $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            
            $fileBytes = [System.IO.File]::ReadAllBytes($localPath)
            $request.ContentLength = $fileBytes.Length
            
            $requestStream = $request.GetRequestStream()
            $requestStream.Write($fileBytes, 0, $fileBytes.Length)
            $requestStream.Close()
            
            $response = $request.GetResponse()
            Write-Host ("OK: " + $file) -ForegroundColor Green
            $response.Close()
        }
        catch {
            Write-Host ("ERR: " + $file + " - " + $_.Exception.Message) -ForegroundColor Red
        }
    }
    else {
        Write-Host ("MISSING: " + $file) -ForegroundColor Yellow
    }
}

Write-Host "Upload complete!" -ForegroundColor Cyan
