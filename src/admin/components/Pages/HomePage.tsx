import React from "react";
import { Link } from "react-router-dom";
import { motion, Variants } from "framer-motion";
import {
  Package,
  Users,
  ShoppingCart,
  Tag,
  Settings,
  Truck,
  DollarSign,
  CreditCard,
  ShoppingBag,
  MapPin,
  Star,
  ArrowRight,
  type LucideIcon,
} from "lucide-react";
import { __, sprintf } from "@wordpress/i18n";

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "../ui/card";
import { Badge } from "../ui/badge";

import ProductGenerator from "../Generators/ProductGenerator";
import CustomerGenerator from "../Generators/CustomerGenerator";
import OrderGenerator from "../Generators/OrderGenerator";
import CouponGenerator from "../Generators/CouponGenerator";
import ProductVariationGenerator from "../Generators/ProductVariationGenerator";
import ShippingPlanGenerator from "../Generators/ShippingPlanGenerator";
import TaxClassGenerator from "../Generators/TaxClassGenerator";
import TransactionGenerator from "../Generators/TransactionGenerator";
import CartSessionGenerator from "../Generators/CartSessionGenerator";
import LocationGenerator from "../Generators/LocationGenerator";

interface Generator {
  name: string;
  component: React.ComponentType<any>;
  category: string;
  order: number;
  icon: LucideIcon;
  description: string;
  useCase?: string;
  route: string;
  popular?: boolean;
}

const generators: Generator[] = [
  {
    name: __("Products", "easycommerce-fakerpress"),
    component: ProductGenerator,
    category: __("Core", "easycommerce-fakerpress"),
    order: 1,
    icon: Package,
    description: __(
      "Create realistic products with prices, categories, inventory, and variations. Perfect for testing your store catalog and product pages.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Store owners, theme developers, plugin testers",
      "easycommerce-fakerpress",
    ),
    route: "products",
    popular: true,
  },
  {
    name: __("Customers", "easycommerce-fakerpress"),
    component: CustomerGenerator,
    category: __("Core", "easycommerce-fakerpress"),
    order: 2,
    icon: Users,
    description: __(
      "Generate customer profiles with addresses, purchase history, and loyalty data. Essential for testing user accounts and customer management.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Store owners, CRM developers, membership site testers",
      "easycommerce-fakerpress",
    ),
    route: "customers",
    popular: true,
  },
  {
    name: __("Orders", "easycommerce-fakerpress"),
    component: OrderGenerator,
    category: __("Core", "easycommerce-fakerpress"),
    order: 3,
    icon: ShoppingCart,
    description: __(
      "Create complete order histories with payments, shipping, and tax calculations. Test your checkout flow and order management system.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Store owners, payment gateway developers, shipping testers",
      "easycommerce-fakerpress",
    ),
    route: "orders",
    popular: true,
  },
  {
    name: __("Coupons", "easycommerce-fakerpress"),
    component: CouponGenerator,
    category: __("Core", "easycommerce-fakerpress"),
    order: 4,
    icon: Tag,
    description: __(
      "Generate discount codes with various rules and restrictions. Perfect for testing promotional campaigns and discount logic.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Store owners, marketing teams, discount plugin developers",
      "easycommerce-fakerpress",
    ),
    route: "coupons",
  },
  {
    name: __("Product Variations", "easycommerce-fakerpress"),
    component: ProductVariationGenerator,
    category: __("Advanced", "easycommerce-fakerpress"),
    order: 1,
    icon: Settings,
    description: __(
      "Create complex product variations with size, color, and material options. Essential for testing variable product functionality.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "E-commerce developers, product catalog managers",
      "easycommerce-fakerpress",
    ),
    route: "product-variations",
  },
  {
    name: __("Shipping Plans", "easycommerce-fakerpress"),
    component: ShippingPlanGenerator,
    category: __("Advanced", "easycommerce-fakerpress"),
    order: 2,
    icon: Truck,
    description: __(
      "Generate shipping methods, zones, and rate tables. Test delivery calculations and logistics workflows.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Store owners, shipping plugin developers, logistics teams",
      "easycommerce-fakerpress",
    ),
    route: "shipping-plans",
  },
  {
    name: __("Tax Classes", "easycommerce-fakerpress"),
    component: TaxClassGenerator,
    category: __("Advanced", "easycommerce-fakerpress"),
    order: 3,
    icon: DollarSign,
    description: __(
      "Create tax rules and classes for different regions and product types. Perfect for testing international tax compliance.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Store owners, accountants, tax plugin developers",
      "easycommerce-fakerpress",
    ),
    route: "tax_classes",
  },
  {
    name: __("Transactions", "easycommerce-fakerpress"),
    component: TransactionGenerator,
    category: __("Advanced", "easycommerce-fakerpress"),
    order: 4,
    icon: CreditCard,
    description: __(
      "Generate payment transaction records with multiple gateways and statuses. Test financial reporting and reconciliation.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Payment gateway developers, accountants, financial analysts",
      "easycommerce-fakerpress",
    ),
    route: "transactions",
  },
  {
    name: __("Cart Sessions", "easycommerce-fakerpress"),
    component: CartSessionGenerator,
    category: __("Advanced", "easycommerce-fakerpress"),
    order: 5,
    icon: ShoppingBag,
    description: __(
      "Create shopping cart abandonment scenarios and session data. Test cart recovery systems and analytics.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Marketing teams, cart recovery plugin developers",
      "easycommerce-fakerpress",
    ),
    route: "cart-sessions",
  },
  {
    name: __("Locations", "easycommerce-fakerpress"),
    component: LocationGenerator,
    category: __("Advanced", "easycommerce-fakerpress"),
    order: 6,
    icon: MapPin,
    description: __(
      "Populate geographic data with countries, states, and cities. Foundation data for shipping, taxes, and regional features.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Store owners setting up multi-region stores",
      "easycommerce-fakerpress",
    ),
    route: "locations",
  },
];

export default function HomePage() {
  const groupedGenerators = generators.reduce(
    (acc, generator) => {
      if (!acc[generator.category]) {
        acc[generator.category] = [];
      }
      acc[generator.category].push(generator);
      return acc;
    },
    {} as Record<string, Generator[]>,
  );

  const sortedCategories = Object.keys(groupedGenerators).sort((a, b) => {
    // Put "Core" category first
    if (a === __("Core", "easycommerce-fakerpress")) return -1;
    if (b === __("Core", "easycommerce-fakerpress")) return 1;
    // Then sort alphabetically
    return a.localeCompare(b);
  });

  // Animation variants
  const containerVariants: Variants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1,
        delayChildren: 0.2,
      },
    },
  };

  const categoryVariants: Variants = {
    hidden: { opacity: 0, y: 20 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 0.6,
        ease: [0.4, 0.0, 0.2, 1],
      },
    },
  };

  const cardVariants: Variants = {
    hidden: { opacity: 0, y: 30, scale: 0.95 },
    visible: {
      opacity: 1,
      y: 0,
      scale: 1,
      transition: {
        duration: 0.5,
        ease: [0.4, 0.0, 0.2, 1],
      },
    },
    hover: {
      y: -8,
      scale: 1.02,
      transition: {
        duration: 0.2,
        ease: [0.4, 0.0, 0.2, 1],
      },
    },
  };

  return (
    <motion.div
      className="max-w-7xl mx-auto px-6 space-y-12"
      variants={containerVariants}
      initial="hidden"
      animate="visible"
    >
      {sortedCategories.map((category, categoryIndex) => (
        <motion.div
          key={category}
          variants={categoryVariants}
          className="space-y-6"
        >
          <motion.div
            className="text-center"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: categoryIndex * 0.1 + 0.3 }}
          >
            <h2 className="text-3xl font-bold text-gray-900 mb-2">
              {sprintf(
                __("%s Generators", "easycommerce-fakerpress"),
                category,
              )}
            </h2>
            <motion.div
              className="h-1 w-24 bg-linear-to-r from-blue-500 to-purple-600 rounded-full mx-auto"
              initial={{ width: 0 }}
              animate={{ width: 96 }}
              transition={{ delay: categoryIndex * 0.1 + 0.5, duration: 0.8 }}
            />
          </motion.div>

          <motion.div
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"
            variants={containerVariants}
          >
            {groupedGenerators[category]
              .sort((a, b) => a.order - b.order)
              .map((generator, index) => {
                const IconComponent = generator.icon;
                return (
                  <motion.div
                    key={generator.name}
                    variants={cardVariants}
                    whileHover="hover"
                    className="group"
                  >
                    <Link
                      to={`/generator/${generator.route}`}
                      className="block h-full"
                    >
                      <Card className="h-full transition-all duration-300 hover:shadow-lg border-2 hover:border-blue-200 bg-linear-to-br from-white to-gray-50/50">
                        <CardHeader className="pb-4">
                          <div className="flex items-start justify-between">
                            <motion.div
                              className="flex items-center justify-center w-12 h-12 bg-linear-to-br from-blue-100 to-purple-100 rounded-xl mb-4"
                              whileHover={{ scale: 1.1, rotate: 5 }}
                              transition={{ type: "spring", stiffness: 300 }}
                            >
                              <IconComponent className="w-6 h-6 text-blue-600" />
                            </motion.div>
                            {generator.popular && (
                              <motion.div
                                initial={{ scale: 0, rotate: -180 }}
                                animate={{ scale: 1, rotate: 0 }}
                                transition={{
                                  delay: 0.3 + index * 0.1,
                                  type: "spring",
                                  stiffness: 200,
                                }}
                              >
                                <Badge
                                  variant="secondary"
                                  className="bg-linear-to-r from-orange-400 to-red-500 text-white border-0"
                                >
                                  <Star className="w-3 h-3 mr-1" />
                                  {__("Popular", "easycommerce-fakerpress")}
                                </Badge>
                              </motion.div>
                            )}
                          </div>
                          <CardTitle className="text-xl group-hover:text-blue-700 transition-colors">
                            {generator.name}
                          </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-0">
                          <CardDescription className="text-sm leading-relaxed mb-4">
                            {generator.description}
                          </CardDescription>
                          {generator.useCase && (
                            <div className="pt-4 border-t border-gray-100">
                              <p className="text-xs text-gray-500 font-medium flex items-center">
                                <span className="inline-block w-2 h-2 bg-blue-400 rounded-full mr-2"></span>
                                {__("Best for:", "easycommerce-fakerpress")}{" "}
                                {generator.useCase}
                              </p>
                            </div>
                          )}
                          <motion.div
                            className="mt-4 flex items-center text-sm text-blue-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity"
                            initial={{ x: -10 }}
                            whileHover={{ x: 0 }}
                          >
                            {__("Get started", "easycommerce-fakerpress")}
                            <ArrowRight className="w-4 h-4 ml-1" />
                          </motion.div>
                        </CardContent>
                      </Card>
                    </Link>
                  </motion.div>
                );
              })}
          </motion.div>
        </motion.div>
      ))}
    </motion.div>
  );
}

// Export generators for use in other components
export { generators };
