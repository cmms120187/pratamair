# Script untuk pull dari GitHub di server SSH (PowerShell)
# Mengatasi konflik dengan stash perubahan lokal

Write-Host "Checking git status..." -ForegroundColor Yellow
git status

Write-Host ""
Write-Host "Stashing local changes..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
git stash push -m "Local changes before pull - $timestamp"

Write-Host ""
Write-Host "Pulling from origin/main..." -ForegroundColor Yellow
git pull origin main

Write-Host ""
Write-Host "Applying stashed changes..." -ForegroundColor Yellow
git stash pop

Write-Host ""
Write-Host "Done! Check for any conflicts above." -ForegroundColor Green

