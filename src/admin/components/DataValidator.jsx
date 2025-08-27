import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Data Validation Hook
 * 
 * Validates data availability and dependencies for generators
 */
export function useDataValidation() {
	const [ validationCache, setValidationCache ] = useState( new Map() );
	
	/**
	 * Check data availability for a generator type
	 * 
	 * @param {string} generatorType Generator type to check
	 * @returns {Promise<Object>} Validation status
	 */
	const checkDataAvailability = async ( generatorType ) => {
		// Check cache first
		const cacheKey = `availability_${generatorType}`;
		if ( validationCache.has( cacheKey ) ) {
			const cached = validationCache.get( cacheKey );
			// Return cached result if less than 5 minutes old
			if ( Date.now() - cached.timestamp < 300000 ) {
				return cached.data;
			}
		}

		try {
			const response = await apiFetch( {
				path: `/easycommerce-fakerpress/v1/validation/check-data/${generatorType}`,
				method: 'GET',
			} );

			// Cache the result
			setValidationCache( prev => new Map( prev ).set( cacheKey, {
				data: response,
				timestamp: Date.now(),
			} ) );

			return response;
		} catch ( error ) {
			console.error( 'Data validation error:', error );
			return {
				ready: false,
				missing_data: [ 'unknown' ],
				recommendations: [ __( 'Unable to check data availability. Please try again.', 'easycommerce-fakerpress' ) ],
				counts: {},
			};
		}
	};

	/**
	 * Check dependencies for a generator
	 * 
	 * @param {string} generatorType Generator type to check dependencies for
	 * @returns {Promise<Object>} Dependency status
	 */
	const checkDependencies = async ( generatorType ) => {
		const cacheKey = `dependencies_${generatorType}`;
		if ( validationCache.has( cacheKey ) ) {
			const cached = validationCache.get( cacheKey );
			if ( Date.now() - cached.timestamp < 300000 ) {
				return cached.data;
			}
		}

		try {
			const response = await apiFetch( {
				path: `/easycommerce-fakerpress/v1/validation/check-dependencies/${generatorType}`,
				method: 'GET',
			} );

			setValidationCache( prev => new Map( prev ).set( cacheKey, {
				data: response,
				timestamp: Date.now(),
			} ) );

			return response;
		} catch ( error ) {
			console.error( 'Dependency validation error:', error );
			return {
				ready: true,
				missing_dependencies: [],
				dependency_counts: {},
				recommendations: [],
			};
		}
	};

	/**
	 * Clear validation cache
	 */
	const clearValidationCache = () => {
		setValidationCache( new Map() );
	};

	return {
		checkDataAvailability,
		checkDependencies,
		clearValidationCache,
	};
}

/**
 * Data Validation Status Component
 * 
 * Shows validation status and recommendations for a generator
 */
export function DataValidationStatus( { generatorType, onValidationComplete } ) {
	const [ status, setStatus ] = useState( null );
	const [ dependencies, setDependencies ] = useState( null );
	const [ isChecking, setIsChecking ] = useState( true );
	const { checkDataAvailability, checkDependencies } = useDataValidation();

	useEffect( () => {
		const validateData = async () => {
			setIsChecking( true );
			
			try {
				const [ availabilityStatus, dependencyStatus ] = await Promise.all( [
					checkDataAvailability( generatorType ),
					checkDependencies( generatorType ),
				] );

				setStatus( availabilityStatus );
				setDependencies( dependencyStatus );
				
				// Notify parent component
				if ( onValidationComplete ) {
					onValidationComplete( {
						ready: availabilityStatus.ready && dependencyStatus.ready,
						availability: availabilityStatus,
						dependencies: dependencyStatus,
					} );
				}
			} catch ( error ) {
				console.error( 'Validation error:', error );
			} finally {
				setIsChecking( false );
			}
		};

		if ( generatorType ) {
			validateData();
		}
	}, [ generatorType ] );

	if ( isChecking ) {
		return (
			<div className="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
				<div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
				<span className="text-sm text-gray-600">
					{ __( 'Checking data availability...', 'easycommerce-fakerpress' ) }
				</span>
			</div>
		);
	}

	if ( ! status || ! dependencies ) {
		return null;
	}

	const allReady = status.ready && dependencies.ready;
	const allRecommendations = [ ...status.recommendations, ...dependencies.recommendations ];

	return (
		<div className={ `p-4 rounded-lg border ${allReady ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200'}` }>
			<div className="flex items-center mb-2">
				<div className={ `w-3 h-3 rounded-full mr-2 ${allReady ? 'bg-green-500' : 'bg-yellow-500'}` }></div>
				<h4 className="font-medium text-gray-900">
					{ allReady 
						? __( 'Ready to Generate', 'easycommerce-fakerpress' )
						: __( 'Setup Required', 'easycommerce-fakerpress' )
					}
				</h4>
			</div>

			{/* Data Counts */}
			{ Object.keys( status.counts ).length > 0 && (
				<div className="mb-3">
					<h5 className="text-sm font-medium text-gray-700 mb-1">
						{ __( 'Available Data:', 'easycommerce-fakerpress' ) }
					</h5>
					<div className="flex flex-wrap gap-2">
						{ Object.entries( status.counts ).map( ( [ key, count ] ) => (
							<span key={ key } className="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
								{ formatDataType( key ) }: { count }
							</span>
						) ) }
						{ Object.entries( dependencies.dependency_counts || {} ).map( ( [ key, count ] ) => (
							<span key={ key } className="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
								{ formatDataType( key ) }: { count }
							</span>
						) ) }
					</div>
				</div>
			) }

			{/* Missing Data */}
			{ status.missing_data && status.missing_data.length > 0 && (
				<div className="mb-3">
					<h5 className="text-sm font-medium text-red-700 mb-1">
						{ __( 'Missing Data:', 'easycommerce-fakerpress' ) }
					</h5>
					<div className="flex flex-wrap gap-2">
						{ status.missing_data.map( ( missing ) => (
							<span key={ missing } className="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
								{ formatDataType( missing ) }
							</span>
						) ) }
					</div>
				</div>
			) }

			{/* Missing Dependencies */}
			{ dependencies.missing_dependencies && dependencies.missing_dependencies.length > 0 && (
				<div className="mb-3">
					<h5 className="text-sm font-medium text-yellow-700 mb-1">
						{ __( 'Missing Dependencies:', 'easycommerce-fakerpress' ) }
					</h5>
					<div className="flex flex-wrap gap-2">
						{ dependencies.missing_dependencies.map( ( missing ) => (
							<span key={ missing } className="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
								{ formatDataType( missing ) }
							</span>
						) ) }
					</div>
				</div>
			) }

			{/* Recommendations */}
			{ allRecommendations.length > 0 && (
				<div>
					<h5 className="text-sm font-medium text-gray-700 mb-2">
						{ __( 'Recommendations:', 'easycommerce-fakerpress' ) }
					</h5>
					<ul className="text-sm text-gray-600 space-y-1">
						{ allRecommendations.map( ( recommendation, index ) => (
							<li key={ index } className="flex items-start">
								<span className="text-blue-500 mr-2">•</span>
								{ recommendation }
							</li>
						) ) }
					</ul>
				</div>
			) }
		</div>
	);
}

/**
 * Format data type for display
 * 
 * @param {string} type Data type
 * @returns {string} Formatted type
 */
function formatDataType( type ) {
	const formatMap = {
		customers: __( 'Customers', 'easycommerce-fakerpress' ),
		products: __( 'Products', 'easycommerce-fakerpress' ),
		orders: __( 'Orders', 'easycommerce-fakerpress' ),
		product_variation: __( 'Product Variations', 'easycommerce-fakerpress' ),
		location: __( 'Location Data', 'easycommerce-fakerpress' ),
		tax_class: __( 'Tax Classes', 'easycommerce-fakerpress' ),
		shipping_plan: __( 'Shipping Plans', 'easycommerce-fakerpress' ),
	};

	return formatMap[ type ] || type.replace( /_/g, ' ' ).replace( /\b\w/g, l => l.toUpperCase() );
}

/**
 * Quick Setup Component
 * 
 * Shows quick setup buttons for missing dependencies
 */
export function QuickSetup( { generatorType, missingDependencies } ) {
	if ( ! missingDependencies || missingDependencies.length === 0 ) {
		return null;
	}

	const getSetupOrder = ( dependencies ) => {
		const order = [ 'location', 'customer', 'product', 'product_variation', 'order' ];
		return dependencies.sort( ( a, b ) => order.indexOf( a ) - order.indexOf( b ) );
	};

	const orderedDependencies = getSetupOrder( missingDependencies );

	return (
		<div className="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
			<h4 className="font-medium text-blue-900 mb-2">
				{ __( 'Quick Setup', 'easycommerce-fakerpress' ) }
			</h4>
			<p className="text-sm text-blue-800 mb-3">
				{ __( 'Generate the required data first:', 'easycommerce-fakerpress' ) }
			</p>
			<div className="flex flex-wrap gap-2">
				{ orderedDependencies.map( ( dependency, index ) => (
					<button
						key={ dependency }
						className="inline-flex items-center px-3 py-1 rounded-md text-sm bg-blue-600 text-white hover:bg-blue-700"
						onClick={ () => {
							// Navigate to the appropriate generator
							const route = dependency === 'location' ? 'locations' : 
										 dependency === 'product_variation' ? 'product-variations' :
										 `${dependency}s`;
							window.location.hash = `#/generator/${route}`;
						} }
					>
						{ index + 1 }. { __( 'Generate', 'easycommerce-fakerpress' ) } { formatDataType( dependency ) }
					</button>
				) ) }
			</div>
		</div>
	);
}

export default DataValidationStatus;