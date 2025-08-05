import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import EnhancedGeneratorBase from './EnhancedGeneratorBase';

export default function LocationGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (params) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/locations/generate',
                method: 'POST',
                data: params
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating location data.');
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        coverage_scope: {
            description: 'Geographic coverage scope',
            type: 'string',
            enum: ['minimal', 'regional', 'national', 'international'],
            default: 'international'
        },
        data_completeness: {
            description: 'Location data completeness level',
            type: 'string',
            enum: ['basic', 'standard', 'comprehensive'],
            default: 'standard'
        },
        include_coordinates: {
            description: 'Include latitude/longitude coordinates',
            type: 'boolean',
            default: true
        },
        include_timezones: {
            description: 'Include timezone information',
            type: 'boolean',
            default: true
        },
        include_currencies: {
            description: 'Include currency information',
            type: 'boolean',
            default: true
        }
    };

    return (
        <div>
            <EnhancedGeneratorBase
                title="Generate Location Data"
                description="Create comprehensive location hierarchy data (countries, states, cities) for the EasyCommerce system. This populates the locations.json file used throughout the system."
                type="locations"
                onGenerate={handleGenerate}
                isLoading={isLoading}
                result={result}
                error={error}
                parameterConfig={parameterConfig}
            />
            
            <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 className="font-medium text-blue-900 mb-2">Important Note</h4>
                <p className="text-sm text-blue-800">
                    The Location Generator creates the foundational geographic data used by other generators. 
                    It's recommended to run this first, especially before generating customers and orders that 
                    rely on realistic address data. The generated data is saved to the EasyCommerce locations.json file.
                </p>
            </div>
        </div>
    );
}