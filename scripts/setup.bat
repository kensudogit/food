@echo off
REM Food Delivery API - Windows Development Setup Script

echo 🍽️  Food Delivery API - Development Setup
echo ==========================================

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker is not running. Please start Docker and try again.
    pause
    exit /b 1
)

REM Check if Docker Compose is available
docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Compose is not installed. Please install Docker Compose.
    pause
    exit /b 1
)

REM Create .env file if it doesn't exist
if not exist .env (
    echo 📝 Creating .env file from template...
    copy config\env.example .env
    echo ✅ .env file created. Please update the configuration values.
)

REM Create logs directory
echo 📁 Creating logs directory...
if not exist logs mkdir logs

REM Start Docker services
echo 🐳 Starting Docker services...
docker-compose up -d

REM Wait for services to be ready
echo ⏳ Waiting for services to be ready...
timeout /t 10 /nobreak >nul

REM Check if services are running
echo 🔍 Checking service status...
docker-compose ps

REM Install PHP dependencies
echo 📦 Installing PHP dependencies...
where composer >nul 2>&1
if %errorlevel% equ 0 (
    composer install
) else (
    echo ⚠️  Composer not found. Please install Composer or run: docker-compose exec php-fpm composer install
)

REM Run database migrations
echo 🗄️  Setting up database...
docker-compose exec mysql mysql -u root -prootpassword -e "CREATE DATABASE IF NOT EXISTS food_delivery;"

REM Test API health endpoint
echo 🏥 Testing API health...
timeout /t 5 /nobreak >nul
curl -f http://localhost/api/v1/health >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ API is responding
) else (
    echo ⚠️  API health check failed. Check logs with: docker-compose logs
)

echo.
echo 🎉 Setup completed!
echo.
echo 📋 Next steps:
echo 1. Update .env file with your configuration
echo 2. Run database migrations: docker-compose exec php-fpm php api/bin/doctrine-migrations.php migrate
echo 3. Access the API at: http://localhost/api/v1
echo 4. Check logs with: docker-compose logs -f
echo 5. Stop services with: docker-compose down
echo.
echo 📚 Documentation: README.md
echo 🐛 Issues: Check logs in the logs/ directory

pause
