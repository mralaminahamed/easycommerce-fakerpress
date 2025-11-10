import React from "react";
import { Link } from "react-router-dom";
import { __, sprintf } from "@wordpress/i18n";

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

const generators = [
  {
    name: __("Products", "easycommerce-fakerpress"),
    component: ProductGenerator,
    category: __("Core", "easycommerce-fakerpress"),
    order: 1,
    description: __(
      "Generate fake product data for testing.",
      "easycommerce-fakerpress",
    ),
    route: "products",
  },
  {
    name: __("Customers", "easycommerce-fakerpress"),
    component: CustomerGenerator,
    category: __("Core", "easycommerce-fakerpress"),
    order: 2,
    description: __(
      "Generate fake customer data for testing.",
      "easycommerce-fakerpress",
    ),
    route: "customers",
  },
  {
    name: __("Orders", "easycommerce-fakerpress"),
    component: OrderGenerator,
    category: __("Core", "easycommerce-fakerpress"),
    order: 3,
    description: __(
      "Generate fake order data for testing.",
      "easycommerce-fakerpress",
    ),
    route: "orders",
  },
  {
    name: __("Coupons", "easycommerce-fakerpress"),
    component: CouponGenerator,
    category: __("Core", "easycommerce-fakerpress"),
    order: 4,
    description: __(
      "Generate fake coupon data for testing.",
      "easycommerce-fakerpress",
    ),
    route: "coupons",
  },
  {
    name: __("Product Variations", "easycommerce-fakerpress"),
    component: ProductVariationGenerator,
    category: __("Enhanced", "easycommerce-fakerpress"),
    order: 1,
    description: __(
      "Generate fake product variation data.",
      "easycommerce-fakerpress",
    ),
    route: "product-variations",
  },
  {
    name: __("Shipping Plans", "easycommerce-fakerpress"),
    component: ShippingPlanGenerator,
    category: __("Enhanced", "easycommerce-fakerpress"),
    order: 2,
    description: __(
      "Generate fake shipping plan data.",
      "easycommerce-fakerpress",
    ),
    route: "shipping-plans",
  },
  {
    name: __("Tax Classes", "easycommerce-fakerpress"),
    component: TaxClassGenerator,
    category: __("Enhanced", "easycommerce-fakerpress"),
    order: 3,
    description: __("Generate fake tax class data.", "easycommerce-fakerpress"),
    route: "tax_classes",
  },
  {
    name: __("Transactions", "easycommerce-fakerpress"),
    component: TransactionGenerator,
    category: __("Enhanced", "easycommerce-fakerpress"),
    order: 4,
    description: __(
      "Generate fake transaction data.",
      "easycommerce-fakerpress",
    ),
    route: "transactions",
  },
  {
    name: __("Cart Sessions", "easycommerce-fakerpress"),
    component: CartSessionGenerator,
    category: __("Enhanced", "easycommerce-fakerpress"),
    order: 5,
    description: __(
      "Generate fake cart session data.",
      "easycommerce-fakerpress",
    ),
    route: "cart-sessions",
  },
  {
    name: __("Locations", "easycommerce-fakerpress"),
    component: LocationGenerator,
    category: __("Enhanced", "easycommerce-fakerpress"),
    order: 6,
    description: __("Generate fake location data.", "easycommerce-fakerpress"),
    route: "locations",
  },
];

export default function HomePage() {
  const groupedGenerators = generators.reduce((acc, generator) => {
    if (!acc[generator.category]) {
      acc[generator.category] = [];
    }
    acc[generator.category].push(generator);
    return acc;
  }, {});

  const sortedCategories = Object.keys(groupedGenerators).sort();

  return (
    <div className="space-y-8">
      {sortedCategories.map((category) => (
        <div key={category}>
          <h2 className="text-lg font-semibold text-gray-900 mb-4">
            {
              /* translators: %s: Category name (e.g., Core, Enhanced) */ sprintf(
                __("%s Generators", "easycommerce-fakerpress"),
                category,
              )
            }
          </h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {groupedGenerators[category].map((generator) => (
              <Link
                key={generator.name}
                to={`/generator/${generator.route}`}
                className="block text-left bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all border border-gray-200 hover:border-blue-200"
              >
                <h3 className="text-base font-medium text-gray-900">
                  {generator.name}
                </h3>
                <p className="mt-1 text-sm text-gray-500">
                  {generator.description}
                </p>
              </Link>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}

// Export generators for use in other components
export { generators };
