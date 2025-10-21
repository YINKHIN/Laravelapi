# Forgot Password API Documentation

This document explains the API endpoints for the forgot password functionality in the Inventory Management System.

## Endpoints

### 1. Request Password Reset
```
POST /api/auth/forgot-password
```

#### Request Body
```json
{
  "email": "user@example.com"
}
```

#### Response
```json
{
  "success": true,
  "message": "Password reset instructions have been sent to your email address."
}
```

#### Error Responses
```json
{
  "success": false,
  "message": "Validation Error",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

```json
{
  "success": false,
  "message": "Validation Error",
  "errors": {
    "email": [
      "The selected email is invalid."
    ]
  }
}
```

### 2. Reset Password
```
POST /api/auth/reset-password
```

#### Request Body
```json
{
  "email": "user@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### Response
```json
{
  "success": true,
  "message": "Your password has been reset successfully. You can now login with your new password."
}
```

#### Error Responses
```json
{
  "success": false,
  "message": "Validation Error",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

```json
{
  "success": false,
  "message": "Validation Error",
  "errors": {
    "password": [
      "The password confirmation does not match."
    ]
  }
}
```

## Implementation Details

### Current Implementation
The current implementation is a simplified version that:
1. Validates the email exists in the database
2. Updates the password directly without token validation
3. Returns success messages

### Full Implementation Requirements
For a production-ready implementation, the following would be needed:

1. **Password Reset Token Generation**
   - Generate a secure, time-limited token
   - Store the token in the database with expiration time
   - Associate the token with the user's email

2. **Email Sending**
   - Send an email with a reset link containing the token
   - Include expiration information in the email

3. **Token Validation**
   - Validate the token when the user clicks the reset link
   - Check token expiration
   - Ensure the token matches the user's email

4. **Security Enhancements**
   - Rate limiting for password reset requests
   - Logging of password reset attempts
   - CAPTCHA integration to prevent abuse

## Database Considerations

### Current Schema
The users table already has the required fields:
- `email` (string, unique)
- `password` (string)

### Additional Schema for Full Implementation
For a complete implementation, you might want to add a password reset tokens table:

```php
Schema::create('password_reset_tokens', function (Blueprint $table) {
    $table->string('email')->primary();
    $table->string('token');
    $table->timestamp('created_at')->nullable();
});
```

## Security Recommendations

1. **Token Security**
   - Use cryptographically secure random tokens
   - Set short expiration times (1-2 hours)
   - Hash tokens before storing in database

2. **Rate Limiting**
   - Limit password reset requests per IP
   - Limit requests per email address
   - Implement exponential backoff for repeated attempts

3. **User Privacy**
   - Don't reveal if an email exists in the system
   - Use generic success messages
   - Log all password reset attempts

4. **Password Requirements**
   - Enforce strong password policies
   - Check against common password lists
   - Prevent password reuse

## Testing

### Test Cases

1. **Valid Email Request**
   - Send request with valid email
   - Expect success response

2. **Invalid Email Request**
   - Send request with invalid email format
   - Expect validation error

3. **Non-existent Email Request**
   - Send request with non-existent email
   - Expect validation error

4. **Valid Password Reset**
   - Send reset request with valid data
   - Expect success response

5. **Password Mismatch**
   - Send reset request with mismatched passwords
   - Expect validation error

6. **Weak Password**
   - Send reset request with weak password
   - Expect validation error

## Integration with Frontend

The frontend components already implement:
- Form validation
- User feedback messages
- Navigation between steps
- Loading states

The API endpoints are designed to work seamlessly with the existing frontend implementation.