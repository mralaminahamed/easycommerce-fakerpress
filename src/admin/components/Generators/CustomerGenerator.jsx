import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import GeneratorBase from '../GeneratorBase';

export default function CustomerGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (params) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/customers/generate',
                method: 'POST',
                data: params
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating customers.');
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        customer_types: {
            description: 'Types of customers to generate',
            type: 'array',
            items: {
                type: 'string',
                enum: ['regular', 'vip', 'wholesale', 'guest', 'returning']
            },
            default: ['regular', 'returning']
        },
        demographics: {
            description: 'Demographic distribution',
            type: 'object',
            properties: {
                age_groups: {
                    type: 'array',
                    items: {
                        type: 'string',
                        enum: ['18-25', '26-35', '36-45', '46-55', '56-65', '65+']
                    },
                    default: ['26-35', '36-45', '46-55']
                },
                gender_distribution: {
                    type: 'object',
                    properties: {
                        male: { type: 'integer', minimum: 0, maximum: 100, default: 45 },
                        female: { type: 'integer', minimum: 0, maximum: 100, default: 45 },
                        other: { type: 'integer', minimum: 0, maximum: 100, default: 10 }
                    }
                }
            }
        },
        address_preferences: {
            description: 'Address generation preferences',
            type: 'object',
            properties: {
                include_billing: { type: 'boolean', default: true },
                include_shipping: { type: 'boolean', default: true },
                different_addresses_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 30 }
            }
        },
        purchase_history: {
            description: 'Purchase history simulation',
            type: 'object',
            properties: {
                simulate_history: { type: 'boolean', default: true },
                loyalty_tiers: { type: 'boolean', default: true }
            }
        },
        contact_preferences: {
            description: 'Contact and communication preferences',
            type: 'object',
            properties: {
                phone_numbers: { type: 'boolean', default: true },
                marketing_opt_in_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 65 }
            }
        }
    };

    return (
        <GeneratorBase
            title="Generate Customers"
            description="Create realistic customer accounts with demographics, addresses, contact information, and purchase history simulation."
            type="customers"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}
