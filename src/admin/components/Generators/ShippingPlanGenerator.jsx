import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';
import { useDataValidation } from '../DataValidator';

export default function ShippingPlanGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );
	const validationStatus = useDataValidation( 'shipping-plans' );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/shipping-plans/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating shipping plans.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		shipping_types: {
			description: __( 'Types of shipping plans to generate', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'standard', 'express', 'overnight', 'free', 'weight' ],
			},
			default: [ 'standard', 'express', 'free' ],
		},
		cost_range: {
			description: __( 'Shipping cost range', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min: { type: 'number', minimum: 0, default: 0 },
				max: { type: 'number', minimum: 0, default: 50 },
			},
		},
		coverage_areas: {
			description: __( 'Geographic coverage areas and regional scope', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'north_america', 'europe', 'asia_pacific', 'international', 'worldwide' ],
			},
			default: [ 'north_america', 'europe' ],
		},
		calculation_methods: {
			description: __( 'Shipping calculation methods', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'flat_rate', 'weight_based', 'price_based', 'quantity_based' ],
			},
			default: [ 'flat_rate', 'weight_based' ],
		},
		delivery_timeframes: {
			description: __( 'Delivery time ranges and scheduling', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min_days: { type: 'integer', minimum: 0, default: 1 },
				max_days: { type: 'integer', minimum: 1, default: 14 },
			},
		},
		plan_settings: {
			description: __( 'Shipping plan configuration settings', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				active_ratio: { type: 'integer', minimum: 50, maximum: 100, default: 85 },
				taxable_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 60 },
				regions_per_plan: {
					type: 'object',
					properties: {
						min: { type: 'integer', minimum: 1, maximum: 5, default: 2 },
						max: { type: 'integer', minimum: 3, maximum: 15, default: 8 },
					},
				},
			},
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Shipping Plans', 'easycommerce-fakerpress' ) }
			description={ __( 'Create comprehensive shipping plans with different methods, costs, coverage areas, and delivery timeframes.', 'easycommerce-fakerpress' ) }
			type="shipping-plans"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
			validationStatus={ validationStatus }
		/>
	);
}
