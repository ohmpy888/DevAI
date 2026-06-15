
$ErrorActionPreference = "Stop"

Write-Host "=========================================" -ForegroundColor Green
Write-Host "Starting SafeVision AI System..." -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

if (-not (Test-Path ".\dotnet\dotnet.exe")) {
    Write-Error "Local .NET SDK not found. Please run .\setup.ps1 first!"
    exit 1
}

Write-Host "Starting C# Backend API on http://localhost:5100..." -ForegroundColor Cyan
$BackendProcess = Start-Process -FilePath ".\dotnet\dotnet.exe" -ArgumentList "run --project backend/ImageClassifierBackend.csproj" -PassThru -NoNewWindow

Write-Host "Starting PHP Web Server on http://localhost:8000..." -ForegroundColor Cyan
$PhpProcess = Start-Process -FilePath "php" -ArgumentList "-S localhost:8000 -t frontend" -PassThru -NoNewWindow

Write-Host "Waiting 4 seconds for services to initialize..." -ForegroundColor Yellow
Start-Sleep -Seconds 4

Write-Host "Opening web browser to http://localhost:8000..." -ForegroundColor Green
Start-Process "http://localhost:8000"

Write-Host ""
Write-Host "=========================================================" -ForegroundColor Green
Write-Host " SafeVision AI is running successfully!" -ForegroundColor Green
Write-Host " - PHP Frontend: http://localhost:8000" -ForegroundColor Green
Write-Host " - C# Backend API: http://localhost:5100" -ForegroundColor Green
Write-Host "=========================================================" -ForegroundColor Green
Write-Host "Press Ctrl+C in this terminal to stop both servers." -ForegroundColor Yellow
Write-Host ""

try {
    while ($true) {

        if ($BackendProcess.HasExited) {
            Write-Host "Warning: C# Backend process exited unexpectedly!" -ForegroundColor Red
        }
        if ($PhpProcess.HasExited) {
            Write-Host "Warning: PHP Server process exited unexpectedly!" -ForegroundColor Red
        }
        Start-Sleep -Seconds 1
    }
}
finally {
    Write-Host ""
    Write-Host "Stopping backend and frontend servers..." -ForegroundColor Red

    if ($BackendProcess -and -not $BackendProcess.HasExited) {
        Stop-Process -Id $BackendProcess.Id -Force -ErrorAction SilentlyContinue
        Write-Host "- Stopped C# Backend API (PID: $($BackendProcess.Id))" -ForegroundColor Red
    }
    if ($PhpProcess -and -not $PhpProcess.HasExited) {
        Stop-Process -Id $PhpProcess.Id -Force -ErrorAction SilentlyContinue
        Write-Host "- Stopped PHP Server (PID: $($PhpProcess.Id))" -ForegroundColor Red
    }
    Write-Host "All services stopped." -ForegroundColor Red
}