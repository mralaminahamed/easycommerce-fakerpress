import React, { useState } from 'react';
import { ChevronRightIcon, ArrowLeftIcon } from '@heroicons/react/24/outline';
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

function classNames(...classes) {
    return classes.filter(Boolean).join(' ');
}

export default function App() {
    const generators = [
        { name: 'Products', component: ProductGenerator, category: 'Core', order: 1, description: 'Generate fake product data for testing.' },
        { name: 'Customers', component: CustomerGenerator, category: 'Core', order: 2, description: 'Generate fake customer data for testing.' },
        { name: 'Orders', component: OrderGenerator, category: 'Core', order: 3, description: 'Generate fake order data for testing.' },
        { name: 'Coupons', component: CouponGenerator, category: 'Core', order: 4, description: 'Generate fake coupon data for testing.' },
        { name: 'Product Variations', component: ProductVariationGenerator, category: 'Enhanced', order: 1, description: 'Generate fake product variation data.' },
        { name: 'Shipping Plans', component: ShippingPlanGenerator, category: 'Enhanced', order: 2, description: 'Generate fake shipping plan data.' },
        { name: 'Tax Classes', component: TaxGenerator, category: 'Enhanced', order: 3, description: 'Generate fake tax class data.' },
        { name: 'Transactions', component: TransactionGenerator, category: 'Enhanced', order: 4, description: 'Generate fake transaction data.' },
        { name: 'Cart Sessions', component: CartSessionGenerator, category: 'Enhanced', order: 5, description: 'Generate fake cart session data.' },
        { name: 'Locations', component: LocationGenerator, category: 'Enhanced', order: 6, description: 'Generate fake location data.' },
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
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-900 tracking-tight">EasyCommerce FakerPress</h1>
                <p className="mt-2 text-sm text-gray-500 leading-relaxed">
                    Generate fake ecommerce data for testing and development with a seamless and intuitive interface.
                </p>
            </div>

            {selectedGenerator ? (
                <div className="flex">
                    {/* Sidebar for other generators */}
                    <div className="w-64 hidden lg:block">
                        <h2 className="text-sm font-semibold text-gray-900 mb-4">Other Generators</h2>
                        <ul className="space-y-2">
                            {sortedGenerators
                                .filter(gen => gen.name !== selectedGenerator.name)
                                .map(gen => (
                                    <li key={gen.name}>
                                        <button
                                            onClick={() => setSelectedGenerator(gen)}
                                            className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-md transition-colors duration-200"
                                        >
                                            {gen.name}
                                        </button>
                                    </li>
                                ))}
                        </ul>
                    </div>

                    {/* Main content with back button and breadcrumb */}
                    <div className="flex-1">
                        <div className="flex flex-row-reverse justify-between mb-6 w-full">
                            <button
                                onClick={() => setSelectedGenerator(null)}
                                className="inline-flex items-center px-4 py-2 rounded-md bg-gray-100 text-gray-700 font-medium text-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                            >
                                <ArrowLeftIcon className="h-5 w-5 mr-2" aria-hidden="true" />
                                Back to Generators
                            </button>
                            <nav className="flex" aria-label="Breadcrumb">
                                <ol className="flex items-center space-x-2">
                                    <li>
                                        <button
                                            onClick={() => setSelectedGenerator(null)}
                                            className="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200"
                                        >
                                            Home
                                        </button>
                                    </li>
                                    <li>
                                        <ChevronRightIcon className="h-4 w-4 text-gray-400" />
                                    </li>
                                    <li>
                                        <span className="text-sm text-gray-500">{selectedGenerator.name}</span>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                        <div className="rounded-xl bg-white p-6 shadow-sm transition-all duration-200">
                            <selectedGenerator.component />
                        </div>
                    </div>
                </div>
            ) : (
                <div className="space-y-8">
                    {sortedCategories.map(category => (
                        <div key={category}>
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">{category} Generators</h2>
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                {groupedGenerators[category].map(generator => (
                                    <button
                                        key={generator.name}
                                        onClick={() => setSelectedGenerator(generator)}
                                        className="text-left bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 border border-gray-200 hover:border-blue-200"
                                    >
                                        <h3 className="text-base font-medium text-gray-900">{generator.name}</h3>
                                        <p className="mt-1 text-sm text-gray-500">{generator.description}</p>
                                    </button>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
