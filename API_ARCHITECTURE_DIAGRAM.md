# Aquado API Architecture

## API Structure Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         Aquado API v1                           │
│                    http://domain.com/api/v1                     │
└─────────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
┌──────────────┐    ┌──────────────────┐    ┌─────────────┐
│   PUBLIC     │    │   AUTHENTICATED  │    │   VENDOR    │
│  ENDPOINTS   │    │    ENDPOINTS     │    │  ENDPOINTS  │
└──────────────┘    └──────────────────┘    └─────────────┘
        │                     │                     │
        │                     │                     │
        ▼                     ▼                     ▼

┌─────────────────────────────────────────────────────────────────┐
│                        PUBLIC ENDPOINTS                         │
│                       (No Auth Required)                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Authentication                                                 │
│  ├─ POST   /register              Register new user            │
│  ├─ POST   /login                 User login                   │
│  ├─ POST   /forgot-password       Request password reset       │
│  └─ POST   /reset-password        Reset password               │
│                                                                 │
│  Products                                                       │
│  ├─ GET    /products               List all products           │
│  ├─ GET    /products/{slug}        Get product details         │
│  ├─ GET    /products/featured      Get featured products       │
│  └─ GET    /products/search        Search products             │
│                                                                 │
│  Categories                                                     │
│  ├─ GET    /categories             List all categories         │
│  ├─ GET    /categories/{slug}      Get category details        │
│  └─ GET    /categories/{slug}/products  Category products      │
│                                                                 │
│  Vendors                                                        │
│  ├─ GET    /vendors                List all vendors            │
│  ├─ GET    /vendors/{slug}         Get vendor details          │
│  └─ GET    /vendors/{slug}/products  Vendor products           │
│                                                                 │
│  Reviews                                                        │
│  └─ GET    /products/{product}/reviews  Get product reviews    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    AUTHENTICATED ENDPOINTS                      │
│              (Requires: Authorization: Bearer token)            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  User Management                                                │
│  ├─ POST   /logout                 Logout user                 │
│  ├─ GET    /user                   Get current user            │
│  ├─ PUT    /user                   Update profile              │
│  ├─ PUT    /user/password          Update password             │
│  └─ DELETE /user                   Delete account              │
│                                                                 │
│  Cart Management                                                │
│  ├─ GET    /cart                   Get user cart               │
│  ├─ POST   /cart/add               Add item to cart            │
│  ├─ PUT    /cart/update/{item}     Update cart item            │
│  ├─ DELETE /cart/remove/{item}     Remove from cart            │
│  ├─ DELETE /cart/clear             Clear entire cart           │
│  └─ GET    /cart/count             Get cart count              │
│                                                                 │
│  Order Management                                               │
│  ├─ GET    /orders                 List user orders            │
│  ├─ GET    /orders/{order}         Get order details           │
│  ├─ POST   /orders/create          Create new order            │
│  ├─ POST   /orders/{order}/cancel  Cancel order                │
│  ├─ GET    /orders/{order}/invoice Get order invoice           │
│  └─ GET    /orders/{order}/track   Track order status          │
│                                                                 │
│  Review Management                                              │
│  ├─ POST   /reviews/products/{product}  Create review          │
│  ├─ PUT    /reviews/{review}       Update review               │
│  ├─ DELETE /reviews/{review}       Delete review               │
│  └─ GET    /reviews/my-reviews     Get user reviews            │
│                                                                 │
│  Wishlist (Optional)                                            │
│  ├─ GET    /wishlist               Get wishlist                │
│  ├─ POST   /wishlist/add/{product} Add to wishlist             │
│  └─ DELETE /wishlist/remove/{product} Remove from wishlist     │
│                                                                 │
│  Addresses (Optional)                                           │
│  ├─ GET    /addresses              List addresses              │
│  ├─ POST   /addresses              Create address              │
│  ├─ PUT    /addresses/{address}    Update address              │
│  ├─ DELETE /addresses/{address}    Delete address              │
│  └─ POST   /addresses/{address}/set-default  Set default       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                      VENDOR ENDPOINTS                           │
│        (Requires: Vendor role + Authorization: Bearer token)    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Dashboard & Profile                                            │
│  ├─ GET    /vendor/dashboard       Get dashboard data          │
│  ├─ GET    /vendor/statistics      Get statistics              │
│  ├─ GET    /vendor/sales-chart     Get sales chart data        │
│  ├─ GET    /vendor/profile         Get vendor profile          │
│  ├─ PUT    /vendor/profile         Update profile              │
│  ├─ POST   /vendor/profile/logo    Update logo                 │
│  └─ POST   /vendor/profile/banner  Update banner               │
│                                                                 │
│  Product Management                                             │
│  ├─ GET    /vendor/products        List vendor products        │
│  ├─ POST   /vendor/products        Create product              │
│  ├─ GET    /vendor/products/{product}  Get product             │
│  ├─ PUT    /vendor/products/{product}  Update product          │
│  ├─ DELETE /vendor/products/{product}  Delete product          │
│  ├─ POST   /vendor/products/{product}/images  Add image        │
│  ├─ DELETE /vendor/products/images/{image}  Delete image       │
│  ├─ PUT    /vendor/products/{product}/toggle-status            │
│  └─ PUT    /vendor/products/{product}/toggle-featured          │
│                                                                 │
│  Order Management                                               │
│  ├─ GET    /vendor/orders          List vendor orders          │
│  ├─ GET    /vendor/orders/{orderItem}  Get order details       │
│  ├─ PUT    /vendor/orders/{orderItem}/status  Update status    │
│  └─ GET    /vendor/orders/{orderItem}/invoice  Get invoice     │
│                                                                 │
│  Analytics & Reports                                            │
│  ├─ GET    /vendor/analytics/revenue      Revenue analytics    │
│  ├─ GET    /vendor/analytics/top-products  Top products        │
│  ├─ GET    /vendor/analytics/recent-orders  Recent orders      │
│  └─ GET    /vendor/analytics/customers     Customer analytics  │
│                                                                 │
│  Reviews                                                        │
│  ├─ GET    /vendor/reviews         List reviews                │
│  └─ PUT    /vendor/reviews/{review}/reply  Reply to review     │
│                                                                 │
│  Notifications                                                  │
│  ├─ GET    /vendor/notifications   Get notifications           │
│  ├─ PUT    /vendor/notifications/{id}/read  Mark as read       │
│  └─ PUT    /vendor/notifications/mark-all-read  Mark all read  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                      DATA FLOW DIAGRAM                          │
└─────────────────────────────────────────────────────────────────┘

    ┌──────────┐         ┌──────────┐         ┌──────────┐
    │  Mobile  │         │   Web    │         │ 3rd Party│
    │   App    │         │  Client  │         │ Services │
    └────┬─────┘         └────┬─────┘         └────┬─────┘
         │                    │                    │
         └────────────────────┼────────────────────┘
                              │
                    HTTP/HTTPS Request
                    (JSON Payload)
                              │
                              ▼
         ┌────────────────────────────────────────┐
         │         Laravel API Routes             │
         │           routes/api.php               │
         └────────────────┬───────────────────────┘
                          │
            ┌─────────────┴──────────────┐
            │                            │
            ▼                            ▼
    ┌──────────────┐            ┌──────────────┐
    │ Sanctum Auth │            │ Middleware   │
    │  Middleware  │            │  Validation  │
    └──────┬───────┘            └──────┬───────┘
           │                           │
           └───────────┬───────────────┘
                       │
                       ▼
         ┌─────────────────────────────┐
         │    API Controllers          │
         │  - AuthController           │
         │  - ProductController        │
         │  - CartController           │
         │  - OrderController          │
         │  - VendorController         │
         │  - etc.                     │
         └────────────┬────────────────┘
                      │
        ┌─────────────┼─────────────┐
        │             │             │
        ▼             ▼             ▼
   ┌────────┐   ┌────────┐   ┌────────┐
   │ Models │   │Business│   │  File  │
   │        │   │ Logic  │   │Storage │
   │ - User │   │        │   │        │
   │ - Product  │ - Stock│   │ - AWS  │
   │ - Order│   │ - Price│   │ - Local│
   │ - Vendor   │ - Calc │   │        │
   └────┬───┘   └────────┘   └────────┘
        │
        ▼
   ┌────────────┐
   │  Database  │
   │   MySQL    │
   └────────────┘
        │
        ▼
   JSON Response
   Back to Client

┌─────────────────────────────────────────────────────────────────┐
│                   AUTHENTICATION FLOW                           │
└─────────────────────────────────────────────────────────────────┘

1. Register/Login
   ┌────────┐                                    ┌────────┐
   │ Client │─────── POST /api/v1/login ────────▶│  API   │
   └────────┘                                    └───┬────┘
                                                     │
                                               Validate
                                               Credentials
                                                     │
                                                     ▼
                                            Create Access Token
                                              (Sanctum)
                                                     │
   ┌────────┐                                       │
   │ Client │◀──── { "access_token": "..." } ──────┘
   └────────┘
       │
   Store Token
       │
       ▼

2. Authenticated Request
   ┌────────┐                                    ┌────────┐
   │ Client │─── GET /api/v1/cart ──────────────▶│  API   │
   │        │    Header:                         │        │
   │        │    Authorization: Bearer {token}   │        │
   └────────┘                                    └───┬────┘
                                                     │
                                               Verify Token
                                              (Sanctum Middleware)
                                                     │
                                                     ▼
                                              Process Request
                                                     │
   ┌────────┐                                       │
   │ Client │◀────── JSON Response ─────────────────┘
   └────────┘

┌─────────────────────────────────────────────────────────────────┐
│                     ORDER FLOW DIAGRAM                          │
└─────────────────────────────────────────────────────────────────┘

Customer Journey:
─────────────────

1. Browse Products → GET /api/v1/products
                    ↓
2. Add to Cart     → POST /api/v1/cart/add
                    ↓
3. View Cart       → GET /api/v1/cart
                    ↓
4. Checkout        → POST /api/v1/orders/create
                    ↓
5. Track Order     → GET /api/v1/orders/{id}/track
                    ↓
6. Write Review    → POST /api/v1/reviews/products/{id}


Vendor Journey:
───────────────

1. View Dashboard  → GET /api/v1/vendor/dashboard
                    ↓
2. Check Orders    → GET /api/v1/vendor/orders
                    ↓
3. Update Status   → PUT /api/v1/vendor/orders/{id}/status
                    ↓
4. View Analytics  → GET /api/v1/vendor/analytics/revenue
                    ↓
5. Manage Products → GET /api/v1/vendor/products

┌─────────────────────────────────────────────────────────────────┐
│                   RESPONSE FORMAT STANDARD                      │
└─────────────────────────────────────────────────────────────────┘

Success Response (200):
{
  "success": true,
  "data": { ... },
  "message": "Optional success message"
}

Created Response (201):
{
  "success": true,
  "data": { ... },
  "message": "Resource created successfully"
}

Error Response (4xx/5xx):
{
  "success": false,
  "message": "Error description",
  "errors": { ... }  // Optional validation errors
}

Paginated Response:
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [ ... ],
    "total": 100,
    "per_page": 15,
    "last_page": 7,
    ...
  }
}

┌─────────────────────────────────────────────────────────────────┐
│                        SECURITY LAYERS                          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Layer 1: HTTPS/TLS Encryption (Production)                │
├─────────────────────────────────────────────────────────────┤
│  Layer 2: Rate Limiting (60-120 req/min)                   │
├─────────────────────────────────────────────────────────────┤
│  Layer 3: Sanctum Token Authentication                     │
├─────────────────────────────────────────────────────────────┤
│  Layer 4: Middleware (auth, vendor)                        │
├─────────────────────────────────────────────────────────────┤
│  Layer 5: Input Validation                                 │
├─────────────────────────────────────────────────────────────┤
│  Layer 6: Ownership Verification                           │
├─────────────────────────────────────────────────────────────┤
│  Layer 7: Database Transactions                            │
└─────────────────────────────────────────────────────────────┘

═══════════════════════════════════════════════════════════════════
                         END OF DIAGRAM
═══════════════════════════════════════════════════════════════════
