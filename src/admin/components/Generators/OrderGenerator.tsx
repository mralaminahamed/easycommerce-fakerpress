import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '@/admin/components/GeneratorBase';

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function OrderGenerator() {
	const [ isLoading, setIsLoading ] = useState<boolean>( false );
	const [ result, setResult ] = useState<GeneratorResult | null>( null );
	const [ error, setError ] = useState<string | null>( null );

	const handleGenerate = async ( params: Record<string, any> ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/orders/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data as GeneratorResult );
		} catch ( err ) {
			const errorMessage =
        err instanceof Error
        	? err.message
        	: __(
        		'An error occurred while generating orders.',
        		'easycommerce-fakerpress',
        	);
			setError( errorMessage );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		order_status: {
			description: __( 'Order status distribution', 'easycommerce-fakerpress' ),
			type: 'string',
			enum: [
				'pending',
				'processing',
				'completed',
				'cancelled',
				'on_hold',
				'refunded',
				'mixed',
			],
			default: 'mixed',
		},
		customer_type: {
			description: __(
				'Type of customers for orders',
				'easycommerce-fakerpress',
			),
			type: 'string',
			enum: [ 'existing', 'new', 'mixed', 'specific' ],
			default: 'mixed',
		},
		specific_customer_id: {
			description: __(
				"Specific customer ID to use for all orders (when customer_type is 'specific')",
				'easycommerce-fakerpress',
			),
			type: 'integer',
			minimum: 1,
			dependsOn: { customer_type: 'specific' },
		},
		customer_distribution: {
			description: __(
				'Customer type distribution for mixed mode',
				'easycommerce-fakerpress',
			),
			type: 'object',
			properties: {
				existing_ratio: {
					description: __(
						'Percentage of existing customers (0–100)',
						'easycommerce-fakerpress',
					),
					type: 'integer',
					minimum: 0,
					maximum: 100,
					default: 70,
				},
				new_ratio: {
					description: __(
						'Percentage of new customers (0–100)',
						'easycommerce-fakerpress',
					),
					type: 'integer',
					minimum: 0,
					maximum: 100,
					default: 30,
				},
			},
		},
		order_value: {
			description: __( 'Order value configuration', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min_total: {
					description: __( 'Minimum order total', 'easycommerce-fakerpress' ),
					type: 'number',
					minimum: 0,
					default: 10,
				},
				max_total: {
					description: __( 'Maximum order total', 'easycommerce-fakerpress' ),
					type: 'number',
					minimum: 1,
					default: 1000,
				},
			},
		},
		items_per_order: {
			description: __( 'Number of items per order', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min: {
					description: __( 'Minimum items per order', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 1,
					default: 1,
				},
				max: {
					description: __( 'Maximum items per order', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 1,
					maximum: 20,
					default: 5,
				},
			},
		},
		payment_methods: {
			description: __( 'Payment methods to use', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [
					'stripe',
					'paypal',
					'bank_transfer',
					'cash_on_delivery',
					'credit_card',
				],
			},
			default: [ 'stripe', 'paypal', 'bank_transfer' ],
		},
		geographical_distribution: {
			description: __(
				'Geographic distribution of orders',
				'easycommerce-fakerpress',
			),
			type: 'object',
			properties: {
				countries: {
					description: __(
						'Countries to generate orders from',
						'easycommerce-fakerpress',
					),
					type: 'array',
					items: {
						type: 'string',
						enum: [ 'US', 'CA', 'GB', 'AU', 'DE', 'FR' ],
					},
					default: [ 'US', 'CA', 'GB' ],
				},
			},
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Orders', 'easycommerce-fakerpress' ) }
			description={ __(
				'Create complete order histories with payments, shipping, and tax calculations. Test your checkout flow and order management system.',
				'easycommerce-fakerpress',
			) }
			type="order"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
		/>
	);
}
