import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function TransactionGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/transactions/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating transactions.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		transaction_types: {
			description: __( 'Types of transactions to generate', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'payment', 'refund', 'adjustment', 'fee', 'commission' ],
			},
			default: [ 'payment', 'refund', 'adjustment' ],
		},
		payment_gateways: {
			description: __( 'Payment gateways to simulate', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'stripe', 'paypal', 'square', 'authorize_net', 'braintree' ],
			},
			default: [ 'stripe', 'paypal', 'square' ],
		},
		amount_range: {
			description: __( 'Transaction amount range', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min: { type: 'number', minimum: 0, default: 1 },
				max: { type: 'number', minimum: 1, default: 1000 },
			},
		},
		status_distribution: {
			description: __( 'Transaction status distribution', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				success_rate: { type: 'integer', minimum: 0, maximum: 100, default: 85 },
				pending_rate: { type: 'integer', minimum: 0, maximum: 100, default: 10 },
				failed_rate: { type: 'integer', minimum: 0, maximum: 100, default: 5 },
			},
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Transactions', 'easycommerce-fakerpress' ) }
			description={ __( 'Create realistic payment transaction history with different gateways, amounts, and status distributions.', 'easycommerce-fakerpress' ) }
			type="transactions"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
		/>
	);
}
