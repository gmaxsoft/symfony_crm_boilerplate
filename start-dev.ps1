# VENOM CRM — skrypt uruchamiający środowisko deweloperskie
# Uruchom z głównego katalogu projektu: .\start-dev.ps1

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$backend = Join-Path $root "backend"
$frontend = Join-Path $root "frontend"

Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  VENOM CRM — Start Dev Environment" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Sprawdź czy symfony CLI jest dostępne
$symfonyCmd = Get-Command "symfony" -ErrorAction SilentlyContinue

# Backend
Write-Host "[1/2] Uruchamiam backend Symfony..." -ForegroundColor Yellow
if ($symfonyCmd) {
    Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$backend'; symfony server:start" -WindowStyle Normal
    Write-Host "  Backend: http://localhost:8000" -ForegroundColor Green
} else {
    Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$backend'; php -S 0.0.0.0:8000 -t public" -WindowStyle Normal
    Write-Host "  Backend (PHP built-in): http://localhost:8000" -ForegroundColor Green
}

Start-Sleep -Seconds 2

# Frontend
Write-Host "[2/2] Uruchamiam frontend Vite..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$frontend'; npm run dev" -WindowStyle Normal
Write-Host "  Frontend: http://localhost:5173" -ForegroundColor Green

Write-Host ""
Write-Host "Oba serwery uruchomione w osobnych oknach." -ForegroundColor Cyan
Write-Host "Otwórz przegladarke: http://localhost:5173" -ForegroundColor White
Write-Host ""
