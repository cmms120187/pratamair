# PowerShell script to fix platform_check.php after composer update
# Run this script after running: composer update

$platformCheckFile = "vendor\composer\platform_check.php"
$content = Get-Content $platformCheckFile -Raw

# Replace PHP 8.3 requirement with PHP 8.2
$content = $content -replace 'PHP_VERSION_ID >= 80300', 'PHP_VERSION_ID >= 80200'
$content = $content -replace 'PHP version ">= 8\.3\.0"', 'PHP version ">= 8.2.0"'

Set-Content -Path $platformCheckFile -Value $content -NoNewline

Write-Host "Platform check fixed! PHP 8.2 is now allowed." -ForegroundColor Green

