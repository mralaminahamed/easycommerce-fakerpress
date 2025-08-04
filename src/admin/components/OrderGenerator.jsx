import React, { useState } from 'react';
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
            const formData = new FormData();
            formData.append('action', 'ecfp_generate_data');
            formData.append('type', 'orders');
            formData.append('count', count);
            formData.append('nonce', window.ecfpAjax.nonce);

            const response = await fetch(window.ecfpAjax.url, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                setResult(data.data);
            } else {
                setError(data.data || 'An error occurred while generating orders.');
            }
        } catch (err) {
            setError('Network error occurred. Please try again.');
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