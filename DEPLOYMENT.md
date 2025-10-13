# Deployment Instructions for Render.com

## Prerequisites
1. Create a Render.com account
2. Fork this repository to your GitHub account
3. Prepare your MySQL database credentials

## Deploying to Render.com

### Step 1: Create a MySQL Database on Render.com
1. Go to your Render.com dashboard
2. Click "New" → "Database"
3. Choose "MySQL" as the database type
4. Give it a name (e.g., "inventory-db")
5. Select the free plan or a paid plan based on your needs
6. Click "Create Database"

### Step 2: Note Your Database Connection Details
After the database is created, note down these details from the Render.com dashboard:
- External Database URL
- Host
- Port
- Database Name
- Username
- Password

### Step 3: Create a Web Service on Render.com
1. Go to your Render.com dashboard
2. Click "New" → "Web Service"
3. Connect your GitHub repository
4. Give your service a name (e.g., "inventory-api")
5. Select the branch you want to deploy (usually "main" or "master")
6. Set the root directory to the folder containing your Laravel application
7. Select "Docker" as the runtime environment
8. Click "Create Web Service"

### Step 4: Configure Environment Variables
In your web service settings, go to the "Environment" section and add these variables:

```
APP_KEY=base64:JyUoiNJqH6gYGH5p08m4MuyezcdQW2h+wS8uXvBXskc=
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=your-mysql-host.onrender.com
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Replace the DB_* values with the actual connection details from your MySQL database.

### Step 5: Run Migrations
After deployment, you need to run the database migrations:

1. Go to your web service in the Render.com dashboard
2. Click on "Shell" to open a terminal
3. Run these commands:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

### Step 6: Test Your API
After deployment and migration, test your API endpoints:
- Main API endpoint: `https://your-service-name.onrender.com/api/`
- Login endpoint: `https://your-service-name.onrender.com/api/login`

## Troubleshooting

### Database Connection Issues
If you're having database connection issues:
1. Double-check your environment variables
2. Ensure your database is not blocked by a firewall
3. Check that you're using the correct external database URL

### Common Error Messages
- "Unsupported driver [d1]": This means you're trying to use Cloudflare D1 which is not supported. Make sure DB_CONNECTION is set to "mysql".
- "Access denied for user": Check your database username and password.
- "Connection timed out": Check your database host and port settings.

## Local Development
For local development, you can use SQLite by setting these environment variables in your [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) file:
```
DB_CONNECTION=sqlite
DB_DATABASE=../database/database.sqlite
```

Then run:
```bash
php artisan migrate
php artisan db:seed
```