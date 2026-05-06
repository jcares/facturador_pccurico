# Build and deploy script for FACTURADOR-PCCURICO
# Usage: .\build.ps1 -ProductionPath 'Y:\' -CreateZip -Deploy
param(
    [string]$ProductionPath = '',
    [switch]$CreateZip,
    [switch]$Deploy
)

function Get-PhpCommand {
    $php = Get-Command php -ErrorAction SilentlyContinue
    if ($php) {
        return $php.Source
    }
    Write-Error 'PHP no está disponible en PATH. Instale PHP o actualice su PATH antes de ejecutar este script.'
    exit 1
}

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $projectRoot

if ($CreateZip) {
    $phpCmd = Get-PhpCommand
    Write-Host 'Generando paquete cPanel...'
    & $phpCmd "$projectRoot\build_cpanel.php"
}

if ($Deploy) {
    if (-not $ProductionPath) {
        Write-Error 'Debe especificar el parámetro -ProductionPath con la ruta de destino, por ejemplo Y:\\'
        exit 1
    }

    Write-Host "Desplegando archivos a: $ProductionPath"
    $excludeDirs = @('.git', '.vs', '.vscode')
    $excludeArgs = $excludeDirs | ForEach-Object { "/XD `"$_`"" }
    $robocopyCmd = "robocopy `"$projectRoot`" `"$ProductionPath`" /E /COPY:DAT /R:2 /W:5 $($excludeArgs -join ' ')"

    Invoke-Expression $robocopyCmd
}

Write-Host 'Build completado.'
