@echo off
echo ============================================
echo   Icy's Simplicitea POS - Setup Script
echo ============================================
echo.

REM Check if Composer is installed
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Composer is not installed!
    echo Please install Composer from: https://getcomposer.org
    echo.
    pause
    exit /b 1
)

REM Check if Node.js is installed
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Node.js is not installed!
    echo Please install Node.js from: https://nodejs.org
    echo.
    pause
    exit /b 1
)

REM Check if PHP is installed
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP is not installed!
    echo Please install PHP or use Laragon/XAMPP
    echo.
    pause
    exit /b 1
)

echo [OK] All required software detected!
echo.

echo [1/6] Installing PHP dependencies...
call composer install
if %errorlevel% neq 0 (
    echo [ERROR] Composer install failed!
    pause
    exit /b 1
)
echo.

echo [2/6] Installing Node.js dependencies...
call npm install
if %errorlevel% neq 0 (
    echo [ERROR] NPM install failed!
    pause
    exit /b 1
)
echo.

echo [3/6] Setting up environment file...
if not exist .env (
    copy .env.example .env
    echo Created .env file from .env.example
) else (
    echo .env file already exists
)
echo.

echo [4/6] Generating application key...
call php artisan key:generate
echo.

echo [5/6] Building frontend assets...
call npm run build
echo.

echo ============================================
echo   IMPORTANT: Before continuing, you need to:
echo ============================================
echo.
echo   1. Open .env file and configure your database:
echo      - DB_DATABASE=simplicitea
echo      - DB_USERNAME=root
echo      - DB_PASSWORD=your_password
echo.
echo   2. Create a database named 'simplicitea' in MySQL
echo.
echo   Press any key after completing the above steps...
pause >nul
echo.

echo [6/6] Running database migrations...
call php artisan migrate
echo.

echo ============================================
echo   Setup Complete!
echo ============================================
echo.
echo   To start the server, run:
echo   php artisan serve
echo.
echo   Then open: http://localhost:8000
echo.
pause
