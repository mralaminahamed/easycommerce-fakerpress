# Architecture Documentation

## Modern Plugin Structure

```
easycommerce-fakerpress/
├── easycommerce-fakerpress.php           # Main plugin file
├── class-easycommerce-fakerpress.php     # Main plugin class with color integration
├── includes/
│   ├── Abstracts/                        # Abstract base classes
│   │   ├── Generator.php                 # Base generator with parameter handling
│   │   └── REST_Controller.php           # Base REST controller with validation
│   ├── Generators/                       # 10 Specialized generators
│   │   ├── Product_Generator.php         # Products with attributes & variations
│   │   ├── Customer_Generator.php        # Comprehensive customer profiles
│   │   ├── Order_Generator.php           # Orders with item metadata & locations
│   │   ├── Coupon_Generator.php          # Coupons with advanced rule systems
│   │   ├── Product_Variation_Generator.php # Product variations & attributes
│   │   ├── Shipping_Plan_Generator.php   # Shipping methods & regional coverage
│   │   ├── Tax_Generator.php             # Tax classes & location-based rates
│   │   ├── Transaction_Generator.php     # Payment transaction history
│   │   ├── Cart_Session_Generator.php    # Cart sessions & abandonment
│   │   └── Location_Generator.php        # Geographic location hierarchy
│   └── Controllers/                      # REST API controllers with parameters
│       ├── Product_REST_Controller.php
│       ├── Customer_REST_Controller.php
│       ├── Order_REST_Controller.php
│       ├── Coupon_REST_Controller.php
│       ├── Product_Variation_REST_Controller.php
│       ├── Shipping_Plan_REST_Controller.php
│       ├── Tax_REST_Controller.php
│       ├── Transaction_REST_Controller.php
│       ├── Cart_Session_REST_Controller.php
│       └── Location_REST_Controller.php
├── src/
│   └── admin/
│       ├── components/                   # React components with advanced UX
│       │   ├── App.jsx                   # Main router with createHashRouter
│       │   ├── GeneratorBase.jsx         # Enhanced form controls & validation
│       │   ├── Pages/                    # Route-based page components
│       │   │   ├── RootLayout.jsx        # Main layout wrapper with Outlet
│       │   │   ├── HomePage.jsx          # Generator selection grid
│       │   │   └── GeneratorPage.jsx     # Individual generator pages
│       │   └── Generators/               # Individual generator components
│       │       ├── ProductGenerator.jsx  # Core generators
│       │       ├── CustomerGenerator.jsx
│       │       ├── OrderGenerator.jsx
│       │       ├── CouponGenerator.jsx
│       │       ├── ProductVariationGenerator.jsx # Enhanced generators
│       │       ├── ShippingPlanGenerator.jsx
│       │       ├── TaxGenerator.jsx
│       │       ├── TransactionGenerator.jsx
│       │       ├── CartSessionGenerator.jsx
│       │       └── LocationGenerator.jsx
│       ├── styles.css                   # Tailwind with WordPress color integration
│       └── index.js                     # Entry point with color scheme support
├── build/                               # Compiled assets with CSS variables
├── vendor/                              # Composer dependencies
├── node_modules/                        # NPM dependencies
├── composer.json                        # PSR-4 autoloading & dependencies
├── package.json                         # Modern build tools & dependencies
├── webpack.config.js                    # Advanced build configuration
├── tailwind.config.js                   # WordPress admin color integration
├── phpcs.xml.dist                       # PHP CodeSniffer rules
└── docs/                                # Documentation files
```

## EasyCommerce Integration

The plugin seamlessly integrates with EasyCommerce through:

- **Model Integration**: Uses EasyCommerce Product, Customer, Order, and Coupon models
- **Database Abstraction**: Leverages EasyCommerce Database class for consistent data access
- **Business Logic**: Implements EasyCommerce business rules and validation
- **Relationship Management**: Maintains proper data relationships and integrity
- **Attribute System**: Creates proper product attributes and variations
- **Meta Data**: Uses EasyCommerce meta systems for extended data storage

## Design Patterns

### Abstract Base Classes

- **Generator**: Common functionality for all data generators with template method pattern
- **REST_Controller**: Standardized REST API response handling with error management
- **Validation**: Input validation and sanitization with WordPress standards
- **Logging**: Integrated WordPress debug logging with context information

### Used Patterns

- **Template Method**: Consistent generation workflow across all generators
- **Factory Pattern**: Dynamic generator instantiation based on data type
- **Strategy Pattern**: Configurable generation strategies per data type
- **Observer Pattern**: Event-driven generation with progress tracking

## Frontend Architecture

### React Router v7 Architecture

- **createHashRouter**: Used for WordPress admin compatibility with hash-based routing
- **RouterProvider**: Top-level router provider wrapping the entire application
- **Nested Routes**: Layout routes with child pages using Outlet component
- **Route Object Pattern**: Declarative route configuration over component-based routing

### Component Organization

- **Pages**: Route-based components that handle specific URLs
- **Generators**: Data generation components that extend GeneratorBase
- **Base Components**: Shared/reusable components across the application

## Performance Optimization

- **Batch Processing**: Efficient bulk data generation with memory management
- **Query Optimization**: Optimized database queries with proper indexing
- **Caching**: Strategic caching for repeated operations and lookups
- **Memory Management**: Proper cleanup for large data sets with garbage collection
- **Database Transactions**: Atomic operations for data consistency