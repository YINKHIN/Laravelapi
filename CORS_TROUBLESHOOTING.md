# CORS Troubleshooting Guide

## Common CORS Issues and Solutions

### 1. "No 'Access-Control-Allow-Origin' header is present on the requested resource"

**Problem**: The browser is blocking requests from your frontend to your backend API due to CORS policy.

**Solutions**:

1. **Check CORS Configuration**:
   - Ensure `config/cors.php` includes your frontend domain in `allowed_origins`
   - Verify the paths in `paths` array include your API routes

2. **Verify Environment Variables**:
   - Check that `SANCTUM_STATEFUL_DOMAINS` in [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) includes your frontend domains
   - Ensure `FRONTEND_URL` is correctly set

3. **Check Vercel Configuration**:
   - Verify `vercel.json` includes proper CORS headers
   - Ensure `SANCTUM_STATEFUL_DOMAINS` in vercel.json includes your frontend domains

### 2. "Response to preflight request doesn't pass access control check"

**Problem**: The OPTIONS preflight request is failing.

**Solutions**:

1. **Add CORS Middleware**:
   - Ensure `CorsMiddleware` is registered in `bootstrap/app.php`
   - Verify the middleware sets proper CORS headers

2. **Check HTTP Methods**:
   - Ensure `allowed_methods` in `config/cors.php` includes the methods you're using
   - Verify your routes accept the correct HTTP methods

### 3. Local Development vs Production Issues

**Problem**: CORS works locally but fails in production.

**Solutions**:

1. **Environment-Specific Configuration**:
   - Use different configurations for local and production environments
   - Ensure all domains (localhost, vercel.app, etc.) are included in allowed origins

2. **Check URLs**:
   - Verify frontend and backend URLs are consistent across environments
   - Ensure `VITE_API_URL` in frontend [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_react/.env) points to the correct backend

## Testing CORS Configuration

### 1. Manual Testing
You can test CORS by making a simple request from your browser console:

```javascript
fetch('https://your-api-domain.com/api/cors-test')
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));
```

### 2. Using curl
Test preflight requests:

```bash
curl -X OPTIONS \
  -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type, Authorization" \
  https://your-api-domain.com/api/auth/login
```

## Debugging Steps

### 1. Check Browser Developer Tools
1. Open Network tab
2. Look for the failed request
3. Check the Response Headers for CORS headers
4. Check the Request Headers for Origin header

### 2. Check Backend Logs
1. Look for any errors in Laravel logs
2. Check if the request is reaching your application
3. Verify middleware is being executed

### 3. Verify Configuration Files
1. Check `config/cors.php`
2. Check [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) configuration
3. Check `vercel.json` headers
4. Check middleware registration

## Common Configuration Examples

### config/cors.php
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'https://your-production-domain.vercel.app',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### vercel.json Headers
```json
{
  "headers": [
    {
      "source": "/api/(.*)",
      "headers": [
        {
          "key": "Access-Control-Allow-Credentials",
          "value": "true"
        },
        {
          "key": "Access-Control-Allow-Origin",
          "value": "*"
        },
        {
          "key": "Access-Control-Allow-Methods",
          "value": "GET,OPTIONS,PATCH,DELETE,POST,PUT"
        },
        {
          "key": "Access-Control-Allow-Headers",
          "value": "X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version, Authorization"
        }
      ]
    }
  ]
}
```

## Production Deployment Checklist

- [ ] Update `SANCTUM_STATEFUL_DOMAINS` with production domains
- [ ] Update `allowed_origins` in `config/cors.php` with production domains
- [ ] Verify `APP_URL` in [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_api/.env) points to production URL
- [ ] Check that `VITE_API_URL` in frontend [.env](file:///C:/xampp/htdocs/soft_se/project_SE/invetory_react/.env) points to production API
- [ ] Test CORS with production URLs
- [ ] Verify all subdomains are included if needed