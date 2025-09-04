#!/bin/bash

# Set the correct path to your Laravel project
cd /home/autowqrj/repositories/autosaas-be

# Load environment variables
export PATH=/usr/local/bin:$PATH

# Set Laravel environment
export APP_ENV=production

# Run the queue worker continuously with shorter intervals
while true; do
    # Run queue worker to process available jobs with shorter timeout
    /usr/local/bin/php artisan queue:work --max-time=295 --sleep=3 --tries=3
    
    # Short pause before checking for new jobs again (5 seconds)
    sleep 5
    
    # Log the execution
    echo "Queue worker cycle completed at $(date)" >> /home/autowqrj/queue_worker.log
done