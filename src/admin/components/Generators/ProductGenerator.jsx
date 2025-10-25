import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function ProductGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/products/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating products.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		product_type: {
			description: __( 'Type of products to generate', 'easycommerce-fakerpress' ),
			type: 'string',
			enum: [ 'physical', 'digital', 'mixed' ],
			default: 'mixed',
		},
		price_range: {
			description: __( 'Price range for generated products', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min: { type: 'number', minimum: 0, default: 10 },
				max: { type: 'number', minimum: 1, default: 500 },
			},
		},
		categories: {
			description: __( 'Product categories and taxonomy configuration', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				create_new_categories: { type: 'boolean', default: true },
				max_categories_per_product: { type: 'integer', minimum: 1, maximum: 10, default: 3 },
				create_brands: { type: 'boolean', default: true },
				max_brands_per_product: { type: 'integer', minimum: 1, maximum: 3, default: 1 },
				assign_tags: { type: 'boolean', default: true },
				tags_per_product: { type: 'integer', minimum: 1, maximum: 8, default: 3 },
			},
		},
		attributes: {
			description: __( 'Product attributes and variations configuration', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				include_attributes: { type: 'boolean', default: true },
				attributes_per_product: { type: 'integer', minimum: 1, maximum: 6, default: 3 },
				max_variations: { type: 'integer', minimum: 1, maximum: 20, default: 10 },
				variation_pricing_variance: { type: 'number', minimum: 0.1, maximum: 0.8, default: 0.3 },
				use_physical_attributes: { type: 'boolean', default: true },
				use_digital_attributes: { type: 'boolean', default: true },
			},
		},
		inventory: {
			description: __( 'Inventory and stock management settings', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				manage_stock: { type: 'boolean', default: true },
				stock_range: {
					type: 'object',
					properties: {
						min: { type: 'integer', minimum: 0, default: 0 },
						max: { type: 'integer', minimum: 1, default: 150 },
					},
				},
				stock_status_distribution: {
					type: 'object',
					properties: {
						in_stock_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 70 },
						low_stock_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 15 },
						out_of_stock_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 10 },
						backorder_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 5 },
					},
				},
				stock_limits: {
					type: 'object',
					properties: {
						min_limit: { type: 'integer', minimum: 5, maximum: 100, default: 10 },
						max_limit: { type: 'integer', minimum: 10, maximum: 200, default: 50 },
					},
				},
			},
		},
		content_options: {
			description: __( 'Content generation and media options', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				include_images: { type: 'boolean', default: false },
				gallery_image_count: { type: 'integer', minimum: 1, maximum: 12, default: 4 },
				description_length: { type: 'string', enum: [ 'short', 'medium', 'long' ], default: 'medium' },
				generate_seo_data: { type: 'boolean', default: true },
				include_specifications: { type: 'boolean', default: true },
			},
		},
		sales_options: {
			description: __( 'Sales and pricing configuration', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				sale_price_probability: { type: 'integer', minimum: 0, maximum: 100, default: 40 },
				sale_discount_range: {
					type: 'object',
					properties: {
						min_discount: { type: 'number', minimum: 0.05, maximum: 0.5, default: 0.1 },
						max_discount: { type: 'number', minimum: 0.1, maximum: 0.7, default: 0.4 },
					},
				},
				tax_class_distribution: {
					type: 'array',
					items: {
						type: 'string',
						enum: [ 'standard', 'reduced-rate', 'zero-rate' ],
					},
					default: [ 'standard', 'reduced-rate' ],
				},
				featured_probability: { type: 'integer', minimum: 0, maximum: 50, default: 25 },
			},
		},
		digital_options: {
			description: __( 'Digital product specific settings', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				download_files_per_product: { type: 'integer', minimum: 1, maximum: 8, default: 2 },
				download_limit_probability: { type: 'integer', minimum: 0, maximum: 100, default: 80 },
				download_expiry_probability: { type: 'integer', minimum: 0, maximum: 100, default: 60 },
				file_formats: {
					type: 'array',
					items: {
						type: 'string',
						enum: [ 'pdf', 'mp4', 'mp3', 'zip', 'exe', 'dmg' ],
					},
					default: [ 'pdf', 'zip', 'mp4' ],
				},
			},
			dependsOn: {
				product_type: [ 'digital', 'mixed' ],
			},
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Products', 'easycommerce-fakerpress' ) }
			description={ __( 'Create fake products with random names, descriptions, prices, and attributes. Configure product types, pricing, categories, and inventory settings.', 'easycommerce-fakerpress' ) }
			type="products"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
		/>
	);
}
