# 🚀 Usage Guide (v2.0.1)

Master EasyCommerce FakerPress v2.0.1 with this comprehensive usage guide. Learn how to generate realistic test data efficiently and safely, now with complete parameter schema alignment, TypeScript support, and enhanced validation.

## 🎯 Quick Start

### First Time Setup

1. **Access the Interface**: WordPress Admin → **EC FakerPress**
2. **Check Dependencies**: Review the dependency validation panel
3. **Generate Foundation Data**: Start with Locations, then Customers, then Products
4. **Test Small Batches**: Generate 5-10 items first to verify everything works
5. **Scale Up**: Gradually increase batch sizes as needed

### v2.0.1: Complete Parameter Schema Alignment & TypeScript

**All generators now feature perfectly aligned parameter schemas between TypeScript frontend forms and backend API validation.**

- **Type-Safe Validation**: Full TypeScript interface validation prevents errors
- **Real-Time Feedback**: Immediate feedback on parameter compatibility
- **API Reliability**: Guaranteed valid data submission from frontend to backend endpoints
- **Schema Consistency**: Frontend and backend parameter structures are perfectly aligned
- **Error Prevention**: Comprehensive validation prevents invalid parameter combinations

### Interface Overview

```
┌─────────────────────────────────────────────────────────────┐
│  EC FakerPress v2.0.1 - TypeScript-Powered Data Generator   │
├─────────────────────────────────────────────────────────────┤
│  ┌─ Generator Selection ──────────────────┐                 │
│  │  🛍️ Products        📦 Orders           │                 │
│  │  👥 Customers       🎫 Coupons          │                 │
│  │  🔄 Variations      🚚 Shipping         │                 │
│  │  💰 Tax Classes     💳 Transactions     │                 │
│  │  🛒 Cart Sessions   🌍 Locations        │                 │
│  └─────────────────────────────────────────┘                 │
│                                                             │
│  ┌─ Parameter Configuration ──────────────────────────────┐ │
│  │  Count: [50]    ┌─ Advanced Options ─┐                 │ │
│  │  Categories: [✓] │ □ Include Sales   │                 │ │
│  │  Attributes: [✓] │ □ Virtual Products│                 │ │
│  │  TypeScript: [✓] │ □ Schema Validated│                 │ │
│  │                 └─────────────────────┘                 │ │
│  └─────────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌─ Generation Progress ──────────────────────────────────┐ │
│  │  ▓▓▓▓▓▓▓▓░░░░░░░░ 60% Complete                        │ │
│  │  Generated 30/50 products...                          │ │
│  └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## 🗂️ Generator Categories

### 🏪 Core E-commerce Generators

#### Products Generator

**Create comprehensive product catalogs with full e-commerce features**

**Key Parameters:**

- **Count**: Number of products to generate (1-1000)
- **Categories**: Enable hierarchical category creation
- **Attributes**: Product attributes (size, color, material)
- **Variations**: Enable/disable product variations
- **Pricing**: Cost-based pricing with margins
- **Inventory**: Stock levels and management settings

**Generated Data Includes:**

- Product names, descriptions, and SKUs
- Category assignments and hierarchies
- Product images and galleries
- Pricing with regular and sale prices
- Inventory levels and stock status
- SEO metadata and structured data

#### Customers Generator

**Generate realistic customer profiles with behavioral data**

**Key Parameters:**

- **Count**: Number of customers (1-5000)
- **Demographics**: Age distribution and geographic spread
- **Purchase History**: Average orders per customer
- **Loyalty Tiers**: Bronze/Silver/Gold/Platinum distribution
- **Account Status**: Active/inactive customer ratios

**Generated Data Includes:**

- Complete contact information
- Billing and shipping addresses
- Purchase history and order values
- Loyalty points and tier status
- Marketing preferences and consent
- Account creation and last login dates

#### Orders Generator

**Create complete order histories with full transaction details**

**Key Parameters:**

- **Count**: Number of orders (1-2000)
- **Date Range**: Order date distribution
- **Customer Selection**: Use existing or generate new customers
- **Product Selection**: Order composition and item counts
- **Payment Methods**: Payment method distribution
- **Order Status**: Order status ratios (pending/processing/completed)

**Generated Data Includes:**

- Order items with quantities and pricing
- Payment method and transaction details
- Shipping addresses and method selection
- Tax calculations and breakdowns
- Order notes and special instructions
- Order status progression with timestamps

#### Coupons Generator

**Generate discount codes with sophisticated rules**

**Key Parameters:**

- **Count**: Number of coupons (1-500)
- **Discount Types**: Percentage, fixed amount, free shipping
- **Usage Limits**: Per customer and total usage limits
- **Date Restrictions**: Start/end dates and expiration
- **Product Restrictions**: Include/exclude specific products
- **Minimum Requirements**: Minimum purchase amounts

**Generated Data Includes:**

- Unique coupon codes and descriptions
- Discount amounts and types
- Usage tracking and limits
- Customer and product restrictions
- Expiration dates and grace periods
- Redemption history and analytics

### 🔧 Enhanced Business Generators

#### Product Variations Generator

**Create complex product variations with attribute combinations**

**Use Case**: When you need products with multiple variation options like size, color, and material combinations.

**Key Parameters:**

- **Parent Products**: Select existing products to add variations to
- **Attribute Types**: Choose variation attributes (size, color, style)
- **Combination Logic**: How attributes combine (all combinations vs. specific)
- **Pricing Strategy**: Individual pricing per variation
- **Stock Management**: Inventory per variation

#### Shipping Plans Generator

**Generate shipping methods and regional pricing**

**Use Case**: Testing shipping calculations and regional pricing strategies.

**Key Parameters:**

- **Carriers**: Shipping carriers (UPS, FedEx, USPS)
- **Zones**: Geographic shipping zones
- **Rate Tables**: Weight-based or flat rate pricing
- **Restrictions**: Shipping restrictions by product or region

#### Tax Classes Generator

**Create multi-jurisdiction tax configurations**

**Use Case**: Testing tax calculations for different regions and product types.

**Key Parameters:**

- **Tax Rates**: Location-based tax rates
- **Tax Classes**: Product tax classifications
- **Compound Rules**: Multiple tax rate calculations
- **Exemptions**: Tax-exempt products or customer types

#### Transactions Generator

**Generate payment transaction histories**

**Use Case**: Testing payment processing, refunds, and financial reporting.

**Key Parameters:**

- **Gateways**: Payment gateways (Stripe, PayPal, etc.)
- **Transaction Types**: Payments, refunds, adjustments
- **Success Rates**: Transaction success/failure ratios
- **Amount Ranges**: Transaction value distributions

#### Cart Sessions Generator

**Create shopping cart abandonment scenarios**

**Use Case**: Testing cart recovery systems and abandonment analytics.

**Key Parameters:**

- **Session Count**: Number of cart sessions
- **Abandonment Rate**: Percentage of abandoned carts
- **Cart Value**: Average cart values and distributions
- **Time Frames**: Session duration and abandonment timing

#### Location Data Generator

**Populate geographic location hierarchies**

**Use Case**: Required foundation data for shipping, taxes, and regional features.

**Key Parameters:**

- **Countries**: Geographic coverage (global vs. specific regions)
- **Hierarchy Depth**: Country → State → City completeness
- **Data Completeness**: Include coordinates, timezones, currencies

## 🎯 Generated Data Quality

EasyCommerce FakerPress generates production-ready data that accurately simulates real e-commerce scenarios.

### 🛍️ Products Data Quality

#### Realistic Product Catalog

- **Diverse Product Types**: Physical, digital, and variable products
- **Industry-Specific Naming**: Context-appropriate product names and descriptions
- **Professional Imagery**: High-quality placeholder images with proper metadata
- **SEO Optimization**: Meta titles, descriptions, and structured data markup
- **Category Intelligence**: Hierarchical categorization with proper relationships

#### Advanced E-commerce Features

- **Dynamic Pricing**: Regular prices, sale prices, and bulk pricing tiers
- **Inventory Realism**: Stock levels, low stock alerts, and backorder settings
- **Attribute Systems**: Size, color, material combinations with proper validation
- **Variation Complexity**: Multi-attribute variations with individual pricing and stock

### 👥 Customer Data Quality

#### Demographic Accuracy

- **Age-Appropriate Behavior**: Purchase patterns based on customer demographics
- **Geographic Distribution**: Population-weighted geographic data
- **Cultural Relevance**: Region-appropriate names and preferences
- **Contact Validation**: RFC-compliant email addresses and valid phone formats

#### Behavioral Simulation

- **Loyalty Progression**: Realistic customer lifecycle from acquisition to retention
- **Purchase Frequency**: Time-based ordering patterns and spending habits
- **Engagement Levels**: Marketing consent, communication preferences, and interaction history
- **Account Management**: Registration dates, login history, and account status

### 📦 Orders Data Quality

#### Transaction Realism

- **Order Composition**: Realistic product combinations and quantities
- **Payment Distribution**: Region-appropriate payment method preferences
- **Temporal Patterns**: Realistic order timing and seasonal trends
- **Value Distribution**: Natural order value ranges and distributions

#### Business Logic Compliance

- **Shipping Calculations**: Distance-based costs and carrier selection
- **Tax Compliance**: Multi-jurisdiction tax calculation and reporting
- **Order Fulfillment**: Complete order lifecycle with status progression
- **Customer Linking**: Proper association with existing customer records

### 🎫 Coupons Data Quality

#### Business Rule Accuracy

- **Discount Logic**: Realistic discount values and application rules
- **Usage Patterns**: Natural redemption rates and customer behavior
- **Restriction Complexity**: Multi-level restrictions and validation rules
- **Expiration Handling**: Time-based and usage-based expiration logic

#### Advanced Features

- **Stacking Logic**: Compatible and conflicting coupon combinations
- **Customer Targeting**: Segment-based coupon distribution
- **Performance Tracking**: Usage analytics and conversion metrics
- **A/B Testing**: Multiple coupon variants for optimization

### 🌍 Additional Data Quality Features

#### Geographic Intelligence

- **Address Validation**: Real postal codes and proper address formatting
- **Timezone Accuracy**: Location-aware timestamp generation
- **Currency Handling**: Multi-currency support with proper formatting
- **Regional Compliance**: Location-specific business rules and regulations

#### Performance & Scalability

- **Batch Processing**: Memory-efficient large dataset generation
- **Relationship Integrity**: 100% referential integrity across all data
- **Data Consistency**: Zero orphaned records or broken relationships
- **Query Optimization**: Database-friendly data structures and indexing

## 💡 Best Practices & Tips

Maximize the effectiveness of EasyCommerce FakerPress with these proven strategies.

### 🎯 Generation Strategy

#### Start with Foundation Data

**Recommended Generation Order:**

1. **Locations** → Establish geographic foundation
2. **Customers** → Create user base for orders
3. **Products** → Build product catalog
4. **Product Variations** → Add complexity to products
5. **Orders** → Generate purchase history
6. **Coupons** → Create discount programs
7. **Transactions** → Add payment processing data
8. **Cart Sessions** → Simulate shopping behavior

#### Batch Size Optimization

- **Small Batches (1-50)**: For testing and validation
- **Medium Batches (50-500)**: For development and staging
- **Large Batches (500+)**: For performance testing and production simulation
- **Memory Consideration**: Reduce batch size if experiencing memory issues

### 🔧 Configuration Best Practices

#### Parameter Tuning

- **Realistic Ratios**: Use distribution settings that match your business model
- **Dependency Awareness**: Ensure prerequisite data exists before generation
- **Business Logic**: Configure parameters to reflect your actual business rules
- **Performance Balance**: Find the sweet spot between data quality and generation speed

#### Quality Assurance

- **Validation Checks**: Always review dependency validation before generation
- **Sample Testing**: Generate small samples and verify data quality
- **Integration Testing**: Test generated data with EasyCommerce features
- **Cleanup Planning**: Plan for data removal if needed

### 🚀 Performance Optimization

#### System Resources

- **Memory Allocation**: Increase PHP memory limit for large datasets
  ```php
  define('WP_MEMORY_LIMIT', '512M');
  ```
- **Execution Time**: Extend max execution time for long-running generations
  ```php
  ini_set('max_execution_time', 300);
  ```
- **Database Optimization**: Ensure proper database indexing and optimization

#### Monitoring & Troubleshooting

- **Progress Tracking**: Monitor generation progress for performance bottlenecks
- **Error Handling**: Check error logs for failed generations
- **Resource Usage**: Monitor server CPU and memory during generation
- **Rollback Planning**: Have a backup strategy for generated data

### 🛡️ Safety & Maintenance

#### Data Management

- **Backup First**: Always backup your database before large generations
- **Test Environment**: Use staging environments for extensive testing
- **Incremental Generation**: Generate data incrementally rather than all at once
- **Cleanup Procedures**: Know how to remove generated data if needed

#### Security Considerations

- **User Permissions**: Restrict access to administrators only
- **Data Privacy**: Be aware of any sensitive data in generated content
- **Access Logging**: Monitor who generates data and when
- **Compliance**: Ensure generated data complies with your data policies

### 🎨 Advanced Usage Patterns

#### Custom Workflows

- **Automated Testing**: Integrate with CI/CD pipelines for automated testing
- **Performance Benchmarking**: Use for load testing and performance analysis
- **Feature Validation**: Test new EasyCommerce features with realistic data
- **Training Environments**: Create consistent training data for team members

#### Integration Scenarios

- **Plugin Development**: Test integrations with realistic e-commerce data
- **Theme Development**: Validate themes with comprehensive product catalogs
- **API Development**: Test REST APIs with varied data scenarios
- **Migration Testing**: Validate data migrations with production-like datasets

### 📊 Analytics & Reporting

#### Usage Tracking

- **Generation Metrics**: Track what data is generated and how often
- **Performance Data**: Monitor generation speed and resource usage
- **Quality Metrics**: Track data quality and business logic compliance
- **User Adoption**: Monitor which generators are most frequently used

#### Optimization Insights

- **Popular Configurations**: Identify most effective parameter combinations
- **Performance Patterns**: Optimize based on generation speed analytics
- **Data Usage**: Understand which generated data is most valuable
- **Improvement Areas**: Identify generators needing enhancement

### 🔄 Maintenance & Updates

#### Regular Maintenance

- **Version Updates**: Keep EasyCommerce FakerPress updated
- **Dependency Checks**: Regularly validate data dependencies
- **Performance Tuning**: Optimize configurations based on usage patterns
- **Data Cleanup**: Periodically review and clean up test data

#### Troubleshooting Resources

- **Documentation**: Refer to comprehensive docs for complex scenarios
- **Community Support**: Check GitHub issues for similar problems
- **Debug Mode**: Enable debug logging for detailed error information
- **Professional Support**: Contact developers for complex issues
