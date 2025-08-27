import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

export default function LocationGenerator() {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ result, setResult ] = useState( null );
	const [ error, setError ] = useState( null );

	const handleGenerate = async ( params ) => {
		setIsLoading( true );
		setError( null );
		setResult( null );

		try {
			const data = await apiFetch( {
				path: '/easycommerce-fakerpress/v1/locations/generate',
				method: 'POST',
				data: params,
			} );

			setResult( data );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred while generating location data.', 'easycommerce-fakerpress' ) );
		} finally {
			setIsLoading( false );
		}
	};

	const parameterConfig = {
		regions: {
			description: __( 'Geographic regions to generate locations for', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
				enum: [ 
					'Americas', 'Europe', 'Asia', 'Africa', 'Oceania', 
					'Northern America', 'Western Europe', 'Eastern Europe', 'Southern Europe', 'Northern Europe',
					'Southeast Asia', 'East Asia', 'South Asia', 'Western Asia',
					'North Africa', 'Sub-Saharan Africa', 'Australia and New Zealand'
				],
			},
		},
		countries: {
			description: __( 'Specific countries to generate (ISO2, ISO3, or full names)', 'easycommerce-fakerpress' ),
			type: 'array',
			items: {
				type: 'string',
			},
		},
		max_countries: {
			description: __( 'Maximum number of countries to generate', 'easycommerce-fakerpress' ),
			type: 'integer',
			minimum: 1,
			maximum: 50,
			default: 10,
		},
		include_states: {
			description: __( 'Include states/provinces for countries', 'easycommerce-fakerpress' ),
			type: 'boolean',
			default: true,
		},
		include_cities: {
			description: __( 'Include cities for states/provinces', 'easycommerce-fakerpress' ),
			type: 'boolean',
			default: true,
		},
		cities_per_state: {
			description: __( 'Maximum cities per state/province', 'easycommerce-fakerpress' ),
			type: 'object',
			properties: {
				min: {
					description: __( 'Minimum cities per state', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 1,
					default: 3,
				},
				max: {
					description: __( 'Maximum cities per state', 'easycommerce-fakerpress' ),
					type: 'integer',
					minimum: 1,
					maximum: 50,
					default: 15,
				},
			},
		},
		include_coordinates: {
			description: __( 'Include latitude/longitude coordinates', 'easycommerce-fakerpress' ),
			type: 'boolean',
			default: true,
		},
		include_currencies: {
			description: __( 'Include currency information', 'easycommerce-fakerpress' ),
			type: 'boolean',
			default: true,
		},
	};

	return (
		<div>
			<GeneratorBase
				title={ __( 'Generate Location Data', 'easycommerce-fakerpress' ) }
				description={ __( 'Create comprehensive location hierarchy data (countries, states, cities) for the EasyCommerce system. This populates the locations.json file used throughout the system.', 'easycommerce-fakerpress' ) }
				type="locations"
				onGenerate={ handleGenerate }
				isLoading={ isLoading }
				result={ result }
				error={ error }
				parameterConfig={ parameterConfig }
			/>

			<div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
				<h4 className="font-medium text-blue-900 mb-2">{ __( 'Important Note', 'easycommerce-fakerpress' ) }</h4>
				<p className="text-sm text-blue-800">
					{ __( 'The Location Generator creates the foundational geographic data used by other generators. It\'s recommended to run this first, especially before generating customers and orders that rely on realistic address data. The generated data is saved to the EasyCommerce locations.json file.', 'easycommerce-fakerpress' ) }
				</p>
			</div>
		</div>
	);
}
