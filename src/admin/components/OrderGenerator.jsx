import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import GeneratorBase from './GeneratorBase';

export default function OrderGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (count) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/orders/generate',
                method: 'POST',
                data: {
                    count: count
                }
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating orders.');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <GeneratorBase
            title="Generate Orders"
            description="Create fake orders with random customers, products, and order details. Requires existing customers and products."
            type="orders"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
        />
    );
}
