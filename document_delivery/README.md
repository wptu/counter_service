# Document Delivery System

This system runs independently but shares the database with the main Shift Scheduler system.

## Setup & Run

1.  **Ensure the main system is running**:
    The database network `php_scheduler_network` must exist.
    ```bash
    cd ../php
    docker-compose up -d
    ```

2.  **Start this system**:
    ```bash
    cd document_delivery
    docker-compose up -d --build
    ```

3.  **Access**:
    Open [http://localhost:8082](http://localhost:8082)

## Troubleshooting
- **Network not found**: If you get an error about `php_scheduler_network` not found, run `docker network ls` to check the actual name of the network created by the `php` folder. Update `docker-compose.yml` -> `networks` -> `name` accordingly.
