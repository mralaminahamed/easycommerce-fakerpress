import React, { useState } from 'react';
import { Tab } from '@headlessui/react';
import ProductGenerator from './ProductGenerator';
import CustomerGenerator from './CustomerGenerator';
import OrderGenerator from './OrderGenerator';
import CouponGenerator from './CouponGenerator';
import ProductVariationGenerator from './ProductVariationGenerator';
import ShippingPlanGenerator from './ShippingPlanGenerator';
import TaxGenerator from './TaxGenerator';
import TransactionGenerator from './TransactionGenerator';
import CartSessionGenerator from './CartSessionGenerator';
import LocationGenerator from './LocationGenerator';

function classNames(...classes) {
    return classes.filter(Boolean).join(' ');
}

export default function App() {
    const tabs = [
        // Core generators
        { name: 'Products', component: ProductGenerator, category: 'core' },
        { name: 'Customers', component: CustomerGenerator, category: 'core' },
        { name: 'Orders', component: OrderGenerator, category: 'core' },
        { name: 'Coupons', component: CouponGenerator, category: 'core' },
        
        // Enhanced generators (Version 2.0)
        { name: 'Product Variations', component: ProductVariationGenerator, category: 'enhanced' },
        { name: 'Shipping Plans', component: ShippingPlanGenerator, category: 'enhanced' },
        { name: 'Tax Classes', component: TaxGenerator, category: 'enhanced' },
        { name: 'Transactions', component: TransactionGenerator, category: 'enhanced' },
        { name: 'Cart Sessions', component: CartSessionGenerator, category: 'enhanced' },
        { name: 'Locations', component: LocationGenerator, category: 'enhanced' },
    ];

    return (
        <div className="ecfp-container">
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-900">EasyCommerce FakerPress</h1>
                <p className="mt-1 text-sm text-gray-600">
                    Generate fake ecommerce data for testing and development purposes.
                </p>
            </div>

            <Tab.Group>
                <Tab.List className="flex space-x-1 rounded-xl bg-wp-gray p-1">
                    {tabs.map((tab) => (
                        <Tab
                            key={tab.name}
                            className={({ selected }) =>
                                classNames(
                                    'w-full rounded-lg py-2.5 text-sm font-medium leading-5 text-wp-blue',
                                    'ring-white ring-opacity-60 ring-offset-2 ring-offset-wp-blue focus:outline-none focus:ring-2',
                                    selected
                                        ? 'bg-white shadow'
                                        : 'text-gray-600 hover:bg-white/[0.12] hover:text-wp-blue'
                                )
                            }
                        >
                            {tab.name}
                        </Tab>
                    ))}
                </Tab.List>
                <Tab.Panels className="mt-6">
                    {tabs.map((tab, idx) => (
                        <Tab.Panel
                            key={idx}
                            className={classNames(
                                'rounded-xl bg-white p-6',
                                'ring-white ring-opacity-60 ring-offset-2 ring-offset-wp-blue focus:outline-none focus:ring-2'
                            )}
                        >
                            <tab.component />
                        </Tab.Panel>
                    ))}
                </Tab.Panels>
            </Tab.Group>
        </div>
    );
}