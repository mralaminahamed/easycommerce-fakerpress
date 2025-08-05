import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

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
            setError(err.message || __('An error occurred while generating coupons.', 'easycommerce-fakerpress'));
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        locale: {
            description: __('Locale for generated data', 'easycommerce-fakerpress'),
            type: 'string',
            enum: ['en_US', 'en_GB', 'fr_FR', 'de_DE', 'es_ES', 'it_IT', 'ja_JP', 'zh_CN'],
            default: 'en_US'
        },
        seed: {
            description: __('Random seed for reproducible data generation', 'easycommerce-fakerpress'),
            type: 'integer',
            minimum: 1
        },
        status: {
            description: __('Status filter for generated coupons', 'easycommerce-fakerpress'),
            type: 'string',
            enum: ['active', 'inactive', 'draft', 'pending'],
            default: 'active'
        },
        date_range: {
            description: __('Date range for coupon validity', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                start: { type: 'string', format: 'date', description: __('Start date (YYYY-MM-DD)', 'easycommerce-fakerpress') },
                end: { type: 'string', format: 'date', description: __('End date (YYYY-MM-DD)', 'easycommerce-fakerpress') }
            }
        },
        relationships: {
            description: __('Control relationship creation with existing data', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                create_missing: { type: 'boolean', default: true, description: __('Create missing related items if needed', 'easycommerce-fakerpress') },
                link_existing: { type: 'boolean', default: true, description: __('Link to existing items when possible', 'easycommerce-fakerpress') }
            }
        },
        meta_options: {
            description: __('Metadata generation options', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                include_meta: { type: 'boolean', default: true, description: __('Include additional metadata', 'easycommerce-fakerpress') },
                custom_fields: { type: 'boolean', default: false, description: __('Generate custom fields', 'easycommerce-fakerpress') }
            }
        }
    };

    return (
        <GeneratorBase
            title={__('Generate Coupons', 'easycommerce-fakerpress')}
            description={__('Create fake discount coupons with random codes, amounts, and expiration dates. Configure locale, status, date ranges, and metadata options.', 'easycommerce-fakerpress')}
            type="coupons"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}
