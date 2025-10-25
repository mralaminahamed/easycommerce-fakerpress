import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function ProductVariationGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/product-variations/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating product variations.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		specific_product_id: {
			description: __( 'Specific product ID to generate variations for', 'easycommerce-fakerpress' ),
			type: 'integer',
			minimum: 1,
		},
		product_types: {
			description: __( 'Product types to consider for variation generation', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'simple', 'variable', 'grouped', 'external', 'digital' ],
			},
			default: [ 'simple', 'variable' ],
		},
		exclude_products: {
			description: __( 'Product IDs to exclude from variation generation', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'integer',
			},
		},
		price_variance: {
			description: __( 'Price variance settings for variations', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min_percentage: {
					description: __( 'Minimum price variance percentage from base product', 'easycommerce-fakerpress' ),
					type: 'number',
					minimum: -50,
					maximum: 50,
					default: -20,
				},
				max_percentage: {
					description: __( 'Maximum price variance percentage from base product', 'easycommerce-fakerpress' ),
					type: 'number',
					minimum: -50,
					maximum: 100,
					default: 30,
				},
			},
		},
		stock_settings: {
			description: __( 'Stock management settings for variations', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				manage_stock: {
					description: __( 'Enable stock management for variations', 'easycommerce-fakerpress' ),
					type: 'boolean',
					default: true,
				},
				stock_range: {
					description: __( 'Stock quantity range', 'easycommerce-fakerpress' ),
					type: 'object',
					properties: {
						min: {
							type: 'integer',
							minimum: 0,
							default: 0,
						},
						max: {
							type: 'integer',
							minimum: 1,
							default: 100,
						},
					},
				},
			},
		},
		variation_attributes: {
			description: __( 'Attribute generation settings', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				create_missing_attributes: {
					description: __( 'Create missing attributes if needed', 'easycommerce-fakerpress' ),
					type: 'boolean',
					default: true,
				},
				max_attributes_per_variation: {
					description: __( 'Maximum attributes per variation', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 1,
					maximum: 10,
					default: 3,
				},
			},
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Product Variations', 'easycommerce-fakerpress' ) }
			description={ __( 'Create realistic product variations with different attributes, prices, and stock levels. Requires existing products to create variations for.', 'easycommerce-fakerpress' ) }
			type="product_variation"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
		/>
	);
}
