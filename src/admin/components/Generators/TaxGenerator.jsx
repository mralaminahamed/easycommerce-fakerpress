import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function TaxGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/taxes/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating tax classes.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		tax_types: {
			description: __( 'Types of tax classes to generate', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'standard', 'reduced', 'luxury', 'digital', 'zero_rate' ],
			},
			default: [ 'standard', 'reduced', 'luxury' ],
		},
		rate_ranges: {
			description: __( 'Tax rate ranges', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min_rate: { type: 'number', minimum: 0, maximum: 50, default: 0 },
				max_rate: { type: 'number', minimum: 0, maximum: 50, default: 25 },
			},
		},
		jurisdictions: {
			description: __( 'Tax jurisdictions', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 'federal', 'state', 'county', 'city', 'special_district' ],
			},
			default: [ 'federal', 'state', 'city' ],
		},
	};

	return (
		<GeneratorBase
			title={ __( 'Generate Tax Classes', 'easycommerce-fakerpress' ) }
			description={ __( 'Create tax classes with location-based rates for different jurisdictions and product types.', 'easycommerce-fakerpress' ) }
			type="taxes"
			onGenerate={ handleGenerate }
			isLoading={ isLoading }
			result={ result }
			error={ error }
			parameterConfig={ parameterConfig }
		/>
	);
}
