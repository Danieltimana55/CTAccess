@echo off
REM ====================================
REM  CTAccess - Queue Worker Starter
REM ====================================
echo.
echo ========================================
echo  CTAccess - Worker de Colas
echo ========================================
echo.
echo Iniciando worker de colas para generacion de QR...
echo.
echo NOTA: Mantén esta ventana abierta mientras trabajas
echo Presiona Ctrl+C para detener el worker
echo.
echo ========================================
echo.

REM Limpiar caché si es necesario
php artisan config:cache
php artisan queue:restart

REM Iniciar el worker con logs detallados
php artisan queue:work --verbose --tries=3 --timeout=60

pause
