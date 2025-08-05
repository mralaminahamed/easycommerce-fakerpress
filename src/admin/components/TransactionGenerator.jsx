import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import EnhancedGeneratorBase from './EnhancedGeneratorBase';

export default function TransactionGenerator() {
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleGenerate = async (params) => {
        setIsLoading(true);
        setError(null);
        setResult(null);

        try {
            const data = await apiFetch({
                path: '/easycommerce-fakerpress/v1/transactions/generate',
                method: 'POST',
                data: params
            });

            setResult(data);
        } catch (err) {
            setError(err.message || 'An error occurred while generating transactions.');
        } finally {
            setIsLoading(false);
        }
    };

    const parameterConfig = {
        transaction_types: {
            description: 'Types of transactions to generate',
            type: 'array',
            items: {
                type: 'string',
                enum: ['payment', 'refund', 'adjustment', 'fee', 'commission']
            },
            default: ['payment', 'refund', 'adjustment']
        },
        payment_gateways: {
            description: 'Payment gateways to simulate',
            type: 'array',
            items: {
                type: 'string',
                enum: ['stripe', 'paypal', 'square', 'authorize_net', 'braintree']
            },
            default: ['stripe', 'paypal', 'square']
        },
        amount_range: {
            description: 'Transaction amount range',
            type: 'object',
            properties: {
                min: { type: 'number', minimum: 0, default: 1 },
                max: { type: 'number', minimum: 1, default: 1000 }
            }
        },
        status_distribution: {
            description: 'Transaction status distribution',
            type: 'object',
            properties: {
                success_rate: { type: 'integer', minimum: 0, maximum: 100, default: 85 },
                pending_rate: { type: 'integer', minimum: 0, maximum: 100, default: 10 },
                failed_rate: { type: 'integer', minimum: 0, maximum: 100, default: 5 }
            }
        }
    };

    return (
        <EnhancedGeneratorBase
            title="Generate Transactions"
            description="Create realistic payment transaction history with different gateways, amounts, and status distributions."
            type="transactions"
            onGenerate={handleGenerate}
            isLoading={isLoading}
            result={result}
            error={error}
            parameterConfig={parameterConfig}
        />
    );
}