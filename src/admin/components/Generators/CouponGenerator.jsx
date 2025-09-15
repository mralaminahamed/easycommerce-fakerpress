import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function CouponGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/coupons/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating coupons.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		discount_types: {
			description: __( 'Types of discount coupons to generate', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'percentage', 'fixed', 'fixed_product', 'buy_x_get_y' ],
			},
			default: [ 'percentage', 'fixed' ],
		},
		discount_range: {
			description: __( 'Discount value ranges by type', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				percentage_range: {
					type: 'object',
					properties: {
						min: { type: 'integer', minimum: 5, maximum: 50, default: 10 },
						max: { type: 'integer', minimum: 10, maximum: 70, default: 50 },
					},
				},
				fixed_range: {
					type: 'object',
					properties: {
						min: { type: 'number', minimum: 5, maximum: 50, default: 10 },
						max: { type: 'number', minimum: 25, maximum: 200, default: 100 },
					},
				},
				fixed_product_range: {
					type: 'object',
					properties: {
						min: { type: 'number', minimum: 2, maximum: 10, default: 5 },
						max: { type: 'number', minimum: 10, maximum: 25, default: 20 },
					},
				},
			},
		},
		usage_limits: {
			description: __( 'Usage limitation and restrictions settings', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				set_usage_limits: { type: 'boolean', default: true },
				usage_limit_probability: { type: 'integer', minimum: 50, maximum: 100, default: 80 },
				max_uses_range: {
					type: 'object',
					properties: {
						min: { type: 'integer', minimum: 1, maximum: 10, default: 5 },
						max: { type: 'integer', minimum: 50, maximum: 500, default: 200 },
					},
				},
				per_customer_limits: { type: 'boolean', default: true },
				max_uses_per_customer: {
					type: 'object',
					properties: {
						min: { type: 'integer', minimum: 1, maximum: 3, default: 1 },
						max: { type: 'integer', minimum: 3, maximum: 10, default: 5 },
					},
				},
			},
		},
		validity_period: {
			description: __( 'Coupon validity period and scheduling', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				set_date_range: { type: 'boolean', default: true },
				date_range_probability: { type: 'integer', minimum: 80, maximum: 100, default: 95 },
				start_date_range: {
					type: 'object',
					properties: {
						min_days_past: { type: 'integer', minimum: 0, maximum: 60, default: 0 },
						max_days_future: { type: 'integer', minimum: 1, maximum: 14, default: 7 },
					},
				},
				validity_duration: {
					type: 'object',
					properties: {
						min_days: { type: 'integer', minimum: 7, maximum: 30, default: 14 },
						max_days: { type: 'integer', minimum: 30, maximum: 365, default: 180 },
					},
				},
			},
		},
		restrictions: {
			description: __( 'Advanced coupon usage restrictions and rules', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				minimum_spend: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: true },
						probability: { type: 'integer', minimum: 50, maximum: 100, default: 75 },
						range: {
							type: 'object',
							properties: {
								min: { type: 'number', minimum: 20, maximum: 100, default: 50 },
								max: { type: 'number', minimum: 200, maximum: 500, default: 300 },
							},
						},
					},
				},
				maximum_spend: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: false },
						probability: { type: 'integer', minimum: 10, maximum: 50, default: 25 },
					},
				},
				product_restrictions: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: true },
						probability: { type: 'integer', minimum: 20, maximum: 60, default: 40 },
					},
				},
				category_restrictions: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: true },
						probability: { type: 'integer', minimum: 20, maximum: 50, default: 35 },
					},
				},
				customer_restrictions: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: true },
						probability: { type: 'integer', minimum: 10, maximum: 30, default: 20 },
						types: {
							type: 'array',
							items: {
								type: 'string',
								enum: [ 'new_customers', 'existing_customers', 'vip_customers', 'registered_users' ],
							},
							default: [ 'new_customers', 'existing_customers' ],
						},
					},
				},
				sale_items_policy: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: true },
						probability: { type: 'integer', minimum: 20, maximum: 50, default: 35 },
						allow_ratio: { type: 'integer', minimum: 50, maximum: 90, default: 75 },
					},
				},
			},
		},
		advanced_features: {
			description: __( 'Advanced coupon features and behaviors', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				free_shipping: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: true },
						probability: { type: 'integer', minimum: 10, maximum: 30, default: 15 },
					},
				},
				first_time_customer: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: true },
						probability: { type: 'integer', minimum: 5, maximum: 20, default: 10 },
					},
				},
				stackable: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: false },
						probability: { type: 'integer', minimum: 5, maximum: 15, default: 10 },
					},
				},
				auto_apply: {
					type: 'object',
					properties: {
						enabled: { type: 'boolean', default: false },
						probability: { type: 'integer', minimum: 5, maximum: 15, default: 10 },
					},
				},
				active_ratio: { type: 'integer', minimum: 70, maximum: 100, default: 90 },
			},
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Coupons', 'easycommerce-fakerpress' ) }
			description={ __( 'Create discount coupons with configurable types, discount values, usage limits, validity periods, and usage restrictions.', 'easycommerce-fakerpress' ) }
			type="coupons"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
		/>
	);
}
