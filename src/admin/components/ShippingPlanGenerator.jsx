import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import EnhancedGeneratorBase from './EnhancedGeneratorBase';

export default function ShippingPlanGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (params) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/shipping-plans/generate',
                method: 'POST',
                data: params
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating shipping plans.');
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        shipping_types: {
            description: 'Types of shipping methods to generate',
            type: 'array',
            items: {
                type: 'string',
                enum: ['standard', 'express', 'overnight', 'pickup', 'free', 'weight_based', 'flat_rate']
            },
            default: ['standard', 'express', 'free']
        },
        cost_range: {
            description: 'Shipping cost range',
            type: 'object',
            properties: {
                min: { type: 'number', minimum: 0, default: 0 },
                max: { type: 'number', minimum: 0, default: 50 }
            }
        },
        coverage_areas: {
            description: 'Geographic coverage areas',
            type: 'array',
            items: {
                type: 'string',
                enum: ['domestic', 'international', 'regional', 'worldwide']
            },
            default: ['domestic', 'international']
        },
        calculation_methods: {
            description: 'Shipping calculation methods',
            type: 'array',
            items: {
                type: 'string',
                enum: ['flat_rate', 'weight_based', 'price_based', 'quantity_based']
            },
            default: ['flat_rate', 'weight_based']
        },
        delivery_timeframes: {
            description: 'Delivery time ranges',
            type: 'object',
            properties: {
                min_days: { type: 'integer', minimum: 0, default: 1 },
                max_days: { type: 'integer', minimum: 1, default: 14 }
            }
        }
    };

    return (
        <EnhancedGeneratorBase
            title="Generate Shipping Plans"
            description="Create comprehensive shipping plans with different methods, costs, coverage areas, and delivery timeframes."
            type="shipping-plans"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}