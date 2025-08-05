import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import GeneratorBase from '../GeneratorBase';

export default function CouponGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (params) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/coupons/generate',
                method: 'POST',
                data: params
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating coupons.');
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        locale: {
            description: 'Locale for generated data',
            type: 'string',
            enum: ['en_US', 'en_GB', 'fr_FR', 'de_DE', 'es_ES', 'it_IT', 'ja_JP', 'zh_CN'],
            default: 'en_US'
        },
        seed: {
            description: 'Random seed for reproducible data generation',
            type: 'integer',
            minimum: 1
        },
        status: {
            description: 'Status filter for generated coupons',
            type: 'string',
            enum: ['active', 'inactive', 'draft', 'pending'],
            default: 'active'
        },
        date_range: {
            description: 'Date range for coupon validity',
            type: 'object',
            properties: {
                start: { type: 'string', format: 'date', description: 'Start date (YYYY-MM-DD)' },
                end: { type: 'string', format: 'date', description: 'End date (YYYY-MM-DD)' }
            }
        },
        relationships: {
            description: 'Control relationship creation with existing data',
            type: 'object',
            properties: {
                create_missing: { type: 'boolean', default: true, description: 'Create missing related items if needed' },
                link_existing: { type: 'boolean', default: true, description: 'Link to existing items when possible' }
            }
        },
        meta_options: {
            description: 'Metadata generation options',
            type: 'object',
            properties: {
                include_meta: { type: 'boolean', default: true, description: 'Include additional metadata' },
                custom_fields: { type: 'boolean', default: false, description: 'Generate custom fields' }
            }
        }
    };

    return (
        <GeneratorBase
            title="Generate Coupons"
            description="Create fake discount coupons with random codes, amounts, and expiration dates. Configure locale, status, date ranges, and metadata options."
            type="coupons"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}
