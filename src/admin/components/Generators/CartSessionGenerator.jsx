import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function CartSessionGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/cart-sessions/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating cart sessions.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		customer_type: {
			description: __( 'Type of customers for cart sessions', 'easycommerce-fakerpress' ),
			type: 'string',
			enum: [ 'existing', 'new', 'mixed', 'specific', 'guest_only' ],
			default: 'mixed',
		},
		specific_customer_id: {
			description: __( 'Specific customer ID for cart sessions (when customer_type is "specific")', 'easycommerce-fakerpress' ),
			type: 'integer',
			minimum: 1,
			dependsOn: { customer_type: 'specific' },
		},
		guest_cart_ratio: {
			description: __( 'Percentage of guest carts (0–100) when customer_type is "mixed"', 'easycommerce-fakerpress' ),
			type: 'integer',
			minimum: 0,
			maximum: 100,
			default: 40,
			dependsOn: { customer_type: 'mixed' },
		},
		abandonment_rate: {
			description: __( 'Cart abandonment rate percentage (0–100)', 'easycommerce-fakerpress' ),
			type: 'integer',
			minimum: 0,
			maximum: 100,
			default: 30,
		},
		status_distribution: {
			description: __( 'Custom cart status distribution', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				pending: {
					description: __( 'Percentage of pending carts', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 0,
					maximum: 100,
				},
				abandoned: {
					description: __( 'Percentage of abandoned carts', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 0,
					maximum: 100,
				},
				completed: {
					description: __( 'Percentage of completed carts', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 0,
					maximum: 100,
				},
				cancelled: {
					description: __( 'Percentage of cancelled carts', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 0,
					maximum: 100,
				},
			},
		},
		cart_value_range: {
			description: __( 'Cart value range for generated sessions', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min: {
					description: __( 'Minimum cart value', 'easycommerce-fakerpress' ),
					type: 'number',
					minimum: 0,
					default: 5,
				},
				max: {
					description: __( 'Maximum cart value', 'easycommerce-fakerpress' ),
					type: 'number',
					minimum: 1,
					default: 500,
				},
			},
		},
		items_per_cart: {
			description: __( 'Number of items per cart session', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min: {
					description: __( 'Minimum items per cart', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 1,
					default: 1,
				},
				max: {
					description: __( 'Maximum items per cart', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 1,
					maximum: 15,
					default: 5,
				},
			},
		},
		abandonment_tracking: {
			description: __( 'Abandonment tracking settings', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				generate_reminders: {
					description: __( 'Generate abandoned cart reminders', 'easycommerce-fakerpress' ),
					type: 'boolean',
					default: true,
				},
				reminder_count: {
					description: __( 'Maximum number of reminders to generate', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 0,
					maximum: 10,
					default: 3,
				},
				recovery_rate: {
					description: __( 'Cart recovery rate percentage (0–100)', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 0,
					maximum: 100,
					default: 15,
				},
			},
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Cart Sessions', 'easycommerce-fakerpress' ) }
			description={ __( 'Create shopping cart sessions with abandonment scenarios for analyzing cart recovery and customer behavior.', 'easycommerce-fakerpress' ) }
			type="cart_session"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
		/>
	);
}
