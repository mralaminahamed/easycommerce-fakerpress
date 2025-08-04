import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import GeneratorBase from './GeneratorBase';

export default function CouponGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (count) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/coupons/generate',
                method: 'POST',
                data: {
                    count: count
                }
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating coupons.');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <GeneratorBase
            title="Generate Coupons"
            description="Create fake discount coupons with random codes, amounts, and expiration dates."
            type="coupons"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
        />
    );
}
