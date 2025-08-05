import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import EnhancedGeneratorBase from './EnhancedGeneratorBase';

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
            setError(err.message || 'An error occurred while generating product variations.');
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        variation_type: {
            description: 'Type of variations to generate',
            type: 'string',
            enum: ['color', 'size', 'material', 'style', 'mixed'],
            default: 'mixed'
        },
        price_variation: {
            description: 'Price variation settings',
            type: 'object',
            properties: {
                enable_variation: { type: 'boolean', default: true },
                variation_percentage: { type: 'integer', minimum: 5, maximum: 50, default: 20 }
            }
        },
        stock_distribution: {
            description: 'Stock quantity distribution',
            type: 'object',
            properties: {
                low_stock_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 10 },
                out_of_stock_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 5 }
            }
        },
        attribute_complexity: {
            description: 'Attribute complexity level',
            type: 'string',
            enum: ['simple', 'moderate', 'complex'],
            default: 'moderate'
        }
    };

    return (
        <EnhancedGeneratorBase
            title="Generate Product Variations"
            description="Create realistic product variations with different attributes, prices, and stock levels. Requires existing products to create variations for."
            type="product-variations"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}