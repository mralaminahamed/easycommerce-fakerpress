import {
  type LucideIcon,
  Star,
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
} from "lucide-react";

import { __ } from "@wordpress/i18n";

import type { Generator } from "@/admin/types";
import CartSessionGenerator from "@/admin/components/Generators/CartSessionGenerator";
import CouponGenerator from "@/admin/components/Generators/CouponGenerator";
import CustomerGenerator from "@/admin/components/Generators/CustomerGenerator";
import LocationGenerator from "@/admin/components/Generators/LocationGenerator";
import OrderGenerator from "@/admin/components/Generators/OrderGenerator";
import ProductGenerator from "@/admin/components/Generators/ProductGenerator";
import ProductReviewGenerator from "@/admin/components/Generators/ProductReviewGenerator";
import ProductVariationGenerator from "@/admin/components/Generators/ProductVariationGenerator";
import ShippingPlanGenerator from "@/admin/components/Generators/ShippingPlanGenerator";
import TaxClassGenerator from "@/admin/components/Generators/TaxClassGenerator";
import TransactionGenerator from "@/admin/components/Generators/TransactionGenerator";

export type { Generator };

export const generators: Generator[] = [
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
    route: "tax-classes",
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
    route: "transaction",
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
    category: __("Enhanced", "easycommerce-fakerpress"),
    order: 10,
    icon: MapPin,
    description: __(
      "Generate geographic data including countries, states, and cities for multi-region stores.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Store owners setting up multi-region stores",
      "easycommerce-fakerpress",
    ),
    route: "locations",
  },
  {
    name: __("Product Reviews", "easycommerce-fakerpress"),
    component: ProductReviewGenerator,
    category: __("Enhanced", "easycommerce-fakerpress"),
    order: 11,
    icon: Star,
    description: __(
      "Create realistic product reviews with ratings and customer feedback. Reviews are automatically linked to existing products and customers.",
      "easycommerce-fakerpress",
    ),
    useCase: __(
      "Store owners, theme developers testing review displays",
      "easycommerce-fakerpress",
    ),
    route: "product-reviews",
  },
];
