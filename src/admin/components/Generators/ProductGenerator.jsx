import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function ProductGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (params) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/products/generate',
                method: 'POST',
                data: params
            });

            setResult(data);
        } catch (err) {
            setError(err.message || __('An error occurred while generating products.', 'easycommerce-fakerpress'));
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        product_type: {
            description: __('Type of products to generate', 'easycommerce-fakerpress'),
            type: 'string',
            enum: ['simple', 'variable', 'grouped', 'external', 'digital', 'mixed'],
            default: 'mixed'
        },
        price_range: {
            description: __('Price range for generated products', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                min: { type: 'number', minimum: 0, default: 10 },
                max: { type: 'number', minimum: 1, default: 500 }
            }
        },
        categories: {
            description: __('Product categories configuration', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                create_new: { type: 'boolean', default: true },
                max_per_product: { type: 'integer', minimum: 1, maximum: 10, default: 3 }
            }
        },
        attributes: {
            description: __('Product attributes configuration', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                include_attributes: { type: 'boolean', default: true },
                variation_count: { type: 'integer', minimum: 1, maximum: 20, default: 5 }
            }
        },
        inventory: {
            description: __('Inventory settings', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                manage_stock: { type: 'boolean', default: true },
                stock_range: {
                    type: 'object',
                    properties: {
                        min: { type: 'integer', minimum: 0, default: 0 },
                        max: { type: 'integer', minimum: 1, default: 100 }
                    }
                }
            }
        },
        content_options: {
            description: __('Content generation options', 'easycommerce-fakerpress'),
            type: 'object',
            properties: {
                include_images: { type: 'boolean', default: false },
                description_length: { type: 'string', enum: ['short', 'medium', 'long'], default: 'medium' }
            }
        }
    };

    return (
        <GeneratorBase
            title={__('Generate Products', 'easycommerce-fakerpress')}
            description={__('Create fake products with random names, descriptions, prices, and attributes. Configure product types, pricing, categories, and inventory settings.', 'easycommerce-fakerpress')}
            type="products"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}
