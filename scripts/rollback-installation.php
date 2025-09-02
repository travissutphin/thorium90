<?php

/**
 * Thorium90 Installation Rollback Script
 * 
 * Safely resets a failed installation to a clean state
 * Run: php scripts/rollback-installation.php
 */

echo "\n🔄 Thorium90 Installation Rollback\n";
echo "==================================\n\n";

$projectRoot = dirname(__DIR__);
$backupCreated = false;

// Confirm rollback
echo "⚠️  WARNING: This will reset your installation to a clean state.\n";
echo "This will:\n";
echo "- Remove .env file\n";
echo "- Clear all caches\n";
echo "- Reset database (SQLite)\n";
echo "- Remove generated files\n";
echo "- Keep composer.json and package.json intact\n\n";

if (php_sapi_name() === 'cli') {
    echo "Continue? [y/N]: ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (strtolower(trim($line)) !== 'y') {
        echo "❌ Rollback cancelled.\n\n";
        exit(0);
    }
    fclose($handle);
}

echo "🔄 Starting rollback process...\n\n";

// Step 1: Create backup of current .env if it exists
if (file_exists($projectRoot . '/.env')) {
    echo "📋 Backing up .env... ";
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $projectRoot . "/.env.backup.{$timestamp}";
    
    if (copy($projectRoot . '/.env', $backupFile)) {
        echo "✅ Backed up to .env.backup.{$timestamp}\n";
        $backupCreated = true;
    } else {
        echo "⚠️ Could not create backup\n";
    }
}

// Step 2: Remove .env file
echo "📋 Removing .env file... ";
if (file_exists($projectRoot . '/.env')) {
    if (unlink($projectRoot . '/.env')) {
        echo "✅ Removed\n";
    } else {
        echo "❌ Failed to remove\n";
    }
} else {
    echo "✅ Not found (already clean)\n";
}

// Step 3: Clear Laravel caches
echo "📋 Clearing Laravel caches... ";
$cacheCommands = [
    'php artisan cache:clear 2>/dev/null',
    'php artisan config:clear 2>/dev/null',
    'php artisan route:clear 2>/dev/null',
    'php artisan view:clear 2>/dev/null',
    'php artisan clear-compiled 2>/dev/null'
];

foreach ($cacheCommands as $command) {
    shell_exec("cd {$projectRoot} && {$command}");
}
echo "✅ Cleared\n";

// Step 4: Reset SQLite database
echo "📋 Resetting SQLite database... ";
$dbFile = $projectRoot . '/database/database.sqlite';

if (file_exists($dbFile)) {
    if (unlink($dbFile)) {
        echo "✅ Database reset\n";
    } else {
        echo "❌ Could not remove database file\n";
    }
} else {
    echo "✅ No database file found\n";
}

// Step 5: Remove bootstrap cache files
echo "📋 Clearing bootstrap cache... ";
$bootstrapCache = $projectRoot . '/bootstrap/cache';
$cacheFiles = glob($bootstrapCache . '/*.php');

foreach ($cacheFiles as $file) {
    if (basename($file) !== '.gitignore') {
        unlink($file);
    }
}
echo "✅ Cleared\n";

// Step 6: Remove node_modules and package-lock.json for clean npm install
echo "📋 Cleaning Node.js modules... ";
$nodeModules = $projectRoot . '/node_modules';
$packageLock = $projectRoot . '/package-lock.json';

if (is_dir($nodeModules)) {
    // Simple recursive delete for node_modules
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($nodeModules, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    
    if (rmdir($nodeModules)) {
        echo "✅ Node modules removed\n";
    } else {
        echo "⚠️ Could not fully remove node_modules\n";
    }
} else {
    echo "✅ No node_modules found\n";
}

if (file_exists($packageLock)) {
    unlink($packageLock);
}

// Step 7: Clean Composer cache
echo "📋 Clearing Composer cache... ";
shell_exec("cd {$projectRoot} && composer clear-cache 2>/dev/null");
echo "✅ Cleared\n";

// Step 8: Remove vendor autoload cache
echo "📋 Refreshing Composer autoload... ";
shell_exec("cd {$projectRoot} && composer dump-autoload 2>/dev/null");
echo "✅ Refreshed\n";

echo "\n🎉 Rollback completed successfully!\n\n";

echo "📋 Next Steps:\n";
echo "1. Run: php scripts/check-prerequisites.php\n";
echo "2. Copy: cp .env.example .env\n";
echo "3. Run: php artisan key:generate\n";
echo "4. Run: composer install\n";
echo "5. Run: npm install\n";
echo "6. Run: php artisan thorium90:setup --interactive\n\n";

if ($backupCreated) {
    echo "💾 Your previous .env was backed up and can be restored if needed.\n\n";
}

echo "✅ System is now ready for a fresh installation.\n\n";