import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '@/admin/components/GeneratorBase';

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function ShippingPlanGenerator() {
	const [ isLoading, setIsLoading ] = useState<boolean>( false );
	const [ result, setResult ] = useState<GeneratorResult | null>( null );
	const [ error, setError ] = useState<string | null>( null );

	const handleGenerate = async ( params: Record<string, any> ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/shipping-plans/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data as GeneratorResult );
		} catch ( err ) {
			const errorMessage =
        err instanceof Error
        	? err.message
        	: __(
        		'An error occurred while generating shipping plans.',
        		'easycommerce-fakerpress',
        	);
			setError( errorMessage );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		shipping_types: {
			description: __(
				'Types of shipping methods to generate',
				'easycommerce-fakerpress',
			),
			type: 'array',
			items: {
				type: 'string',
				enum: [
					'standard',
					'express',
					'overnight',
					'pickup',
					'free',
					'weight_based',
					'flat_rate',
				],
			},
			default: [ 'standard', 'express', 'free' ],
		},
		cost_range: {
			description: __( 'Shipping cost range', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min: {
					description: __( 'Minimum shipping cost', 'easycommerce-fakerpress' ),
					type: 'number',
					minimum: 0,
					default: 0,
				},
				max: {
					description: __( 'Maximum shipping cost', 'easycommerce-fakerpress' ),
					type: 'number',
					minimum: 0,
					default: 50,
				},
			},
		},
		coverage_areas: {
			description: __( 'Geographic coverage areas', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'domestic', 'international', 'regional', 'worldwide' ],
			},
			default: [ 'domestic', 'international' ],
		},
		calculation_methods: {
			description: __(
				'Shipping calculation methods',
				'easycommerce-fakerpress',
			),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'flat_rate', 'weight_based', 'price_based', 'quantity_based' ],
			},
			default: [ 'flat_rate', 'weight_based' ],
		},
		delivery_timeframes: {
			description: __( 'Delivery time ranges', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min_days: {
					description: __( 'Minimum delivery days', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 0,
					default: 1,
				},
				max_days: {
					description: __( 'Maximum delivery days', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 1,
					default: 14,
				},
			},
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Shipping Plans', 'easycommerce-fakerpress' ) }
			description={ __(
				'Generate shipping methods, zones, and rate tables. Test delivery calculations and logistics workflows.',
				'easycommerce-fakerpress',
			) }
			type="shipping-plan"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
		/>
	);
}
