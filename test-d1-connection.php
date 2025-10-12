<?php

require_once 'vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create a service container
$container = new Container;
$container->instance('app', $container);

// Create a database manager instance
$dbManager = new DatabaseManager($container, new Dispatcher($container));

// Register the D1 connector
$dbManager->extend('d1', function ($config, $name) {
    $config['name'] = $name;
    
    // Check if required config values are present
    if (!isset($config['database']) || !isset($config['auth']['token']) || !isset($config['auth']['account_id'])) {
        throw new Exception('Missing required D1 configuration values');
    }
    
    echo "D1 configuration:\n";
    echo "Database ID: " . $config['database'] . "\n";
    echo "Account ID: " . $config['auth']['account_id'] . "\n";
    echo "API URL: " . ($config['api'] ?? 'https://api.cloudflare.com/client/v4') . "\n";
    
    // Return a mock connection for now
    return new class {
        public function getPdo() {
            return new class {
                public function query($sql) {
                    echo "Executing query: $sql\n";
                    return true;
                }
            };
        }
        
        public function select($sql) {
            echo "Executing select query: $sql\n";
            return [['test' => 1]];
        }
    };
});

// Test the connection
try {
    $config = [
        'driver' => 'd1',
        'database' => $_ENV['CLOUDFLARE_D1_DATABASE_ID'] ?? '',
        'api' => 'https://api.cloudflare.com/client/v4',
        'auth' => [
            'token' => $_ENV['CLOUDFLARE_TOKEN'] ?? '',
            'account_id' => $_ENV['CLOUDFLARE_ACCOUNT_ID'] ?? '',
        ],
    ];
    
    echo "Attempting to create D1 connection...\n";
    $connection = $dbManager->connect($config);
    echo "Successfully created D1 connection!\n";
    
    // Try a simple query
    $result = $connection->select('SELECT 1 as test');
    echo "Query result: " . print_r($result, true) . "\n";
} catch (Exception $e) {
    echo "Error connecting to Cloudflare D1 database: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}