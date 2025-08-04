import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import GeneratorBase from './GeneratorBase';

export default function ProductGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (count) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/products/generate',
                method: 'POST',
                data: {
                    count: count
                }
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating products.');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <GeneratorBase
            title="Generate Products"
            description="Create fake products with random names, descriptions, prices, and attributes."
            type="products"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
        />
    );
}
