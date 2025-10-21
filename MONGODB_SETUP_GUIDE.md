# MongoDB Setup Guide

This guide explains how to set up MongoDB for your Laravel application when you're ready to deploy to production.

## Prerequisites

1. MongoDB server installed and running
2. PHP MongoDB extension installed
3. Composer package manager

## Installation Steps

### 1. Install MongoDB PHP Extension

#### On Windows (XAMPP):
1. Download the MongoDB PHP extension from [PECL](https://pecl.php.net/package/mongodb)
2. Extract the appropriate `.dll` file for your PHP version
3. Copy it to your PHP extensions directory (usually `C:\xampp\php\ext\`)
4. Add `extension=php_mongodb.dll` to your `php.ini` file
5. Restart Apache

#### On Linux/Ubuntu:
```bash
sudo apt-get install php-mongodb
```

#### On macOS:
```bash
brew install php-mongodb
```

### 2. Install Laravel MongoDB Package

After installing the PHP extension, run:
```bash
composer require mongodb/laravel-mongodb
```

### 3. Configure Environment Variables

Update your [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) file:
```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=management_system
DB_USERNAME=
DB_PASSWORD=
```

### 4. Update Database Configuration

Add the MongoDB connection to `config/database.php`:
```php
'mongodb' => [
    'driver' => 'mongodb',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 27017),
    'database' => env('DB_DATABASE', 'management_system'),
    'username' => env('DB_USERNAME', ''),
    'password' => env('DB_PASSWORD', ''),
    'options' => [
        'database' => env('DB_AUTHENTICATION_DATABASE', 'admin'),
    ],
],
```

### 5. Update Service Providers

Add the MongoDB service provider to `bootstrap/providers.php`:
```php
return [
    // ... other providers
    MongoDB\Laravel\MongoDBServiceProvider::class,
];
```

### 6. Update Models

Update your models to extend `MongoDB\Laravel\Eloquent\Model` instead of `Illuminate\Database\Eloquent\Model`.

Example for User model:
```php
<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, JWTSubject
{
    use Authenticatable, Authorizable, CanResetPassword, HasApiTokens;
    
    // ... rest of the model
}
```

## Current Fallback Configuration

For local development, the application is currently configured to use SQLite as a fallback. This configuration is in your [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) file:

```env
DB_CONNECTION=sqlite
DB_DATABASE=./database/database.sqlite
```

## Production Deployment with MongoDB

When you're ready to deploy with MongoDB:

1. Set up a MongoDB database (local or cloud like MongoDB Atlas)
2. Update your production [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) file with MongoDB credentials
3. Ensure the MongoDB PHP extension is installed on your production server
4. Run `php artisan config:clear` to clear the configuration cache

## Troubleshooting

### Common Issues:

1. **"Class 'MongoDB\Driver\Manager' not found"**
   - The MongoDB PHP extension is not installed or enabled
   - Solution: Install and enable the extension as described above

2. **Connection refused**
   - MongoDB server is not running
   - Solution: Start the MongoDB service

3. **Authentication failed**
   - Incorrect username/password in [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) file
   - Solution: Verify your credentials

## MongoDB Atlas Configuration

If you're using MongoDB Atlas, your [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) configuration would look like:

```env
DB_CONNECTION=mongodb
DB_URI=mongodb+srv://username:password@cluster0.xxxxx.mongodb.net/management_system?retryWrites=true&w=majority
```

And in `config/database.php`:
```php
'mongodb' => [
    'driver' => 'mongodb',
    'uri' => env('DB_URI'),
    'database' => env('DB_DATABASE', 'management_system'),
],
```

## Security Considerations

1. Never commit sensitive credentials to version control
2. Use environment variables for all database credentials
3. Enable authentication on your MongoDB server
4. Use strong passwords for database users
5. Restrict network access to your MongoDB server