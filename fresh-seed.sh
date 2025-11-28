#!/bin/bash

echo "========================================"
echo "Fresh Migration + Seeder"
echo "========================================"
echo ""
echo "This will:"
echo "1. Drop all tables"
echo "2. Re-run all migrations"
echo "3. Run all seeders"
echo ""
read -p "Press Enter to continue..."

php artisan migrate:fresh --seed

echo ""
echo "========================================"
echo "Done!"
echo "========================================"

