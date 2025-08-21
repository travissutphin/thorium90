<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Exception;

class DatabaseConfigurationService
{
    /**
     * Validate the current database configuration
     */
    public function validateConfiguration(): array
    {
        $results = [
            'valid' => true,
            'warnings' => [],
            'errors' => [],
            'info' => []
        ];

        // Check database connection
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            
            $results['info']['driver'] = $driver;
            $results['info']['database'] = $connection->getDatabaseName();
            
            // Driver-specific validations
            switch ($driver) {
                case 'mysql':
                    $this->validateMySQLConfiguration($connection, $results);
                    break;
                case 'pgsql':
                    $this->validatePostgreSQLConfiguration($connection, $results);
                    break;
                case 'sqlite':
                    $this->validateSQLiteConfiguration($connection, $results);
                    break;
            }
            
        } catch (Exception $e) {
            $results['valid'] = false;
            $results['errors'][] = "Database connection failed: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Validate MySQL-specific configuration
     */
    private function validateMySQLConfiguration($connection, array &$results): void
    {
        try {
            // Check MySQL version
            $version = $connection->select('SELECT VERSION() as version')[0]->version;
            $results['info']['version'] = $version;
            
            $majorVersion = (float) $version;
            if ($majorVersion < 8.0) {
                $results['warnings'][] = "MySQL version {$version} detected. MySQL 8.0+ recommended for optimal performance.";
            }

            // Check character set
            $charset = $connection->select('SELECT @@character_set_database as charset')[0]->charset;
            $results['info']['charset'] = $charset;
            
            if ($charset !== 'utf8mb4') {
                $results['warnings'][] = "Database charset is '{$charset}'. 'utf8mb4' recommended for full Unicode support.";
            }

            // Check collation
            $collation = $connection->select('SELECT @@collation_database as collation')[0]->collation;
            $results['info']['collation'] = $collation;
            
            if (!str_contains($collation, 'utf8mb4')) {
                $results['warnings'][] = "Database collation is '{$collation}'. 'utf8mb4_unicode_ci' recommended.";
            }

            // Check InnoDB availability
            $engines = $connection->select('SHOW ENGINES');
            $innodbAvailable = false;
            foreach ($engines as $engine) {
                if ($engine->Engine === 'InnoDB' && in_array($engine->Support, ['YES', 'DEFAULT'])) {
                    $innodbAvailable = true;
                    break;
                }
            }
            
            if (!$innodbAvailable) {
                $results['errors'][] = "InnoDB storage engine is not available. This is required for proper foreign key support.";
                $results['valid'] = false;
            }

            // Check SQL mode
            $sqlMode = $connection->select('SELECT @@sql_mode as mode')[0]->mode;
            $results['info']['sql_mode'] = $sqlMode;
            
            if (!str_contains($sqlMode, 'STRICT_TRANS_TABLES')) {
                $results['warnings'][] = "SQL mode doesn't include STRICT_TRANS_TABLES. This may cause data integrity issues.";
            }

        } catch (Exception $e) {
            $results['warnings'][] = "Could not validate MySQL configuration: " . $e->getMessage();
        }
    }

    /**
     * Validate PostgreSQL-specific configuration
     */
    private function validatePostgreSQLConfiguration($connection, array &$results): void
    {
        try {
            // Check PostgreSQL version
            $version = $connection->select('SELECT version()')[0]->version;
            $results['info']['version'] = $version;
            
            if (!preg_match('/PostgreSQL (\d+\.\d+)/', $version, $matches)) {
                $results['warnings'][] = "Could not determine PostgreSQL version.";
                return;
            }
            
            $versionNumber = (float) $matches[1];
            if ($versionNumber < 14.0) {
                $results['warnings'][] = "PostgreSQL version {$versionNumber} detected. PostgreSQL 14+ recommended.";
            }

            // Check encoding
            $encoding = $connection->select('SHOW server_encoding')[0]->server_encoding;
            $results['info']['encoding'] = $encoding;
            
            if ($encoding !== 'UTF8') {
                $results['warnings'][] = "Database encoding is '{$encoding}'. 'UTF8' recommended.";
            }

        } catch (Exception $e) {
            $results['warnings'][] = "Could not validate PostgreSQL configuration: " . $e->getMessage();
        }
    }

    /**
     * Validate SQLite-specific configuration
     */
    private function validateSQLiteConfiguration($connection, array &$results): void
    {
        try {
            // Check SQLite version
            $version = $connection->select('SELECT sqlite_version() as version')[0]->version;
            $results['info']['version'] = $version;
            
            $versionNumber = (float) $version;
            if ($versionNumber < 3.35) {
                $results['warnings'][] = "SQLite version {$version} detected. SQLite 3.35+ recommended.";
            }

            // Check if foreign keys are enabled
            $foreignKeys = $connection->select('PRAGMA foreign_keys')[0]->foreign_keys;
            $results['info']['foreign_keys'] = $foreignKeys ? 'enabled' : 'disabled';
            
            if (!$foreignKeys) {
                $results['warnings'][] = "Foreign key constraints are disabled. This may cause data integrity issues.";
            }

            // Production warning for SQLite
            if (config('app.env') === 'production') {
                $results['warnings'][] = "SQLite is not recommended for production environments. Consider using MySQL or PostgreSQL.";
            }

        } catch (Exception $e) {
            $results['warnings'][] = "Could not validate SQLite configuration: " . $e->getMessage();
        }
    }

    /**
     * Check PHP extensions required for database drivers
     */
    public function checkRequiredExtensions(): array
    {
        $results = [
            'mysql' => [
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'mysqli' => extension_loaded('mysqli')
            ],
            'pgsql' => [
                'pdo_pgsql' => extension_loaded('pdo_pgsql'),
                'pgsql' => extension_loaded('pgsql')
            ],
            'sqlite' => [
                'pdo_sqlite' => extension_loaded('pdo_sqlite'),
                'sqlite3' => extension_loaded('sqlite3')
            ]
        ];

        return $results;
    }

    /**
     * Get recommended production settings for current driver
     */
    public function getProductionRecommendations(): array
    {
        $driver = Config::get('database.default');
        
        $recommendations = [
            'mysql' => [
                'Use InnoDB storage engine for all tables',
                'Set charset to utf8mb4 for full Unicode support',
                'Enable strict SQL mode (STRICT_TRANS_TABLES)',
                'Configure connection pooling for high-traffic applications',
                'Set up master-slave replication for read scaling',
                'Configure regular backups with binary logging',
                'Use SSL/TLS for database connections in production'
            ],
            'pgsql' => [
                'Use UTF8 encoding for database',
                'Configure connection pooling (pgpool-II or similar)',
                'Set up streaming replication for high availability',
                'Configure regular backups with point-in-time recovery',
                'Use SSL/TLS for database connections in production',
                'Tune shared_buffers and work_mem for your workload'
            ],
            'sqlite' => [
                'Not recommended for production use',
                'Consider migrating to MySQL or PostgreSQL',
                'If using SQLite, enable WAL mode for better concurrency',
                'Set up regular file-based backups',
                'Monitor database file size and performance'
            ]
        ];

        return $recommendations[$driver] ?? [];
    }
}