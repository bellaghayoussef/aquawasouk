# Aquado API Documentation

## Base URL
```
http://your-domain.com/api/v1
```

## Authentication

Most endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

---

## Authentication Endpoints

### Register User
**POST** `/register`

Request:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer" // optional: "customer" or "vendor"
}
```

Response:
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": { ... },
    "access_token": "token_here",
    "token_type": "Bearer"
  }
}
```

### Login
**POST** `/login`

Request:
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### Logout
**POST** `/logout` (Authenticated)

### Get Current User
**GET** `/user` (Authenticated)

### Update Profile
**PUT** `/user` (Authenticated)

Request:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "photo": "file upload"
}
```

### Update Password
**PUT** `/user/password` (Authenticated)

Request:
```json
{
  "current_password": "old_password",
  "password": "new_password",
  "password_confirmation": "new_password"
}
```

### Forgot Password
**POST** `/forgot-password`

### Reset Password
**POST** `/reset-password`

### Delete Account
**DELETE** `/user` (Authenticated)

---

## Product Endpoints

### Get All Products
**GET** `/products`

Query Parameters:
- `search` - Search in name/description
- `category` - Filter by category slug
- `vendor` - Filter by vendor slug
- `min_price` - Minimum price
- `max_price` - Maximum price
- `featured` - true/false
- `sort_by` - price, name, created_at, popularity
- `sort_order` - asc, desc
- `per_page` - Items per page (default: 15)

### Get Single Product
**GET** `/products/{slug}`

### Get Featured Products
**GET** `/products/featured`

### Search Products
**GET** `/products/search?q={query}`

---

## Category Endpoints

### Get All Categories
**GET** `/categories`

Query Parameters:
- `parent_only` - true to get only parent categories

### Get Single Category
**GET** `/categories/{slug}`

### Get Category Products
**GET** `/categories/{slug}/products`

---

## Vendor Endpoints (Public)

### Get All Vendors
**GET** `/vendors`

Query Parameters:
- `search` - Search in shop name/description
- `city` - Filter by city
- `country` - Filter by country

### Get Single Vendor
**GET** `/vendors/{slug}`

### Get Vendor Products
**GET** `/vendors/{slug}/products`

---

## Cart Endpoints (Authenticated)

### Get Cart
**GET** `/cart`

### Add to Cart
**POST** `/cart/add`

Request:
```json
{
  "product_id": 1,
  "quantity": 2,
  "color": "Red",
  "size": "L"
}
```

### Update Cart Item
**PUT** `/cart/update/{cartItemId}`

Request:
```json
{
  "quantity": 3
}
```

### Remove from Cart
**DELETE** `/cart/remove/{cartItemId}`

### Clear Cart
**DELETE** `/cart/clear`

### Get Cart Count
**GET** `/cart/count`

---

## Order Endpoints (Authenticated)

### Get All Orders
**GET** `/orders`

Query Parameters:
- `status` - pending, processing, shipped, delivered, cancelled
- `payment_status` - pending, paid, failed

### Get Single Order
**GET** `/orders/{orderId}`

### Create Order
**POST** `/orders/create`

Request:
```json
{
  "shipping_address": "123 Main St",
  "shipping_city": "New York",
  "shipping_postal_code": "10001",
  "shipping_country": "USA",
  "phone": "+1234567890",
  "payment_method": "cash_on_delivery",
  "notes": "Optional notes"
}
```

### Cancel Order
**POST** `/orders/{orderId}/cancel`

### Get Order Invoice
**GET** `/orders/{orderId}/invoice`

### Track Order
**GET** `/orders/{orderId}/track`

---

## Review Endpoints

### Get Product Reviews
**GET** `/products/{productId}/reviews`

### Create Review (Authenticated)
**POST** `/reviews/products/{productId}`

Request:
```json
{
  "rating": 5,
  "comment": "Great product!"
}
```

### Update Review (Authenticated)
**PUT** `/reviews/{reviewId}`

### Delete Review (Authenticated)
**DELETE** `/reviews/{reviewId}`

### Get My Reviews (Authenticated)
**GET** `/reviews/my-reviews`

---

## Vendor Management Endpoints (Vendor Auth Required)

### Dashboard
**GET** `/vendor/dashboard`

Returns vendor profile and statistics.

### Statistics
**GET** `/vendor/statistics`

Query Parameters:
- `period` - 7days, 30days, 90days, year

### Sales Chart
**GET** `/vendor/sales-chart`

Query Parameters:
- `days` - Number of days (default: 30)

### Vendor Profile
**GET** `/vendor/profile`

### Update Vendor Profile
**PUT** `/vendor/profile`

Request:
```json
{
  "shop_name": "My Shop",
  "description": "Shop description",
  "phone": "+1234567890",
  "address": "123 Shop St",
  "city": "New York",
  "country": "USA",
  "postal_code": "10001",
  "shipping_price": 10.00
}
```

### Update Logo
**POST** `/vendor/profile/logo`

Request: multipart/form-data with `logo` file

### Update Banner
**POST** `/vendor/profile/banner`

Request: multipart/form-data with `banner` file

---

## Vendor Product Management

### Get My Products
**GET** `/vendor/products`

Query Parameters:
- `status` - active, inactive
- `search` - Search query

### Create Product
**POST** `/vendor/products`

Request: multipart/form-data
```json
{
  "category_id": 1,
  "name": "Product Name",
  "description": "Product description",
  "price": 99.99,
  "discount_price": 79.99,
  "stock": 100,
  "specifications": {"size": "L", "color": "Red"},
  "is_featured": true,
  "images": [file1, file2, ...]
}
```

### Get Single Product
**GET** `/vendor/products/{productId}`

### Update Product
**PUT** `/vendor/products/{productId}`

### Delete Product
**DELETE** `/vendor/products/{productId}`

### Add Product Image
**POST** `/vendor/products/{productId}/images`

Request: multipart/form-data
```json
{
  "image": file,
  "is_primary": false
}
```

### Delete Product Image
**DELETE** `/vendor/products/images/{imageId}`

### Toggle Product Status
**PUT** `/vendor/products/{productId}/toggle-status`

### Toggle Product Featured
**PUT** `/vendor/products/{productId}/toggle-featured`

---

## Vendor Order Management

### Get My Orders
**GET** `/vendor/orders`

Query Parameters:
- `status` - pending, processing, shipped, delivered, cancelled

### Get Order Details
**GET** `/vendor/orders/{orderItemId}`

### Update Order Status
**PUT** `/vendor/orders/{orderItemId}/status`

Request:
```json
{
  "status": "processing"
}
```

### Generate Invoice
**GET** `/vendor/orders/{orderItemId}/invoice`

---

## Vendor Analytics

### Revenue Analytics
**GET** `/vendor/analytics/revenue`

Query Parameters:
- `period` - week, month, year

### Top Products
**GET** `/vendor/analytics/top-products`

Query Parameters:
- `limit` - Number of products (default: 10)

### Recent Orders
**GET** `/vendor/analytics/recent-orders`

Query Parameters:
- `limit` - Number of orders (default: 10)

### Customers
**GET** `/vendor/analytics/customers`

---

## Vendor Reviews

### Get Reviews
**GET** `/vendor/reviews`

### Reply to Review
**PUT** `/vendor/reviews/{reviewId}/reply`

Request:
```json
{
  "reply": "Thank you for your feedback!"
}
```

---

## Vendor Notifications

### Get Notifications
**GET** `/vendor/notifications`

### Mark Notification as Read
**PUT** `/vendor/notifications/{notificationId}/read`

### Mark All as Read
**PUT** `/vendor/notifications/mark-all-read`

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Optional success message",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

---

## Pagination

Paginated responses include:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [ ... ],
    "first_page_url": "...",
    "from": 1,
    "last_page": 5,
    "last_page_url": "...",
    "next_page_url": "...",
    "path": "...",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 75
  }
}
```

---

## Testing with Postman/Insomnia

1. Register a user via `/api/v1/register`
2. Copy the `access_token` from the response
3. Add to request headers: `Authorization: Bearer {access_token}`
4. For vendors, register with `"role": "vendor"` and create a vendor profile
5. Test all endpoints with appropriate authentication

---

## Rate Limiting

API requests are rate limited to prevent abuse. Default limits:
- Unauthenticated: 60 requests per minute
- Authenticated: 120 requests per minute

Rate limit headers are included in responses:
- `X-RateLimit-Limit`
- `X-RateLimit-Remaining`
- `Retry-After` (when limit exceeded)
