import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function OrderGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/orders/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating orders.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		order_status: {
			description: __( 'Order status distribution', 'easycommerce-fakerpress' ),
			type: 'string',
			enum: [ 'pending', 'processing', 'completed', 'cancelled', 'on_hold', 'refunded', 'mixed' ],
			default: 'mixed',
		},
		customer_type: {
			description: __( 'Type of customers for orders', 'easycommerce-fakerpress' ),
			type: 'string',
			enum: [ 'existing', 'new', 'mixed' ],
			default: 'mixed',
		},
		order_value: {
			description: __( 'Order value configuration', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min_total: { type: 'number', minimum: 0, default: 10 },
				max_total: { type: 'number', minimum: 1, default: 1000 },
			},
		},
		items_per_order: {
			description: __( 'Number of items per order', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min: { type: 'integer', minimum: 1, default: 1 },
				max: { type: 'integer', minimum: 1, maximum: 20, default: 5 },
			},
		},
		payment_methods: {
			description: __( 'Payment methods to use', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'stripe', 'paypal', 'bank_transfer', 'cash_on_delivery', 'credit_card' ],
			},
			default: [ 'stripe', 'paypal', 'bank_transfer' ],
		},
		geographical_distribution: {
			description: __( 'Geographic distribution of orders', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				countries: {
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
			description={ __( 'Create realistic orders with comprehensive data including customers, products, payments, shipping, and taxes. Enhanced with Order_Item_Meta and location-based addresses.', 'easycommerce-fakerpress' ) }
			type="orders"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
		/>
	);
}
