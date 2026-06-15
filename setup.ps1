
$ErrorActionPreference = "Stop"

Write-Host "=========================================" -ForegroundColor Green
Write-Host "Downloading dotnet-install.ps1 script..." -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

if (Test-Path "dotnet-install.ps1") {
    Remove-Item "dotnet-install.ps1" -Force
}

Invoke-WebRequest -Uri "https://dot.net/v1/dotnet-install.ps1" -OutFile "dotnet-install.ps1"

Write-Host ""
Write-Host "=========================================================" -ForegroundColor Green
Write-Host "Installing .NET 8.0 SDK locally to .\dotnet folder..." -ForegroundColor Green
Write-Host "=========================================================" -ForegroundColor Green

if (Test-Path "dotnet") {
    Write-Host "Existing local dotnet folder found. Re-installing/updating..." -ForegroundColor Yellow
}

powershell -ExecutionPolicy Bypass -File .\dotnet-install.ps1 -Channel 8.0 -InstallDir .\dotnet

Write-Host ""
if (Test-Path ".\dotnet\dotnet.exe") {
    Write-Host "=========================================================" -ForegroundColor Green
    Write-Host "Local .NET SDK installed successfully!" -ForegroundColor Green
    Write-Host "Local .NET version details:" -ForegroundColor Green
    & .\dotnet\dotnet.exe --info
    Write-Host "=========================================================" -ForegroundColor Green
} else {
    Write-Error "Local .NET SDK could not be verified at .\dotnet\dotnet.exe."
    exit 1
}

if (Test-Path "dotnet-install.ps1") {
    Remove-Item "dotnet-install.ps1" -Force
}

Write-Host "Setup finished. You can now build and run .NET projects using .\dotnet\dotnet.exe" -ForegroundColor Green