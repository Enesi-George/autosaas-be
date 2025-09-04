#!/bin/bash

# Set the correct path to your Laravel project
cd /home/autowqrj/repositories/autosaas-be

# Load environment variables
export PATH=/usr/local/bin:$PATH

# Set Laravel environment
export APP_ENV=production

# Run the queue worker
/usr/local/bin/php artisan queue:work --stop-when-empty --max-time=50 --sleep=3 --tries=3

# Log the execution
echo "Queue worker executed at $(date)" >> /home/autowqrj/queue_worker.log