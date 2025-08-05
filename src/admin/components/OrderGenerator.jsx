import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import EnhancedGeneratorBase from './EnhancedGeneratorBase';

export default function OrderGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (params) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/orders/generate',
                method: 'POST',
                data: params
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating orders.');
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        order_status: {
            description: 'Order status distribution',
            type: 'string',
            enum: ['pending', 'processing', 'completed', 'cancelled', 'on_hold', 'refunded', 'mixed'],
            default: 'mixed'
        },
        customer_type: {
            description: 'Type of customers for orders',
            type: 'string',
            enum: ['existing', 'new', 'mixed'],
            default: 'mixed'
        },
        order_value: {
            description: 'Order value configuration',
            type: 'object',
            properties: {
                min_total: { type: 'number', minimum: 0, default: 10 },
                max_total: { type: 'number', minimum: 1, default: 1000 }
            }
        },
        items_per_order: {
            description: 'Number of items per order',
            type: 'object',
            properties: {
                min: { type: 'integer', minimum: 1, default: 1 },
                max: { type: 'integer', minimum: 1, maximum: 20, default: 5 }
            }
        },
        payment_methods: {
            description: 'Payment methods to use',
            type: 'array',
            items: {
                type: 'string',
                enum: ['stripe', 'paypal', 'bank_transfer', 'cash_on_delivery', 'credit_card']
            },
            default: ['stripe', 'paypal', 'bank_transfer']
        },
        geographical_distribution: {
            description: 'Geographic distribution of orders',
            type: 'object',
            properties: {
                countries: {
                    type: 'array',
                    items: {
                        type: 'string',
                        enum: ['US', 'CA', 'GB', 'AU', 'DE', 'FR']
                    },
                    default: ['US', 'CA', 'GB']
                }
            }
        }
    };

    return (
        <EnhancedGeneratorBase
            title="Generate Orders"
            description="Create realistic orders with comprehensive data including customers, products, payments, shipping, and taxes. Enhanced with Order_Item_Meta and location-based addresses."
            type="orders"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}
