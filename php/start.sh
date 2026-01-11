#!/bin/bash

# Shift Scheduling System - Quick Start Script

echo "🚀 Starting Shift Scheduling System..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker is not running"
    echo "Please start Docker Desktop and try again"
    exit 1
fi

# Navigate to php directory
cd "$(dirname "$0")"

# Build and start containers
echo "📦 Building Docker containers..."
docker-compose up -d --build

# Wait for container to be ready
echo "⏳ Waiting for application to start..."
sleep 3

# Check if container is running
if [ "$(docker-compose ps -q web)" ]; then
    echo ""
    echo "✅ Application started successfully!"
    echo ""
    echo "🌐 Open your browser at: http://localhost:8080"
    echo ""
    echo "📊 Available pages:"
    echo "   - ตารางเวรทั้งปี: http://localhost:8080"
    echo "   - ปฏิทินรายเดือน: http://localhost:8080?page=calendars"
    echo "   - สรุปสถิติ: http://localhost:8080?page=summary"
    echo "   - กระจายวันในสัปดาห์: http://localhost:8080?page=dow"
    echo "   - สรุป TP รายเดือน: http://localhost:8080?page=monthly-tp"
    echo "   - รายละเอียดพนักงาน: http://localhost:8080?page=staff-details"
    echo "   - วันหยุด: http://localhost:8080?page=holidays"
    echo "   - เงื่อนไขการจัดเวร: http://localhost:8080?page=conditions"
    echo ""
    echo "⚙️  To stop: ./stop.sh or docker-compose down"
    echo "🔄 To restart: docker-compose restart"
    echo "📝 To view logs: docker-compose logs -f"
else
    echo "❌ Error: Container failed to start"
    echo "Check logs with: docker-compose logs"
    exit 1
fi
