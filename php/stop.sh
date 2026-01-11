#!/bin/bash

# Stop Shift Scheduling System

echo "🛑 Stopping Shift Scheduling System..."

cd "$(dirname "$0")"

docker-compose down

echo "✅ Application stopped"
