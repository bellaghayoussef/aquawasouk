# Aquado API Setup Guide

## Overview

This guide will help you set up and use the Aquado RESTful API for your e-commerce application. The API provides comprehensive endpoints for both users (customers) and vendors.

## Installation

### 1. Install Laravel Sanctum (Already Done)

Laravel Sanctum is already installed via:
```bash
composer require laravel/sanctum
```

### 2. Run Migrations

Run the Sanctum migrations to create the personal access tokens table:

```bash
php artisan migrate
```

### 3. Configure CORS (Optional)

If you're using the API from a different domain, update `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['*'], // Update with your frontend URL in production
```

## Quick Start

### 1. Register a User

**POST** `http://localhost/api/v1/register`

```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer"
}
```

Response will include an `access_token`. Save this token.

### 2. Use the Token

Add the token to your request headers:

```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

### 3. Test Protected Endpoints

**GET** `http://localhost/api/v1/user`

With the Authorization header, you should get your user details.

## API Features

### For Customers/Users

1. **Authentication**
   - Register, Login, Logout
   - Password reset
   - Profile management

2. **Products**
   - Browse products with filters
   - Search products
   - View product details
   - Featured products

3. **Cart Management**
   - Add/remove items
   - Update quantities
   - Get cart total

4. **Orders**
   - Create orders from cart
   - View order history
   - Track orders
   - Download invoices
   - Cancel orders

5. **Reviews**
   - Write product reviews
   - Edit/delete reviews
   - View reviews

### For Vendors

1. **Dashboard**
   - Sales statistics
   - Revenue analytics
   - Order management

2. **Product Management**
   - Create/edit/delete products
   - Upload product images
   - Manage inventory
   - Toggle product status

3. **Order Management**
   - View vendor orders
   - Update order status
   - Generate invoices

4. **Analytics**
   - Revenue reports
   - Top selling products
   - Customer insights
   - Sales charts

5. **Profile Management**
   - Update shop details
   - Upload logo/banner
   - Manage shipping settings

## Testing the API

### Using Postman

1. Import the `Aquado_API_Postman_Collection.json` file into Postman
2. Set the `base_url` variable to your API URL (e.g., `http://localhost/api/v1`)
3. Register a new user
4. Copy the `access_token` from the response
5. Set the `access_token` variable in Postman
6. Test all endpoints

### Using cURL

**Register:**
```bash
curl -X POST http://localhost/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Get Products:**
```bash
curl -X GET http://localhost/api/v1/products
```

**Authenticated Request:**
```bash
curl -X GET http://localhost/api/v1/cart \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Common Use Cases

### 1. Create a Complete Order Flow

```bash
# 1. Register/Login
POST /api/v1/login

# 2. Browse products
GET /api/v1/products

# 3. Add to cart
POST /api/v1/cart/add

# 4. View cart
GET /api/v1/cart

# 5. Create order
POST /api/v1/orders/create

# 6. Track order
GET /api/v1/orders/{orderId}/track
```

### 2. Vendor Product Management

```bash
# 1. Login as vendor
POST /api/v1/login

# 2. View dashboard
GET /api/v1/vendor/dashboard

# 3. Create product
POST /api/v1/vendor/products

# 4. View orders
GET /api/v1/vendor/orders

# 5. Update order status
PUT /api/v1/vendor/orders/{orderItemId}/status
```

## Error Handling

All API responses follow this format:

**Success:**
```json
{
  "success": true,
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error description"
}
```

**Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

## Security Best Practices

1. **Use HTTPS in production**
   - Never send tokens over HTTP
   
2. **Store tokens securely**
   - Use secure storage in mobile apps
   - Use httpOnly cookies for web apps

3. **Token Expiration**
   - Tokens don't expire by default
   - Implement token rotation for sensitive operations

4. **Rate Limiting**
   - API has built-in rate limiting
   - Authenticated users: 120 req/min
   - Guests: 60 req/min

5. **Validate All Input**
   - All endpoints have validation
   - Check API documentation for required fields

## Environment Variables

Add these to your `.env` file:

```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_DOMAIN=localhost
```

For production:
```env
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

## Mobile App Integration

### For Flutter/React Native/Native Apps

1. **Store the token:**
```dart
// Flutter example
await storage.write(key: 'access_token', value: token);
```

2. **Add to requests:**
```dart
headers: {
  'Authorization': 'Bearer $token',
  'Accept': 'application/json',
}
```

3. **Handle token refresh:**
```dart
if (response.statusCode == 401) {
  // Token expired, redirect to login
}
```

## Frontend Integration

### Vue.js Example

```javascript
// Install axios
npm install axios

// Create API service
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost/api/v1',
  headers: {
    'Content-Type': 'application/json',
  }
});

// Add token to requests
api.interceptors.request.use(config => {
  const token = localStorage.getItem('access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Use it
api.get('/products').then(response => {
  console.log(response.data);
});
```

### React Example

```javascript
// Create API client
const api = axios.create({
  baseURL: 'http://localhost/api/v1',
});

// Add interceptor
api.interceptors.request.use(config => {
  const token = localStorage.getItem('access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Usage
const fetchProducts = async () => {
  const response = await api.get('/products');
  return response.data;
};
```

## Troubleshooting

### 1. CORS Errors

Add to `config/cors.php`:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://localhost:3000'], // Your frontend URL
'allowed_credentials' => true,
```

### 2. Token Not Working

- Ensure token is in `Authorization: Bearer TOKEN` format
- Check token hasn't been deleted
- Verify user still exists

### 3. 419 CSRF Token Mismatch

For API routes, CSRF is not required. Ensure you're hitting `/api/v1/*` routes.

### 4. File Upload Issues

Use `multipart/form-data` for file uploads:
```javascript
const formData = new FormData();
formData.append('image', file);
formData.append('name', 'Product Name');

axios.post('/api/v1/vendor/products', formData, {
  headers: {
    'Content-Type': 'multipart/form-data',
  }
});
```

## Next Steps

1. ✅ API is ready to use
2. 📱 Integrate with your mobile app
3. 🌐 Connect with your frontend
4. 🧪 Test all endpoints
5. 🔒 Add additional security layers
6. 📊 Monitor API usage
7. 🚀 Deploy to production

## Support

For detailed endpoint documentation, see `API_DOCUMENTATION.md`

For Postman testing, import `Aquado_API_Postman_Collection.json`

## Production Checklist

Before going to production:

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Use HTTPS only
- [ ] Update CORS settings
- [ ] Set up proper rate limiting
- [ ] Implement logging
- [ ] Set up monitoring
- [ ] Add API versioning strategy
- [ ] Document all breaking changes
- [ ] Set up automated backups
- [ ] Implement proper error tracking

## License

This API is part of the Aquado e-commerce platform.
