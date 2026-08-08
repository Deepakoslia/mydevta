@echo off
title DEVTA Local Server
cd /d "%~dp0"

set PHP_INI=%~dp0php-local.ini
echo.
echo  DEVTA server starting...
echo  Open: http://127.0.0.1:8080/frontend/index.html
echo  Admin: http://127.0.0.1:8080/admin/
echo  Press Ctrl+C to stop.
echo.

php -c "%PHP_INI%" -S 127.0.0.1:8080
pause
