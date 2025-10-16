@echo off
echo ========================================
echo    INSTALANDO PROJETO QR CODE NO APACHE
echo ========================================
echo.

REM Configurar variáveis
set APACHE_HOME=C:\Apache24
set PROJECT_HOME=%~dp0
set PROJECT_NAME=projeto-qr-code-php

echo [1/8] Verificando Apache...
if not exist "%APACHE_HOME%\bin\httpd.exe" (
    echo ERRO: Apache nao encontrado em %APACHE_HOME%
    echo Verifique se o Apache esta instalado!
    pause
    exit /b 1
)
echo ✓ Apache encontrado

echo.
echo [2/8] Verificando PHP...
if not exist "%APACHE_HOME%\php\php.exe" (
    echo ERRO: PHP nao encontrado em %APACHE_HOME%\php\
    echo Verifique se o PHP esta instalado!
    pause
    exit /b 1
)
echo ✓ PHP encontrado

echo.
echo [3/8] Verificando MySQL...
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERRO: MySQL nao encontrado!
    echo Verifique se o MySQL esta instalado e no PATH
    pause
    exit /b 1
)
echo ✓ MySQL encontrado

echo.
echo [4/8] Parando Apache...
net stop Apache2.4 >nul 2>&1
timeout /t 3 /nobreak >nul

echo.
echo [5/8] Copiando projeto para htdocs...
if not exist "%APACHE_HOME%\htdocs\%PROJECT_NAME%" (
    mkdir "%APACHE_HOME%\htdocs\%PROJECT_NAME%"
)
xcopy "%PROJECT_HOME%*" "%APACHE_HOME%\htdocs\%PROJECT_NAME%\" /E /I /Y >nul
echo ✓ Projeto copiado

echo.
echo [6/8] Configurando banco de dados...
mysql -u root -p"@Ed85962u" -e "CREATE DATABASE IF NOT EXISTS projeto_qr_code CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ Banco de dados configurado
) else (
    echo ⚠️ Erro ao configurar banco de dados
)

echo.
echo [7/8] Configurando Apache...
echo Adicionando configuracao ao httpd.conf...
echo. >> "%APACHE_HOME%\conf\httpd.conf"
echo # Projeto QR Code >> "%APACHE_HOME%\conf\httpd.conf"
echo Include "%APACHE_HOME%\htdocs\%PROJECT_NAME%\apache-config.conf" >> "%APACHE_HOME%\conf\httpd.conf"
echo ✓ Configuracao adicionada

echo.
echo [8/8] Iniciando Apache...
net start Apache2.4 >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ Apache iniciado com sucesso
) else (
    echo ⚠️ Erro ao iniciar Apache
)

echo.
echo ========================================
echo    INSTALACAO CONCLUIDA!
echo ========================================
echo.
echo 📋 INFORMACOES:
echo • Apache: %APACHE_HOME%
echo • Projeto: %APACHE_HOME%\htdocs\%PROJECT_NAME%
echo • URL: http://localhost/
echo • Banco: projeto_qr_code (MySQL)
echo.
echo 🔧 COMANDOS UTEIS:
echo • Parar Apache: net stop Apache2.4
echo • Iniciar Apache: net start Apache2.4
echo • Logs: %APACHE_HOME%\logs\
echo.
echo ⏳ Aguarde 10 segundos para o Apache inicializar...
timeout /t 10 /nobreak >nul

echo.
echo 🧪 Testando aplicacao...
curl -s -o nul -w "%%{http_code}" http://localhost/ 2>nul | findstr "200" >nul
if %errorlevel% equ 0 (
    echo ✅ APLICACAO FUNCIONANDO PERFEITAMENTE!
    echo.
    echo 🌐 Acesse sua aplicacao em:
    echo    http://localhost/
    echo.
    echo 📱 Dados de teste:
    echo    CPF: 123.456.789-00
    echo    Senha: senha123
) else (
    echo ⚠️ Aplicacao pode estar carregando ainda...
    echo Aguarde mais alguns segundos e acesse:
    echo http://localhost/
)

echo.
echo Pressione qualquer tecla para abrir o navegador...
pause >nul

REM Abrir navegador
start http://localhost/

echo.
echo Pressione qualquer tecla para sair...
pause >nul

