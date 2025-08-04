import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import GeneratorBase from './GeneratorBase';

export default function CustomerGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (count) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/customers/generate',
                method: 'POST',
                data: {
                    count: count
                }
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating customers.');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <GeneratorBase
            title="Generate Customers"
            description="Create fake customer accounts with random names, addresses, and contact information."
            type="customers"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
        />
    );
}
