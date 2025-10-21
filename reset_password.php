<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Reset User Password ===\n\n";

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
echo "Enter email of the user to reset password: ";
$email = trim(fgets(STDIN));

try {
    // Check if user exists
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "❌ Error: User with email '$email' not found!\n";
        exit(1);
    }
    
    echo "User found: {$user->name} ({$user->email}) - Type: {$user->type}\n\n";
    
    echo "Enter new password: ";
    $newPassword = trim(fgets(STDIN));
    
    // Validate password length
    if (strlen($newPassword) < 6) {
        echo "❌ Error: Password must be at least 6 characters long!\n";
        exit(1);
    }
    
    // Confirm password
    echo "Confirm new password: ";
    $confirmPassword = trim(fgets(STDIN));
    
    if ($newPassword !== $confirmPassword) {
        echo "❌ Error: Passwords do not match!\n";
        exit(1);
    }
    
    // Update password
    $user->password = Hash::make($newPassword);
    $user->save();
    
    echo "\n✅ Password reset successfully!\n";
    echo "📧 Email: $email\n";
    echo "🔑 New Password: $newPassword\n";
    echo "👤 Type: {$user->type}\n";
    echo "🆔 User ID: {$user->id}\n";
    
    // Test login
    echo "\n=== Testing Login ===\n";
    if (Hash::check($newPassword, $user->password)) {
        echo "✅ Password verification: SUCCESS\n";
        echo "🎯 You can now login with the new password!\n";
    } else {
        echo "❌ Password verification: FAILED\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error resetting password: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
}