<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== List All Users ===\n\n";

// Check database connection
try {
    // Bootstrap Laravel
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Test database connection
    DB::connection()->getPdo();
    echo "✅ Database connection: SUCCESS\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in the .env file.\n";
    exit(1);
}

try {
    // Get all users
    $users = User::all();
    
    if ($users->isEmpty()) {
        echo "No users found in the database.\n";
        exit;
    }
    
    echo "Found " . $users->count() . " user(s):\n\n";
    
    foreach ($users as $user) {
        echo "ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Email: {$user->email}\n";
        echo "Type: {$user->type}\n";
        echo "Created: {$user->created_at}\n";
        echo "------------------------\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error listing users: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
}