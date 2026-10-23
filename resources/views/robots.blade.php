User-agent: *
Allow: /


# Sitemap
Sitemap: {{ url('/sitemap.xml') }}

# Disallow admin and vendor dashboards
Disallow: /admin
Disallow: /admin/*
Disallow: /vendor
Disallow: /vendor/*

# Disallow authentication pages
Disallow: /login
Disallow: /register
Disallow: /password/
Disallow: /email/verify

# Disallow user-specific pages
Disallow: /dashboard
Disallow: /profile
Disallow: /orders

# Disallow checkout and cart (session-specific)
Disallow: /checkout
Disallow: /checkout/*
Disallow: /cart
Disallow: /cart/*

# Disallow API routes
Disallow: /api/
Disallow: /broadcasting/

# Allow specific bots to crawl faster
User-agent: Googlebot


User-agent: Bingbot

