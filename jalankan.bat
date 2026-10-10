@echo off
title Platform Undangan Digital SaaS
cd /d "%~dp0"

echo ========================================================
echo   Menjalankan Platform Undangan Digital SaaS...
echo ========================================================
echo.

:: Menambahkan path PHP winget ke sesi ini jika belum ada
set "PATH=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe;%PATH%"

echo Memeriksa PHP...
php -v
if %errorlevel% neq 0 (
    echo [ERROR] PHP tidak ditemukan. Pastikan PHP terinstal.
    pause
    exit /b
)

echo.
echo Server web aktif di: http://127.0.0.1:8000
echo Tekan Ctrl+C untuk menghentikan server.
echo.

php artisan serve --host=127.0.0.1 --port=8000
pause
