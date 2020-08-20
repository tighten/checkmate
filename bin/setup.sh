#!/bin/bash

echo ''
echo '# Composer Install'
echo ''
composer install

if [ ! -f .env ]; then
    echo '# Set up .env and generate key'
    echo ''
    cp .env.example .env
    php artisan key:generate
fi
if [ ! -f .env.testing ]; then
    echo '# Set up .env.testing'
    cp .env.testing.example .env.testing
fi

echo ''
echo '# Npm install && npm run dev'

npm install
npm run dev

echo ''
echo 'Make sure to create a database, modify your .env and .env.testing files'
echo 'with your local credentials, then run ./bin/db.sh'
echo ''
