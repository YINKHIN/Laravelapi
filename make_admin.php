<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Make User Admin ===\n\n";

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

// Get user input
echo "Enter email of the user to make admin: ";
$email = trim(fgets(STDIN));

try {
    // Check if user exists
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "❌ Error: User with email '$email' not found!\n";
        exit(1);
    }
    
    echo "User found: {$user->name} ({$user->email}) - Current Type: {$user->type}\n\n";
    
    // Confirm action
    echo "Make this user an admin? (y/n): ";
    $confirm = trim(fgets(STDIN));
    
    if (strtolower($confirm) !== 'y' && strtolower($confirm) !== 'yes') {
        echo "Operation cancelled.\n";
        exit;
    }
    
    // Make user admin
    $user->type = 'admin';
    $user->save();
    
    echo "\n✅ User successfully made admin!\n";
    echo "📧 Email: $email\n";
    echo "👤 Name: {$user->name}\n";
    echo "👑 New Type: {$user->type}\n";
    echo "🆔 User ID: {$user->id}\n";
    
} catch (Exception $e) {
    echo "❌ Error making user admin: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
}