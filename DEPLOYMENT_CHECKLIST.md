# Deployment Checklist

## Before Deployment

- [ ] Update all environment variables in [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) file
- [ ] Verify database configuration is correct
- [ ] Check Cloudflare R2 credentials
- [ ] Ensure all API keys and secrets are properly set
- [ ] Run local tests to ensure application works correctly
- [ ] Commit all changes to Git repository

## Database Setup

- [ ] Create MySQL database on Render.com
- [ ] Note database connection details (host, port, name, username, password)
- [ ] Update environment variables in Render.com dashboard
- [ ] Run migrations after deployment:
  ```bash
  php artisan migrate --force
  ```
- [ ] Run seeders to populate initial data:
  ```bash
  php artisan db:seed --force
  ```

## Render.com Deployment

### Web Service
- [ ] Create new Web Service
- [ ] Connect GitHub repository
- [ ] Set build command: `composer install --optimize-autoloader --no-dev`
- [ ] Set start command: `apache2-foreground`
- [ ] Configure environment variables:
  - `APP_KEY` (use existing key or generate new one)
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `DB_CONNECTION=mysql`
  - `DB_HOST=your-mysql-host.onrender.com`
  - `DB_PORT=3306`
  - `DB_DATABASE=your_database_name`
  - `DB_USERNAME=your_username`
  - `DB_PASSWORD=your_password`
  - `R2_ACCESS_KEY_ID=your_r2_access_key`
  - `R2_SECRET_ACCESS_KEY=your_r2_secret_key`
  - `R2_BUCKET=your_bucket_name`
  - `R2_ENDPOINT=your_r2_endpoint`
  - `R2_URL=your_r2_public_url`

### Database
- [ ] Create MySQL database
- [ ] Configure database environment variables in web service

## After Deployment

- [ ] Test API endpoints
- [ ] Test database connectivity
- [ ] Test file uploads to Cloudflare R2
- [ ] Test user authentication
- [ ] Test admin functionality
- [ ] Verify health check endpoints:
  - `https://your-app.onrender.com/health`
  - `https://your-app.onrender.com/api/health`

## Common Issues and Solutions

### Database Connection Issues
- **Error**: "Unsupported driver [d1]"
  - **Solution**: Ensure `DB_CONNECTION` is set to `mysql`, not `d1`

- **Error**: "Access denied for user"
  - **Solution**: Verify database username and password

- **Error**: "Connection timed out"
  - **Solution**: Check database host and port settings

### Environment Variables
- **Issue**: Variables not being read correctly
  - **Solution**: Ensure variables are properly escaped and don't contain special characters

### File Storage (Cloudflare R2)
- **Issue**: File uploads failing
  - **Solution**: Verify R2 credentials and bucket permissions

## Useful Commands for Debugging

```bash
# Test database connection
php test-db-connection.php

# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate new app key
php artisan key:generate --show
```

## Health Check Endpoints

- Web health check: `https://your-app.onrender.com/health`
- API health check: `https://your-app.onrender.com/api/health`
- Ping endpoint: `https://your-app.onrender.com/ping`

These endpoints return JSON responses indicating the status of the application.