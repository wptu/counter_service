---
trigger: always_on
---

# Workspace Rules — Docker-Based Development

## 🔴 CRITICAL: This project runs inside Docker containers

- The application is NOT run directly on the host machine.
- All development, testing, and debugging must happen inside Docker containers.
- NEVER suggest running:
  - `python manage.py runserver`
  - `pip install ...`
  - `pytest`
  - `npm install`
  on the host system.

## ✅ Always check running containers first

Before starting anything, ALWAYS run:

    docker compose ps

If services are already running:
- DO NOT start new containers
- DO NOT rebuild images unless explicitly requested

## 📦 Dependency management

- Python packages must be added in:
  - requirements.txt or pyproject.toml
- After changing dependencies:
  - rebuild containers only when necessary:
    docker compose build

## 🧠 Assumptions for all tasks

When giving solutions or code:
- Assume services are running in Docker
- Assume file paths are inside container filesystem
- Prefer docker-based commands over host commands