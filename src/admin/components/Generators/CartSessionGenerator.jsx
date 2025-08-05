import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import GeneratorBase from '../GeneratorBase';

export default function CartSessionGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (params) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/cart-sessions/generate',
                method: 'POST',
                data: params
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating cart sessions.');
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        session_statuses: {
            description: 'Cart session statuses to generate',
            type: 'array',
            items: {
                type: 'string',
                enum: ['pending', 'abandoned', 'completed', 'cancelled']
            },
            default: ['pending', 'abandoned', 'completed']
        },
        abandonment_scenarios: {
            description: 'Cart abandonment scenarios',
            type: 'object',
            properties: {
                abandonment_rate: { type: 'integer', minimum: 0, maximum: 100, default: 70 },
                average_abandon_time: { type: 'integer', minimum: 1, maximum: 1440, default: 30 }
            }
        },
        items_per_cart: {
            description: 'Items per cart session',
            type: 'object',
            properties: {
                min: { type: 'integer', minimum: 1, default: 1 },
                max: { type: 'integer', minimum: 1, default: 10 }
            }
        },
        value_ranges: {
            description: 'Cart value ranges',
            type: 'object',
            properties: {
                min_value: { type: 'number', minimum: 0, default: 10 },
                max_value: { type: 'number', minimum: 1, default: 500 }
            }
        },
        recovery_simulation: {
            description: 'Cart recovery simulation',
            type: 'object',
            properties: {
                enable_recovery: { type: 'boolean', default: true },
                recovery_rate: { type: 'integer', minimum: 0, maximum: 100, default: 15 }
            }
        }
    };

    return (
        <GeneratorBase
            title="Generate Cart Sessions"
            description="Create shopping cart sessions with abandonment scenarios for analyzing cart recovery and customer behavior."
            type="cart-sessions"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}
