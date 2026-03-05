@echo off
cd /d "%~dp0"
echo InteliFood - Servidor iniciando...
echo.
echo Pasta: %cd%
echo Abra: http://localhost:8097/
echo.
php -S localhost:8097 router.php
pause
