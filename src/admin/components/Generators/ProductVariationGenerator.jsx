import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function ProductVariationGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (params) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/product-variations/generate',
                method: 'POST',
                data: params
            });

            setResult(data);
        } catch (err) {
            setError(err.message || __('An error occurred while generating product variations.', 'easycommerce-fakerpress'));
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        variation_type: {
            description: __('Type of variations to generate', 'easycommerce-fakerpress'),
            type: 'string',
            enum: ['color', 'size', 'material', 'style', 'mixed'],
            default: 'mixed'
        },
        price_variation: {
            description: __('Price variation settings', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                enable_variation: { type: 'boolean', default: true },
                variation_percentage: { type: 'integer', minimum: 5, maximum: 50, default: 20 }
            }
        },
        stock_distribution: {
            description: __('Stock quantity distribution', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                low_stock_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 10 },
                out_of_stock_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 5 }
            }
        },
        attribute_complexity: {
            description: __('Attribute complexity level', 'easycommerce-fakerpress'),
            type: 'string',
            enum: ['simple', 'moderate', 'complex'],
            default: 'moderate'
        }
    };

    return (
        <GeneratorBase
            title={__('Generate Product Variations', 'easycommerce-fakerpress')}
            description={__('Create realistic product variations with different attributes, prices, and stock levels. Requires existing products to create variations for.', 'easycommerce-fakerpress')}
            type="product-variations"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}
