#!/bin/bash

# Thorium90 Prerequisites Checker for macOS/Linux
# Run: ./scripts/check-prerequisites.sh

echo ""
echo "🔍 Thorium90 Prerequisites Checker (macOS/Linux)"
echo "================================================"
echo ""

# Make sure we're in the project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_ROOT"

# Run the PHP prerequisites checker
php scripts/check-prerequisites.php
EXIT_CODE=$?

if [ $EXIT_CODE -ne 0 ]; then
    echo ""
    echo "🛠️ macOS/Linux-specific installation help:"
    echo ""
    echo "macOS (using Homebrew):"
    echo "  brew install php composer node"
    echo "  # Enable PHP extensions in /opt/homebrew/etc/php/8.x/php.ini"
    echo ""
    echo "Ubuntu/Debian:"
    echo "  sudo apt update"
    echo "  sudo apt install php8.2 php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-curl"
    echo "  sudo apt install composer nodejs npm"
    echo ""
    echo "CentOS/RHEL/Fedora:"
    echo "  sudo dnf install php php-cli php-sqlite3 php-mbstring php-xml php-json"
    echo "  sudo dnf install composer nodejs npm"
    echo ""
    echo "File Permissions:"
    echo "  chmod -R 755 storage bootstrap/cache"
    echo "  chmod 664 database/database.sqlite"
    echo ""
    
    exit 1
fi

echo ""
echo "✅ All prerequisites met! Ready for Thorium90 development."
echo ""