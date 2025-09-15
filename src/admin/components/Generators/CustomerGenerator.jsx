import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';
import { useDataValidation } from '../DataValidator';

export default function CustomerGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );
	const validationStatus = useDataValidation( 'customers' );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/customers/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating customers.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		customer_types: {
			description: __( 'Types of customers to generate', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'regular', 'vip', 'wholesale', 'guest', 'returning' ],
			},
			default: [ 'regular', 'returning' ],
		},
		demographics: {
			description: __( 'Demographic distribution and customer characteristics', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				age_groups: {
					type: 'array',
					items: {
						type: 'string',
						enum: [ '18-25', '26-35', '36-45', '46-55', '56-65', '65+' ],
					},
					default: [ '26-35', '36-45', '46-55' ],
				},
				gender_distribution: {
					type: 'object',
					properties: {
						male: { type: 'integer', minimum: 0, maximum: 100, default: 45 },
						female: { type: 'integer', minimum: 0, maximum: 100, default: 45 },
						other: { type: 'integer', minimum: 0, maximum: 100, default: 10 },
					},
				},
			},
		},
		address_preferences: {
			description: __( 'Address generation preferences', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				include_billing: { type: 'boolean', default: true },
				include_shipping: { type: 'boolean', default: true },
				different_addresses_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 30 },
			},
		},
		purchase_history: {
			description: __( 'Purchase history simulation', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				simulate_history: { type: 'boolean', default: true },
				loyalty_tiers: { type: 'boolean', default: true },
			},
		},
		contact_preferences: {
			description: __( 'Contact and communication preferences', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				phone_numbers: { type: 'boolean', default: true },
				marketing_opt_in_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 65 },
				email_verification_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 90 },
				phone_verification_ratio: { type: 'integer', minimum: 0, maximum: 100, default: 65 },
			},
		},
		loyalty_settings: {
			description: __( 'Customer loyalty and engagement settings', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				vip_ratio: { type: 'integer', minimum: 0, maximum: 25, default: 8 },
				referral_program: { type: 'boolean', default: true },
				loyalty_point_system: { type: 'boolean', default: true },
			},
		},
		customer_segments: {
			description: __( 'Customer segmentation and targeting', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [
					'high_value_customer',
					'frequent_shopper',
					'bargain_seeker',
					'early_adopter',
					'loyal_customer',
					'gift_shopper',
					'bulk_purchaser',
					'international_customer',
					'mobile_shopper',
					'social_media_engaged',
					'product_reviewer',
					'seasonal_shopper',
				],
			},
			default: [ 'loyal_customer', 'frequent_shopper', 'bargain_seeker' ],
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Customers', 'easycommerce-fakerpress' ) }
			description={ __( 'Create realistic customer accounts with demographics, addresses, contact information, and purchase history simulation.', 'easycommerce-fakerpress' ) }
			type="customers"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
			validationStatus={ validationStatus }
		/>
	);
}
