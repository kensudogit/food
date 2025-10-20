#!/bin/bash

# Food Delivery API - Development Setup Script

set -e

echo "🍽️  Food Delivery API - Development Setup"
echo "=========================================="

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

# Check if Docker Compose is available
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose."
    exit 1
fi

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file from template..."
    cp config/env.example .env
    echo "✅ .env file created. Please update the configuration values."
fi

# Create logs directory
echo "📁 Creating logs directory..."
mkdir -p logs
chmod 755 logs

# Start Docker services
echo "🐳 Starting Docker services..."
docker-compose up -d

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 10

# Check if services are running
echo "🔍 Checking service status..."
docker-compose ps

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
if command -v composer &> /dev/null; then
    composer install
else
    echo "⚠️  Composer not found. Please install Composer or run: docker-compose exec php-fpm composer install"
fi

# Generate gRPC code
echo "🔧 Generating gRPC code..."
if [ -f scripts/generate_grpc.sh ]; then
    chmod +x scripts/generate_grpc.sh
    ./scripts/generate_grpc.sh
else
    echo "⚠️  gRPC generation script not found"
fi

# Run database migrations
echo "🗄️  Setting up database..."
docker-compose exec mysql mysql -u root -prootpassword -e "CREATE DATABASE IF NOT EXISTS food_delivery;"

# Check if database is accessible
if docker-compose exec mysql mysql -u root -prootpassword -e "USE food_delivery; SHOW TABLES;" 2>/dev/null; then
    echo "✅ Database is ready"
else
    echo "⚠️  Database setup may need manual intervention"
fi

# Test API health endpoint
echo "🏥 Testing API health..."
sleep 5
if curl -f http://localhost/api/v1/health > /dev/null 2>&1; then
    echo "✅ API is responding"
else
    echo "⚠️  API health check failed. Check logs with: docker-compose logs"
fi

echo ""
echo "🎉 Setup completed!"
echo ""
echo "📋 Next steps:"
echo "1. Update .env file with your configuration"
echo "2. Run database migrations: docker-compose exec php-fpm php api/bin/doctrine-migrations.php migrate"
echo "3. Access the API at: http://localhost/api/v1"
echo "4. Check logs with: docker-compose logs -f"
echo "5. Stop services with: docker-compose down"
echo ""
echo "📚 Documentation: README.md"
echo "🐛 Issues: Check logs in the logs/ directory"
