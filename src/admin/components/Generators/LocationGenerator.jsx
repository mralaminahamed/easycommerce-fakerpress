import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import GeneratorBase from '../GeneratorBase';

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
            setError(err.message || __('An error occurred while generating location data.', 'easycommerce-fakerpress'));
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        coverage_scope: {
            description: __('Geographic coverage scope', 'easycommerce-fakerpress'),
            type: 'string',
            enum: ['minimal', 'regional', 'national', 'international'],
            default: 'international'
        },
        data_completeness: {
            description: __('Location data completeness level', 'easycommerce-fakerpress'),
            type: 'string',
            enum: ['basic', 'standard', 'comprehensive'],
            default: 'standard'
        },
        include_coordinates: {
            description: __('Include latitude/longitude coordinates', 'easycommerce-fakerpress'),
            type: 'boolean',
            default: true
        },
        include_timezones: {
            description: __('Include timezone information', 'easycommerce-fakerpress'),
            type: 'boolean',
            default: true
        },
        include_currencies: {
            description: __('Include currency information', 'easycommerce-fakerpress'),
            type: 'boolean',
            default: true
        }
    };

    return (
        <div>
            <GeneratorBase
                title={__('Generate Location Data', 'easycommerce-fakerpress')}
                description={__('Create comprehensive location hierarchy data (countries, states, cities) for the EasyCommerce system. This populates the locations.json file used throughout the system.', 'easycommerce-fakerpress')}
                type="locations"
                onGenerate={handleGenerate}
                isLoading={isLoading}
                result={result}
                error={error}
                parameterConfig={parameterConfig}
            />

            <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 className="font-medium text-blue-900 mb-2">{__('Important Note', 'easycommerce-fakerpress')}</h4>
                <p className="text-sm text-blue-800">
                    {__('The Location Generator creates the foundational geographic data used by other generators. It\'s recommended to run this first, especially before generating customers and orders that rely on realistic address data. The generated data is saved to the EasyCommerce locations.json file.', 'easycommerce-fakerpress')}
                </p>
            </div>
        </div>
    );
}
