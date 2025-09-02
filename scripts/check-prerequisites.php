<?php

/**
 * Thorium90 Prerequisites Checker
 * 
 * Validates system requirements for local development setup
 * Run: php scripts/check-prerequisites.php
 */

echo "\n🔍 Thorium90 Prerequisites Checker\n";
echo "==================================\n\n";

$errors = [];
$warnings = [];
$checks = 0;

// Check PHP Version
$checks++;
echo "📋 Checking PHP version... ";
$phpVersion = PHP_VERSION;
$minPhpVersion = '8.0.0';

if (version_compare($phpVersion, $minPhpVersion, '>=')) {
    echo "✅ PHP {$phpVersion} (meets requirement: {$minPhpVersion}+)\n";
} else {
    echo "❌ PHP {$phpVersion} (requires: {$minPhpVersion}+)\n";
    $errors[] = "PHP version must be {$minPhpVersion} or higher. Current: {$phpVersion}";
}

// Check Required PHP Extensions
$checks++;
echo "📋 Checking PHP extensions... ";
$requiredExtensions = [
    'mbstring', 'xml', 'ctype', 'json', 'bcmath', 
    'fileinfo', 'tokenizer', 'openssl', 'pdo', 'pdo_sqlite'
];

$missingExtensions = [];
foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}

if (empty($missingExtensions)) {
    echo "✅ All required extensions loaded\n";
} else {
    echo "❌ Missing extensions: " . implode(', ', $missingExtensions) . "\n";
    $errors[] = "Missing PHP extensions: " . implode(', ', $missingExtensions);
}

// Check Composer
$checks++;
echo "📋 Checking Composer... ";
$composerVersion = null;
$composerOutput = shell_exec('composer --version 2>&1');

if ($composerOutput && (preg_match('/Composer version (\d+\.\d+\.\d+)/', $composerOutput, $matches) || preg_match('/Composer[^\d]*(\d+\.\d+\.\d+)/', $composerOutput, $matches))) {
    $composerVersion = $matches[1];
    $minComposerVersion = '2.0.0';
    
    if (version_compare($composerVersion, $minComposerVersion, '>=')) {
        echo "✅ Composer {$composerVersion} (meets requirement: {$minComposerVersion}+)\n";
    } else {
        echo "⚠️ Composer {$composerVersion} (recommended: {$minComposerVersion}+)\n";
        $warnings[] = "Consider upgrading Composer to version {$minComposerVersion} or higher";
    }
} else {
    echo "❌ Composer not found or not accessible\n";
    $errors[] = "Composer is not installed or not in PATH";
}

// Check Node.js
$checks++;
echo "📋 Checking Node.js... ";
$nodeVersion = null;
$nodeOutput = shell_exec('node --version 2>&1');

if ($nodeOutput && preg_match('/v(\d+\.\d+\.\d+)/', $nodeOutput, $matches)) {
    $nodeVersion = $matches[1];
    $minNodeVersion = '16.0.0';
    
    if (version_compare($nodeVersion, $minNodeVersion, '>=')) {
        echo "✅ Node.js {$nodeVersion} (meets requirement: {$minNodeVersion}+)\n";
    } else {
        echo "⚠️ Node.js {$nodeVersion} (recommended: {$minNodeVersion}+)\n";
        $warnings[] = "Consider upgrading Node.js to version {$minNodeVersion} or higher";
    }
} else {
    echo "❌ Node.js not found or not accessible\n";
    $errors[] = "Node.js is not installed or not in PATH";
}

// Check NPM
$checks++;
echo "📋 Checking NPM... ";
$npmVersion = null;
$npmOutput = shell_exec('npm --version 2>&1');

if ($npmOutput && preg_match('/(\d+\.\d+\.\d+)/', trim($npmOutput), $matches)) {
    $npmVersion = $matches[1];
    echo "✅ NPM {$npmVersion}\n";
} else {
    echo "❌ NPM not found or not accessible\n";
    $errors[] = "NPM is not installed or not in PATH";
}

// Check Git
$checks++;
echo "📋 Checking Git... ";
$gitOutput = shell_exec('git --version 2>&1');

if ($gitOutput && strpos($gitOutput, 'git version') !== false) {
    preg_match('/git version (\d+\.\d+\.\d+)/', $gitOutput, $matches);
    $gitVersion = isset($matches[1]) ? $matches[1] : 'unknown';
    echo "✅ Git {$gitVersion}\n";
} else {
    echo "⚠️ Git not found\n";
    $warnings[] = "Git is recommended for version control";
}

// Check SQLite Database Directory
$checks++;
echo "📋 Checking database directory... ";
$dbPath = __DIR__ . '/../database';
$dbFile = $dbPath . '/database.sqlite';

if (is_dir($dbPath)) {
    echo "✅ Database directory exists\n";
    
    // Check if SQLite file exists or can be created
    if (file_exists($dbFile)) {
        echo "📋 SQLite database file... ✅ Exists\n";
    } else {
        // Try to create the file
        if (touch($dbFile)) {
            echo "📋 SQLite database file... ✅ Created successfully\n";
        } else {
            echo "📋 SQLite database file... ❌ Cannot create\n";
            $errors[] = "Cannot create SQLite database file. Check permissions on database directory.";
        }
    }
} else {
    echo "❌ Database directory missing\n";
    $errors[] = "Database directory does not exist: {$dbPath}";
}

// Check Storage Directory Permissions
$checks++;
echo "📋 Checking storage directory... ";
$storagePath = __DIR__ . '/../storage';

if (is_dir($storagePath) && is_writable($storagePath)) {
    echo "✅ Storage directory is writable\n";
} else {
    echo "❌ Storage directory not writable\n";
    $errors[] = "Storage directory is not writable. Run: chmod -R 755 storage";
}

// Check Bootstrap Cache Directory
$checks++;
echo "📋 Checking bootstrap cache... ";
$bootstrapPath = __DIR__ . '/../bootstrap/cache';

if (is_dir($bootstrapPath) && is_writable($bootstrapPath)) {
    echo "✅ Bootstrap cache directory is writable\n";
} else {
    echo "❌ Bootstrap cache directory not writable\n";
    $errors[] = "Bootstrap cache directory is not writable. Run: chmod -R 755 bootstrap/cache";
}

// Port 8000 Availability Check
$checks++;
echo "📋 Checking port 8000 availability... ";
$socket = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 1);

if ($socket) {
    fclose($socket);
    echo "⚠️ Port 8000 is in use\n";
    $warnings[] = "Port 8000 is already in use. You may need to use a different port or stop the service using it.";
} else {
    echo "✅ Port 8000 is available\n";
}

// Results Summary
echo "\n📊 Prerequisites Check Results\n";
echo "===============================\n";
echo "Total checks: {$checks}\n";
echo "Errors: " . count($errors) . "\n";
echo "Warnings: " . count($warnings) . "\n\n";

if (!empty($errors)) {
    echo "❌ ERRORS (must fix before proceeding):\n";
    foreach ($errors as $i => $error) {
        echo "   " . ($i + 1) . ". {$error}\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️ WARNINGS (recommended to fix):\n";
    foreach ($warnings as $i => $warning) {
        echo "   " . ($i + 1) . ". {$warning}\n";
    }
    echo "\n";
}

if (empty($errors)) {
    echo "🎉 All required prerequisites met! You're ready for Thorium90 development.\n\n";
    echo "Next steps:\n";
    echo "1. Run: composer install\n";
    echo "2. Run: npm install\n";
    echo "3. Copy .env.example to .env\n";
    echo "4. Run: php artisan key:generate\n";
    echo "5. Run: php artisan thorium90:setup --interactive\n\n";
    
    exit(0);
} else {
    echo "🚨 Please fix the errors above before proceeding with installation.\n\n";
    echo "For help, see: DEPLOYMENT.md or run 'php artisan thorium90:setup --help'\n\n";
    
    exit(1);
}