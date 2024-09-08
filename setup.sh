#!/bin/bash

echo "Building and setting up images..."
docker-compose up -d --build

echo "install app dependencies..."
docker-compose exec app composer install

echo "migrating data..."
docker-compose exec app php artisan migrate --seed

echo "Setup complete!"