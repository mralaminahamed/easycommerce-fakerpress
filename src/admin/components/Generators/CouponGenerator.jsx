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
        discount_types: {
            description: __('Types of discount coupons to generate', 'easycommerce-fakerpress'),
            type: 'array',
            items: {
                type: 'string',
                enum: ['percentage', 'fixed_amount', 'free_shipping', 'buy_x_get_y']
            },
            default: ['percentage', 'fixed_amount']
        },
        discount_range: {
            description: __('Discount value range', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                min_percentage: { type: 'integer', minimum: 5, maximum: 95, default: 10 },
                max_percentage: { type: 'integer', minimum: 5, maximum: 95, default: 50 },
                min_fixed: { type: 'number', minimum: 1, default: 5 },
                max_fixed: { type: 'number', minimum: 1, default: 100 }
            }
        },
        usage_limits: {
            description: __('Usage limitation settings', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                set_usage_limits: { type: 'boolean', default: true },
                max_uses: { type: 'integer', minimum: 1, maximum: 1000, default: 100 },
                max_uses_per_user: { type: 'integer', minimum: 1, maximum: 10, default: 1 }
            }
        },
        validity_period: {
            description: __('Coupon validity period configuration', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                min_days: { type: 'integer', minimum: 1, maximum: 365, default: 7 },
                max_days: { type: 'integer', minimum: 1, maximum: 365, default: 90 }
            }
        },
        restrictions: {
            description: __('Coupon usage restrictions', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                minimum_spend: { type: 'boolean', default: true },
                maximum_spend: { type: 'boolean', default: false },
                exclude_sale_items: { type: 'boolean', default: false },
                product_restrictions: { type: 'boolean', default: true }
            }
        }
    };

    return (
        <GeneratorBase
            title={__('Generate Coupons', 'easycommerce-fakerpress')}
            description={__('Create discount coupons with configurable types, discount values, usage limits, validity periods, and usage restrictions.', 'easycommerce-fakerpress')}
            type="coupons"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}
