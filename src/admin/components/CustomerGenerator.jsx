import React, { useState } from 'react';
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
            const formData = new FormData();
            formData.append('action', 'ecfp_generate_data');
            formData.append('type', 'customers');
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
                setError(data.data || 'An error occurred while generating customers.');
            }
        } catch (err) {
            setError('Network error occurred. Please try again.');
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