<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Check if email already exists
    if (User::where('email', 'yinkhin1999@gmail.com')->exists()) {
        echo "❌ Error: Email already exists!\n";
        exit;
    }
    
    // Create admin user
    $user = User::create([
        'name' => 'Yin Khin',
        'email' => 'yinkhin1999@gmail.com',
        'password' => Hash::make('khin1999'),
        'type' => 'admin',
    ]);
    
    echo "\n✅ Admin user created successfully!\n";
    echo "📧 Email: yinkhin1999@gmail.com\n";
    echo "🔑 Password: khin1999\n";
    echo "👤 Type: admin\n";
    echo "🆔 User ID: {$user->id}\n";
    
    // Test login
    echo "\n=== Testing Login ===\n";
    if (Hash::check('khin1999', $user->password)) {
        echo "✅ Password verification: SUCCESS\n";
        echo "🎯 You can now login with these credentials!\n";
    } else {
        echo "❌ Password verification: FAILED\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
}