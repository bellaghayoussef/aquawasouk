# Aquado Mobile App

A beautiful Flutter e-commerce mobile application for the Aquado platform with **full support for both Customers and Vendors**.

## 🎯 Dual-Role Application

This app automatically detects your user role and provides the appropriate interface:

- **👥 Customer Mode**: Browse products, shopping cart, checkout, order tracking
- **🏪 Vendor Mode**: Dashboard, product management, order management, analytics

## Features

### 👥 Customer Features
- **Authentication**: Login, Register, Logout
- **Product Browsing**: Browse products with categories, featured items
- **Product Details**: View detailed product information with image carousel
- **Shopping Cart**: Add/remove items, update quantities
- **Checkout**: Place orders with shipping information
- **Orders**: View order history and track orders
- **Reviews**: Read product reviews and ratings
- **Profile Management**: Update profile information

### 🏪 Vendor Features
- **Vendor Dashboard**: View statistics (products, orders, revenue)
- Vendor dashboard
- Product management
- Order management
- Analytics

## Tech Stack

- **Framework**: Flutter 3.0+
- **State Management**: Provider
- **HTTP Client**: HTTP package
- **Image Caching**: Cached Network Image
- **UI Components**: 
  - Google Fonts
  - Curved Navigation Bar
  - Carousel Slider
  - Shimmer effects
  - Rating bars

## Project Structure

```
lib/
├── config/
│   └── theme.dart                 # App theme and colors
├── models/
│   ├── user.dart                  # User model
│   ├── product.dart               # Product, Category, Vendor models
│   ├── cart.dart                  # Cart and CartItem models
│   ├── order.dart                 # Order and OrderItem models
│   └── review.dart                # Review model
├── services/
│   ├── api_service.dart           # Core API service
│   ├── auth_service.dart          # Authentication service
│   └── product_service.dart       # Products, Cart, Orders service
├── providers/
│   ├── auth_provider.dart         # Auth state management
│   └── cart_provider.dart         # Cart state management
├── screens/
│   ├── splash_screen.dart         # Splash screen
│   ├── auth/
│   │   ├── login_screen.dart      # Login screen
│   │   └── register_screen.dart   # Registration screen
│   ├── home/
│   │   ├── home_screen.dart       # Main home with navigation
│   │   └── tabs/
│   │       ├── home_tab.dart      # Home feed
│   │       ├── categories_tab.dart # Categories list
│   │       ├── cart_tab.dart      # Shopping cart
│   │       └── profile_tab.dart   # User profile
│   ├── product/
│   │   └── product_details_screen.dart # Product details
│   ├── checkout/
│   │   └── checkout_screen.dart   # Checkout process
│   └── orders/
│       └── orders_screen.dart     # Order history
├── widgets/
│   └── product_card.dart          # Reusable product card
└── main.dart                      # App entry point
```

## Setup Instructions

### 1. Prerequisites

- Flutter SDK (3.0 or higher)
- Dart SDK
- Android Studio / VS Code
- Android Emulator or physical device

### 2. Installation

```bash
# Navigate to the project directory
cd aquado_mobile

# Get dependencies
flutter pub get

# Run the app
flutter run
```

### 3. Configuration

Update the API base URL in `lib/services/api_service.dart`:

```dart
static const String baseUrl = 'http://your-server-ip/api/v1';
```

**Important**: 
- For Android Emulator: Use `http://10.0.2.2/api/v1`
- For iOS Simulator: Use `http://localhost/api/v1`
- For Physical Device: Use `http://YOUR_LOCAL_IP/api/v1`

### 4. Running on Different Platforms

```bash
# Android
flutter run -d android

# iOS (macOS only)
flutter run -d ios

# Chrome (for testing)
flutter run -d chrome
```

## API Integration

The app connects to the Laravel Aquado API. Make sure your Laravel backend is running and accessible.

### API Endpoints

The app uses these main API endpoints:

- `POST /api/v1/register` - User registration
- `POST /api/v1/login` - User login
- `GET /api/v1/products` - Get products
- `GET /api/v1/products/{slug}` - Get single product
- `POST /api/v1/cart/add` - Add to cart
- `GET /api/v1/cart` - Get cart
- `POST /api/v1/orders/create` - Create order
- `GET /api/v1/orders` - Get orders

## Design Features

### Modern UI/UX
- **Aqua-Blue Theme**: Professional color scheme with gradients
- **Smooth Animations**: Page transitions and loading states
- **Curved Navigation**: Beautiful bottom navigation bar
- **Image Carousels**: Featured products and product galleries
- **Card Design**: Modern card layouts for products and orders
- **Responsive**: Works on different screen sizes

### User Experience
- **Pull to Refresh**: Refresh content by pulling down
- **Loading States**: Clear loading indicators
- **Error Handling**: User-friendly error messages
- **Form Validation**: Real-time form validation
- **Empty States**: Informative empty state designs

## Screenshots

Add screenshots of your app here once it's running.

## Building for Production

### Android APK

```bash
flutter build apk --release
```

The APK will be at: `build/app/outputs/flutter-apk/app-release.apk`

### Android App Bundle (for Play Store)

```bash
flutter build appbundle --release
```

### iOS (macOS only)

```bash
flutter build ios --release
```

## Troubleshooting

### Common Issues

1. **API Connection Failed**
   - Check if your Laravel server is running
   - Verify the API base URL is correct
   - For physical devices, ensure device and server are on same network

2. **Dependencies Error**
   ```bash
   flutter clean
   flutter pub get
   ```

3. **Build Errors**
   ```bash
   flutter upgrade
   flutter doctor
   ```

## Future Enhancements

- [ ] Search functionality
- [ ] Wishlist feature
- [ ] Address management
- [ ] Order tracking
- [ ] Push notifications
- [ ] Vendor dashboard and management
- [ ] Payment gateway integration
- [ ] Product filters and sorting
- [ ] Social login (Google, Facebook)
- [ ] Dark mode
- [ ] Multi-language support

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is part of the Aquado e-commerce platform.

## Support

For issues or questions, please contact the development team.

---

**Made with ❤️ using Flutter**
