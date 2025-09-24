#!/bin/bash

# Deploy script for Ondine API
echo "🚀 Deploying Ondine API in Docker..."

# Check if we're in production mode
if [ "$1" = "prod" ]; then
    COMPOSE_FILE="docker-compose.prod.yml"
    echo "🏭 Using production configuration"
else
    COMPOSE_FILE="docker-compose.yml"
    echo "🔧 Using development configuration"
    
    # Check if .env exists for dev mode
    if [ ! -f "config/.env" ]; then
        echo "❌ Error: config/.env file not found for development mode"
        echo "Please copy config/.env.example to config/.env and configure your environment"
        echo "Or use: ./deploy.sh prod  (for production mode without .env file)"
        exit 1
    fi
fi

# Build and run
echo "🔨 Building containers..."
cd docker
docker-compose -f $COMPOSE_FILE --project-name ondine down 2>/dev/null || true
docker-compose -f $COMPOSE_FILE --project-name ondine build --no-cache

echo "🏗️  Starting services..."
docker-compose -f $COMPOSE_FILE --project-name ondine up -d

echo "⏳ Waiting for database to be ready..."
sleep 10

# Check if containers are running
echo "📊 Checking container status..."
docker-compose -f $COMPOSE_FILE --project-name ondine ps

echo ""
echo "✅ Deployment complete!"
echo ""
echo "🌐 Your API should be available at:"
echo "   http://localhost:8080/api/"
echo ""
echo "🔍 To check logs:"
echo "   docker-compose -f $COMPOSE_FILE --project-name ondine logs -f ondine"
echo "   docker-compose -f $COMPOSE_FILE --project-name ondine logs -f ondine-nginx"
echo ""
echo "🛑 To stop:"
echo "   docker-compose -f $COMPOSE_FILE --project-name ondine down --remove-orphans"
echo ""
if [ "$1" = "prod" ]; then
    echo "⚠️  PRODUCTION MODE: Remember to change the default passwords and JWT secret!"
fi