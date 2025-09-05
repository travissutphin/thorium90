#!/usr/bin/env node

/**
 * Thorium90 Health Check Script
 * 
 * Validates system requirements and project configuration
 * for local development environment.
 */

import { execSync } from 'child_process';
import { existsSync, accessSync, constants, readFileSync } from 'fs';
import { join, resolve } from 'path';
import { fileURLToPath } from 'url';
import { dirname } from 'path';
import { createRequire } from 'module';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = resolve(__dirname, '..');

// Colors for console output
const colors = {
    reset: '\x1b[0m',
    bright: '\x1b[1m',
    red: '\x1b[31m',
    green: '\x1b[32m',
    yellow: '\x1b[33m',
    blue: '\x1b[34m',
    magenta: '\x1b[35m',
    cyan: '\x1b[36m'
};

class HealthChecker {
    constructor() {
        this.issues = [];
        this.warnings = [];
        this.passed = [];
    }

    log(message, color = colors.reset) {
        console.log(`${color}${message}${colors.reset}`);
    }

    success(message) {
        this.log(`✅ ${message}`, colors.green);
        this.passed.push(message);
    }

    error(message) {
        this.log(`❌ ${message}`, colors.red);
        this.issues.push(message);
    }

    warning(message) {
        this.log(`⚠️  ${message}`, colors.yellow);
        this.warnings.push(message);
    }

    info(message) {
        this.log(`ℹ️  ${message}`, colors.blue);
    }

    header(message) {
        this.log(`\n${colors.bright}${colors.cyan}${message}${colors.reset}`);
    }

    /**
     * Execute shell command safely
     */
    execCommand(command, options = {}) {
        try {
            const result = execSync(command, {
                encoding: 'utf8',
                stdio: 'pipe',
                cwd: projectRoot,
                ...options
            });
            return result.trim();
        } catch (error) {
            return null;
        }
    }

    /**
     * Check if a file/directory exists and is accessible
     */
    checkPath(path, description, required = true) {
        const fullPath = resolve(projectRoot, path);
        
        if (!existsSync(fullPath)) {
            if (required) {
                this.error(`${description} not found: ${path}`);
            } else {
                this.warning(`${description} not found (optional): ${path}`);
            }
            return false;
        }

        try {
            accessSync(fullPath, constants.R_OK);
            this.success(`${description} exists and readable: ${path}`);
            return true;
        } catch {
            this.error(`${description} exists but not readable: ${path}`);
            return false;
        }
    }

    /**
     * Check if a directory is writable
     */
    checkWritable(path, description) {
        const fullPath = resolve(projectRoot, path);
        
        if (!existsSync(fullPath)) {
            this.error(`${description} directory doesn't exist: ${path}`);
            return false;
        }

        try {
            accessSync(fullPath, constants.W_OK);
            this.success(`${description} is writable: ${path}`);
            return true;
        } catch {
            this.error(`${description} is not writable: ${path}`);
            return false;
        }
    }

    /**
     * Check Node.js version
     */
    checkNodeVersion() {
        this.header('Node.js Environment');
        
        const nodeVersion = process.version.slice(1); // Remove 'v' prefix
        const [major, minor] = nodeVersion.split('.').map(Number);
        
        if (major >= 16) {
            this.success(`Node.js version: ${nodeVersion} (>= 16.0.0)`);
        } else {
            this.error(`Node.js version: ${nodeVersion} (requires >= 16.0.0)`);
            this.info('Update Node.js: https://nodejs.org');
        }

        // Check npm version
        const npmVersion = this.execCommand('npm --version');
        if (npmVersion) {
            this.success(`npm version: ${npmVersion}`);
        } else {
            this.error('npm not available');
        }

        // Check if node_modules exists
        this.checkPath('node_modules', 'Node modules directory', false);
        
        // Check package.json
        if (this.checkPath('package.json', 'Package configuration')) {
            try {
                const packageJsonPath = join(projectRoot, 'package.json');
                const packageJsonContent = readFileSync(packageJsonPath, 'utf8');
                const packageJson = JSON.parse(packageJsonContent);
                this.success(`Project type: ${packageJson.type || 'commonjs'}`);
                
                // Check if dependencies are installed
                const nodeModulesExists = existsSync(join(projectRoot, 'node_modules'));
                if (!nodeModulesExists) {
                    this.warning('Dependencies not installed. Run: npm install');
                } else {
                    // Validate key dependencies exist
                    const criticalDeps = ['react', 'vite', '@inertiajs/react'];
                    const missingDeps = criticalDeps.filter(dep => 
                        !existsSync(join(projectRoot, 'node_modules', dep))
                    );
                    
                    if (missingDeps.length === 0) {
                        this.success('Critical dependencies installed');
                    } else {
                        this.warning(`Missing dependencies: ${missingDeps.join(', ')}`);
                    }
                }
            } catch (error) {
                this.error(`Invalid package.json file: ${error.message}`);
            }
        }
    }

    /**
     * Check PHP environment
     */
    checkPhpEnvironment() {
        this.header('PHP Environment');
        
        const phpVersion = this.execCommand('php --version');
        if (phpVersion) {
            const versionMatch = phpVersion.match(/PHP (\d+\.\d+\.\d+)/);
            if (versionMatch) {
                const version = versionMatch[1];
                const [major, minor] = version.split('.').map(Number);
                
                if (major > 8 || (major === 8 && minor >= 2)) {
                    this.success(`PHP version: ${version} (>= 8.2.0)`);
                } else {
                    this.error(`PHP version: ${version} (requires >= 8.2.0)`);
                }
            } else {
                this.warning('Could not parse PHP version');
            }
        } else {
            this.error('PHP not found in PATH');
        }

        // Check Composer
        const composerVersion = this.execCommand('composer --version');
        if (composerVersion) {
            const versionMatch = composerVersion.match(/Composer version ([\d\.]+)/);
            if (versionMatch) {
                this.success(`Composer version: ${versionMatch[1]}`);
            } else {
                this.success('Composer available');
            }
        } else {
            this.error('Composer not found in PATH');
            this.info('Install Composer: https://getcomposer.org');
        }

        // Check if vendor directory exists
        this.checkPath('vendor', 'Vendor directory', false);
        if (!existsSync(join(projectRoot, 'vendor'))) {
            this.warning('PHP dependencies not installed. Run: composer install');
        }
    }

    /**
     * Check Laravel/Project specific files
     */
    checkProjectFiles() {
        this.header('Project Files');
        
        // Essential files
        this.checkPath('composer.json', 'Composer configuration');
        this.checkPath('artisan', 'Laravel Artisan CLI');
        
        // Environment files
        if (!this.checkPath('.env', 'Environment file', false)) {
            if (this.checkPath('.env.example', 'Environment example')) {
                this.info('Run: cp .env.example .env');
            }
        }

        // Configuration directories
        this.checkPath('config', 'Configuration directory');
        this.checkPath('app', 'Application directory');
        this.checkPath('resources', 'Resources directory');
        this.checkPath('database', 'Database directory');
        
        // Frontend files
        this.checkPath('vite.config.ts', 'Vite configuration');
        this.checkPath('tailwind.config.js', 'Tailwind configuration', false);
    }

    /**
     * Check directory permissions
     */
    checkPermissions() {
        this.header('Directory Permissions');
        
        const writableDirectories = [
            { path: 'storage', desc: 'Storage directory' },
            { path: 'bootstrap/cache', desc: 'Bootstrap cache' },
            { path: 'database', desc: 'Database directory' }
        ];

        writableDirectories.forEach(({ path, desc }) => {
            this.checkWritable(path, desc);
        });
    }

    /**
     * Check database configuration
     */
    checkDatabase() {
        this.header('Database Configuration');
        
        // Check if database file exists for SQLite
        const sqlitePath = join(projectRoot, 'database', 'database.sqlite');
        if (existsSync(sqlitePath)) {
            this.success('SQLite database file exists');
            
            // Check if writable
            try {
                accessSync(sqlitePath, constants.W_OK);
                this.success('SQLite database is writable');
            } catch {
                this.error('SQLite database is not writable');
            }
        } else {
            this.info('SQLite database not found (will be created during setup)');
        }

        // Check migrations directory
        this.checkPath('database/migrations', 'Migrations directory');
        
        // Check if migrations have conflicts (check conflicts directory)
        const conflictsPath = join(projectRoot, 'database', 'migrations', 'conflicts');
        if (existsSync(conflictsPath)) {
            this.warning('Migration conflicts directory exists - previous conflicts resolved');
        }
    }

    /**
     * Check build tools and assets
     */
    checkBuildTools() {
        this.header('Build Tools & Assets');
        
        // Check if build directory exists
        const publicBuildPath = join(projectRoot, 'public', 'build');
        if (existsSync(publicBuildPath)) {
            this.success('Assets built (public/build exists)');
        } else {
            this.warning('Assets not built. Run: npm run build');
        }

        // Check TypeScript config
        this.checkPath('tsconfig.json', 'TypeScript configuration', false);
        
        // Check ESLint config
        this.checkPath('eslint.config.js', 'ESLint configuration', false);
        
        // Check if we can run build tools
        const canRunVite = this.execCommand('npx vite --version') !== null;
        if (canRunVite) {
            this.success('Vite build tool available');
        } else {
            this.warning('Vite build tool not available (run: npm install)');
        }
    }

    /**
     * Test critical commands
     */
    checkCommands() {
        this.header('Critical Commands Test');
        
        // Test Artisan
        const artisanTest = this.execCommand('php artisan --version');
        if (artisanTest) {
            this.success('Artisan command working');
        } else {
            this.error('Artisan command failed');
        }

        // Test npm scripts
        const packageJsonPath = join(projectRoot, 'package.json');
        if (existsSync(packageJsonPath)) {
            try {
                const packageJsonContent = readFileSync(packageJsonPath, 'utf8');
                const pkg = JSON.parse(packageJsonContent);
                const scripts = pkg.scripts || {};
                
                const criticalScripts = ['build', 'dev', 'types', 'health-check'];
                criticalScripts.forEach(script => {
                    if (scripts[script]) {
                        this.success(`npm script available: ${script}`);
                    } else {
                        this.warning(`npm script missing: ${script}`);
                    }
                });
            } catch (error) {
                this.error(`Could not read package.json scripts: ${error.message}`);
            }
        }
    }

    /**
     * Simple APP_URL validation for common issues
     */
    validateAppUrl() {
        this.header('APP_URL Configuration');
        
        const envPath = join(projectRoot, '.env');
        if (!existsSync(envPath)) {
            this.warning('.env file not found - run thorium90:setup first');
            return;
        }
        
        const envContent = readFileSync(envPath, 'utf8');
        const appUrlMatch = envContent.match(/^APP_URL=(.+)$/m);
        
        if (appUrlMatch) {
            const appUrl = appUrlMatch[1].trim();
            this.info(`Current APP_URL: ${appUrl}`);
            
            // Simple HTTPS warning for local development
            if (appUrl.includes('https://localhost') || appUrl.includes('https://127.0.0.1')) {
                this.warning('HTTPS detected for local development - this may cause media loading issues');
                const httpUrl = appUrl.replace('https://', 'http://');
                this.info(`Quick fix: Change APP_URL to ${httpUrl} then run: php artisan config:clear`);
            } else {
                this.success('APP_URL looks good');
            }
        } else {
            this.warning('APP_URL not found in .env file');
        }
    }

    /**
     * Run all health checks
     */
    async run() {
        this.log(`${colors.bright}${colors.blue}🔍 Thorium90 Health Check${colors.reset}`);
        this.log(`${colors.cyan}Project: ${projectRoot}${colors.reset}\n`);

        // Run all checks
        this.checkNodeVersion();
        this.checkPhpEnvironment();
        this.checkProjectFiles();
        this.checkPermissions();
        this.checkDatabase();
        this.checkBuildTools();
        this.checkCommands();
        this.validateAppUrl();

        // Summary
        this.header('Health Check Summary');
        
        this.log(`${colors.green}✅ Passed: ${this.passed.length} checks${colors.reset}`);
        
        if (this.warnings.length > 0) {
            this.log(`${colors.yellow}⚠️  Warnings: ${this.warnings.length} items${colors.reset}`);
        }
        
        if (this.issues.length > 0) {
            this.log(`${colors.red}❌ Issues: ${this.issues.length} problems${colors.reset}`);
        }

        console.log(''); // Empty line

        // Detailed issues and recommendations
        if (this.issues.length > 0) {
            this.header('🔧 Issues to Fix');
            this.issues.forEach((issue, index) => {
                this.log(`${index + 1}. ${issue}`, colors.red);
            });
            console.log('');
        }

        if (this.warnings.length > 0) {
            this.header('💡 Recommendations');
            this.warnings.forEach((warning, index) => {
                this.log(`${index + 1}. ${warning}`, colors.yellow);
            });
            console.log('');
        }

        // Quick setup commands
        if (this.issues.length > 0 || this.warnings.length > 0) {
            this.header('🚀 Quick Setup Commands');
            
            if (!existsSync(join(projectRoot, '.env'))) {
                this.info('cp .env.example .env');
            }
            
            if (!existsSync(join(projectRoot, 'vendor'))) {
                this.info('composer install');
            }
            
            if (!existsSync(join(projectRoot, 'node_modules'))) {
                this.info('npm install');
            }
            
            if (!existsSync(join(projectRoot, 'public', 'build'))) {
                this.info('npm run build');
            }
            
            this.info('php artisan thorium90:setup --interactive');
            console.log('');
        }

        // Exit code
        const exitCode = this.issues.length > 0 ? 1 : 0;
        
        if (exitCode === 0) {
            this.log(`${colors.bright}${colors.green}🎉 All systems ready for development!${colors.reset}\n`);
        } else {
            this.log(`${colors.bright}${colors.red}🚨 Please resolve the issues above before continuing.${colors.reset}\n`);
        }

        process.exit(exitCode);
    }
}

// Run the health check
const checker = new HealthChecker();
checker.run().catch(error => {
    console.error(`${colors.red}Fatal error during health check:${colors.reset}`, error);
    process.exit(1);
});