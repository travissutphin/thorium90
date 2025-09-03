#!/bin/bash

echo "==========================================="
echo "   Thorium90 Creator"
echo "==========================================="
echo

# Get project name
read -p "Enter project name: " PROJECT_NAME
if [ -z "$PROJECT_NAME" ]; then
    PROJECT_NAME="thorium90-project"
fi

echo
echo "Creating project: $PROJECT_NAME"
echo

# Create project using composer
echo "[1/4] Creating project with Composer..."
composer create-project thorium90/boilerplate "$PROJECT_NAME" --prefer-dist

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to create project. Make sure Composer is installed and thorium90/boilerplate package exists."
    exit 1
fi

echo
echo "[2/4] Changing to project directory..."
cd "$PROJECT_NAME"

echo
echo "[3/4] Installing dependencies..."
composer install
npm install

echo
echo "[4/4] Running setup wizard..."
php artisan thorium90:setup --interactive

echo
echo "==========================================="
echo "   Setup Complete!"
echo "==========================================="
echo
echo "Your Thorium90 project '$PROJECT_NAME' is ready!"
echo
echo "Next steps:"
echo "  1. cd $PROJECT_NAME"
echo "  2. php artisan serve"
echo "  3. Visit http://localhost:8000"
echo

read -p "Press enter to continue..."