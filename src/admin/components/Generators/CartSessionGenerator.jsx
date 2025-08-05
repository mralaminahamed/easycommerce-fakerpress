import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

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
            setError(err.message || __('An error occurred while generating cart sessions.', 'easycommerce-fakerpress'));
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        session_statuses: {
            description: __('Cart session statuses to generate', 'easycommerce-fakerpress'),
            type: 'array',
            items: {
                type: 'string',
                enum: ['pending', 'abandoned', 'completed', 'cancelled']
            },
            default: ['pending', 'abandoned', 'completed']
        },
        abandonment_scenarios: {
            description: __('Cart abandonment scenarios', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                abandonment_rate: { type: 'integer', minimum: 0, maximum: 100, default: 70 },
                average_abandon_time: { type: 'integer', minimum: 1, maximum: 1440, default: 30 }
            }
        },
        items_per_cart: {
            description: __('Items per cart session', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                min: { type: 'integer', minimum: 1, default: 1 },
                max: { type: 'integer', minimum: 1, default: 10 }
            }
        },
        value_ranges: {
            description: __('Cart value ranges', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                min_value: { type: 'number', minimum: 0, default: 10 },
                max_value: { type: 'number', minimum: 1, default: 500 }
            }
        },
        recovery_simulation: {
            description: __('Cart recovery simulation', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                enable_recovery: { type: 'boolean', default: true },
                recovery_rate: { type: 'integer', minimum: 0, maximum: 100, default: 15 }
            }
        }
    };

    return (
        <GeneratorBase
            title={__('Generate Cart Sessions', 'easycommerce-fakerpress')}
            description={__('Create shopping cart sessions with abandonment scenarios for analyzing cart recovery and customer behavior.', 'easycommerce-fakerpress')}
            type="cart-sessions"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}
