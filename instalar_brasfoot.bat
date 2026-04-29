@echo off
chcp 65001 >nul
echo ===============================
echo   FENIX FOOT - INSTALADOR
echo ===============================
echo.

echo [1/4] Parando Apache...
net stop "Apache2.4" >nul 2>&1
timeout /t 3 /nobreak >nul

echo [2/4] Removendo pasta antiga...
rmdir /s /q "C:\xampp\htdocs\brasfoot2026" 2> nul

echo [3/4] Copiando arquivos corretos...
xcopy "C:\xampp\htdocs\streamblack\novo\brasfoot2026" "C:\xampp\htdocs\brasfoot2026\" /E /I /Y >nul

echo [4/4] Iniciando Apache...
net start "Apache2.4" >nul 2>&1
timeout /t 2 /nobreak >nul

echo.
echo ===============================
echo   INSTALAÇÃO CONCLUÍDA!
echo ===============================
echo.
echo Acesse no navegador:
echo http://localhost/brasfoot2026/setup.php
echo.
pause
