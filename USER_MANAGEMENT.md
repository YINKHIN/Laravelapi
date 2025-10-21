# User Management Guide

This guide explains how to manage users in your Inventory Management System, including creating new admin users, resetting passwords, and managing user roles.

## Prerequisites

1. Make sure your Laravel application is properly configured
2. Database migrations have been run
3. You're in the `invetory_api` directory

## Available Scripts

### 1. List All Users
To see all users in the system:
```bash
php list_users.php
```

### 2. Create New Admin User
To create a new admin user:
```bash
php create_new_admin.php
```
Follow the prompts to enter:
- Full name
- Email
- Password
- Phone (optional)
- Address (optional)

### 3. Reset User Password
To reset a forgotten password:
```bash
php reset_password.php
```
Follow the prompts to enter:
- Email of the user
- New password
- Confirm new password

### 4. Make User Admin
To make an existing user an admin:
```bash
php make_admin.php
```
Follow the prompts to enter:
- Email of the user to make admin

## Default Admin User

If you need to quickly create a default admin user, you can use this command:
```bash
php create_new_admin.php
```

With the following details:
- Name: Admin User
- Email: admin@inventory.com
- Password: password

## Common Issues and Solutions

### Database Connection Errors
If you encounter database connection errors:
1. Check your [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) file configuration
2. Ensure your database server is running
3. Run `php artisan config:clear` to clear configuration cache

### User Already Exists
If you get an "Email already exists" error:
1. Use `php list_users.php` to see existing users
2. Use `php reset_password.php` to reset the password instead
3. Or use a different email address

### Password Requirements
Passwords must be at least 6 characters long for security.

## Security Recommendations

1. Change default passwords immediately after setup
2. Use strong, unique passwords for admin accounts
3. Regularly review user accounts and permissions
4. Remove unused accounts
5. Enable two-factor authentication if available

## Production Considerations

For production environments:
1. Never use default credentials
2. Implement proper password policies
3. Regularly audit user accounts
4. Use HTTPS for all authentication
5. Implement rate limiting for login attempts

## Troubleshooting

### Script Not Found
If you get "Could not open input file" error:
- Make sure you're in the `invetory_api` directory
- Check that the script files exist

### Permission Denied
If you get permission errors:
- Make sure PHP has execute permissions
- On Windows, run as Administrator if needed

### Database Errors
If you get database errors:
- Verify database connection settings in [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env)
- Ensure the database server is running
- Check that migrations have been run