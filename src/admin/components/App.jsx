import React, { useState } from 'react';
import { ChevronRightIcon, ArrowLeftIcon } from '@heroicons/react/24/outline';
import { __, sprintf } from '@wordpress/i18n';
import { Button } from '@headlessui/react';

import ProductGenerator from './Generators/ProductGenerator';
import CustomerGenerator from './Generators/CustomerGenerator';
import OrderGenerator from './Generators/OrderGenerator';
import CouponGenerator from './Generators/CouponGenerator';
import ProductVariationGenerator from './Generators/ProductVariationGenerator';
import ShippingPlanGenerator from './Generators/ShippingPlanGenerator';
import TaxGenerator from './Generators/TaxGenerator';
import TransactionGenerator from './Generators/TransactionGenerator';
import CartSessionGenerator from './Generators/CartSessionGenerator';
import LocationGenerator from './Generators/LocationGenerator';

export default function App() {
    const generators = [
        { name: __('Products', 'easycommerce-fakerpress'), component: ProductGenerator, category: __('Core', 'easycommerce-fakerpress'), order: 1, description: __('Generate fake product data for testing.', 'easycommerce-fakerpress') },
        { name: __('Customers', 'easycommerce-fakerpress'), component: CustomerGenerator, category: __('Core', 'easycommerce-fakerpress'), order: 2, description: __('Generate fake customer data for testing.', 'easycommerce-fakerpress') },
        { name: __('Orders', 'easycommerce-fakerpress'), component: OrderGenerator, category: __('Core', 'easycommerce-fakerpress'), order: 3, description: __('Generate fake order data for testing.', 'easycommerce-fakerpress') },
        { name: __('Coupons', 'easycommerce-fakerpress'), component: CouponGenerator, category: __('Core', 'easycommerce-fakerpress'), order: 4, description: __('Generate fake coupon data for testing.', 'easycommerce-fakerpress') },
        { name: __('Product Variations', 'easycommerce-fakerpress'), component: ProductVariationGenerator, category: __('Enhanced', 'easycommerce-fakerpress'), order: 1, description: __('Generate fake product variation data.', 'easycommerce-fakerpress') },
        { name: __('Shipping Plans', 'easycommerce-fakerpress'), component: ShippingPlanGenerator, category: __('Enhanced', 'easycommerce-fakerpress'), order: 2, description: __('Generate fake shipping plan data.', 'easycommerce-fakerpress') },
        { name: __('Tax Classes', 'easycommerce-fakerpress'), component: TaxGenerator, category: __('Enhanced', 'easycommerce-fakerpress'), order: 3, description: __('Generate fake tax class data.', 'easycommerce-fakerpress') },
        { name: __('Transactions', 'easycommerce-fakerpress'), component: TransactionGenerator, category: __('Enhanced', 'easycommerce-fakerpress'), order: 4, description: __('Generate fake transaction data.', 'easycommerce-fakerpress') },
        { name: __('Cart Sessions', 'easycommerce-fakerpress'), component: CartSessionGenerator, category: __('Enhanced', 'easycommerce-fakerpress'), order: 5, description: __('Generate fake cart session data.', 'easycommerce-fakerpress') },
        { name: __('Locations', 'easycommerce-fakerpress'), component: LocationGenerator, category: __('Enhanced', 'easycommerce-fakerpress'), order: 6, description: __('Generate fake location data.', 'easycommerce-fakerpress') },
    ];

    const [selectedGenerator, setSelectedGenerator] = useState(null);

    const groupedGenerators = generators.reduce((acc, generator) => {
        if (!acc[generator.category]) {
            acc[generator.category] = [];
        }
        acc[generator.category].push(generator);
        return acc;
    }, {});

    const sortedCategories = Object.keys(groupedGenerators).sort();
    const sortedGenerators = sortedCategories.flatMap(category =>
        groupedGenerators[category].sort((a, b) => a.order - b.order)
    );

    return (
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-900 tracking-tight">{__('EasyCommerce FakerPress', 'easycommerce-fakerpress')}</h1>
                <p className="mt-2 text-sm text-gray-500 leading-6">
                    {__('Generate fake ecommerce data for testing and development with a seamless and intuitive interface.', 'easycommerce-fakerpress')}
                </p>
            </div>

            {selectedGenerator ? (
                <div className="flex gap-6">
                    {/* Sidebar for other generators */}
                    <aside className="w-64 hidden lg:block">
                        <h2 className="text-sm font-semibold text-gray-900 mb-4">{__('Other Generators', 'easycommerce-fakerpress')}</h2>
                        <ul className="space-y-2">
                            {sortedGenerators
                                .filter(gen => gen.name !== selectedGenerator.name)
                                .map(gen => (
                                    <li key={gen.name}>
                                        <Button
                                            onClick={() => setSelectedGenerator(gen)}
                                            className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-md transition-colors data-[focus]:ring-2 data-[focus]:ring-blue-500"
                                        >
                                            {gen.name}
                                        </Button>
                                    </li>
                                ))}
                        </ul>
                    </aside>

                    {/* Main content with back button and breadcrumb */}
                    <main className="flex-1">
                        <div className="flex flex-row-reverse justify-between mb-6">
                            <Button
                                onClick={() => setSelectedGenerator(null)}
                                className="inline-flex items-center px-4 py-2 rounded-md bg-gray-100 text-gray-700 font-medium text-sm hover:bg-gray-200 data-[focus]:ring-2 data-[focus]:ring-blue-500 transition-colors"
                            >
                                <ArrowLeftIcon className="h-5 w-5 mr-2" aria-hidden="true" />
                                {__('Back to Generators', 'easycommerce-fakerpress')}
                            </Button>
                            <nav aria-label="Breadcrumb" className="flex">
                                <ol className="flex items-center space-x-2">
                                    <li>
                                        <Button
                                            onClick={() => setSelectedGenerator(null)}
                                            className="text-sm text-blue-600 hover:text-blue-800 transition-colors"
                                        >
                                            {__('Home', 'easycommerce-fakerpress')}
                                        </Button>
                                    </li>
                                    <li>
                                        <ChevronRightIcon className="h-4 w-4 text-gray-400" aria-hidden="true" />
                                    </li>
                                    <li>
                                        <span className="text-sm text-gray-500">{selectedGenerator.name}</span>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                        <div className="rounded-xl bg-white p-6 shadow-sm transition-all">
                            <selectedGenerator.component />
                        </div>
                    </main>
                </div>
            ) : (
                <div className="space-y-8">
                    {sortedCategories.map(category => (
                        <div key={category}>
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">{sprintf(__('%s Generators', 'easycommerce-fakerpress'), category)}</h2>
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                {groupedGenerators[category].map(generator => (
                                    <Button
                                        key={generator.name}
                                        onClick={() => setSelectedGenerator(generator)}
                                        className="text-left bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all border border-gray-200 hover:border-blue-200 data-[focus]:ring-2 data-[focus]:ring-blue-500"
                                    >
                                        <h3 className="text-base font-medium text-gray-900">{generator.name}</h3>
                                        <p className="mt-1 text-sm text-gray-500">{generator.description}</p>
                                    </Button>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
