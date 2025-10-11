<?php
// This script runs the database migrations
// Run this script from the project root directory

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run migrations
try {
    $exitCode = $kernel->call('migrate', [
        '--force' => true
    ]);
    
    echo "Migrations completed successfully!\n";
    echo $kernel->output();
} catch (Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    exit(1);
}
?>