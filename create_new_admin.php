<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Creating New Admin User ===\n\n";

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
echo "Enter full name: ";
$name = trim(fgets(STDIN));

echo "Enter email: ";
$email = trim(fgets(STDIN));

echo "Enter password: ";
$password = trim(fgets(STDIN));

echo "Enter phone (optional): ";
$phone = trim(fgets(STDIN));

echo "Enter address (optional): ";
$address = trim(fgets(STDIN));

try {
    // Check if email already exists
    if (User::where('email', $email)->exists()) {
        echo "❌ Error: Email already exists!\n";
        exit;
    }
    
    // Create admin user
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
        'type' => 'admin',
    ]);
    
    // Create profile if phone or address provided
    if (!empty($phone) || !empty($address)) {
        Profile::create([
            'user_id' => $user->id,
            'phone' => $phone ?: null,
            'address' => $address ?: null,
        ]);
    }
    
    echo "\n✅ Admin user created successfully!\n";
    echo "📧 Email: $email\n";
    echo "🔑 Password: $password\n";
    echo "👤 Type: admin\n";
    echo "🆔 User ID: {$user->id}\n";
    
    // Test login
    echo "\n=== Testing Login ===\n";
    if (Hash::check($password, $user->password)) {
        echo "✅ Password verification: SUCCESS\n";
        echo "🎯 You can now login with these credentials!\n";
    } else {
        echo "❌ Password verification: FAILED\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
}