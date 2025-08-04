import React, { useState } from 'react';

export default function GeneratorBase({ 
    title, 
    description, 
    type, 
    onGenerate, 
    isLoading, 
    result, 
    error 
}) {
    const [count, setCount] = useState(10);

    const handleSubmit = (e) => {
        e.preventDefault();
        onGenerate(count);
    };

    return (
        <div>
            <div className="mb-6">
                <h3 className="text-lg font-medium text-gray-900">{title}</h3>
                <p className="mt-1 text-sm text-gray-600">{description}</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
                <div className="ecfp-form-group">
                    <label htmlFor={`${type}-count`} className="ecfp-label">
                        Number of {type} to generate
                    </label>
                    <input
                        type="number"
                        id={`${type}-count`}
                        min="1"
                        max="100"
                        value={count}
                        onChange={(e) => setCount(parseInt(e.target.value))}
                        className="ecfp-input w-32"
                        disabled={isLoading}
                    />
                </div>

                <button
                    type="submit"
                    disabled={isLoading}
                    className="ecfp-button"
                >
                    {isLoading ? (
                        <>
                            <span className="ecfp-loading mr-2"></span>
                            Generating...
                        </>
                    ) : (
                        `Generate ${type}`
                    )}
                </button>
            </form>

            {error && (
                <div className="mt-4 ecfp-error">
                    <strong>Error:</strong> {error}
                </div>
            )}

            {result && (
                <div className="mt-4 ecfp-success">
                    <strong>Success!</strong> Generated {result.generated} {type}.
                    {result[type] && result[type].length > 0 && (
                        <div className="mt-2">
                            <h4 className="font-medium">Generated items:</h4>
                            <ul className="mt-1 text-sm">
                                {result[type].slice(0, 5).map((item, index) => (
                                    <li key={index} className="flex justify-between py-1">
                                        <span>{item.name || item.title || item.code}</span>
                                        <span className="text-gray-600">
                                            {item.email || item.price || item.total || item.amount}
                                        </span>
                                    </li>
                                ))}
                                {result[type].length > 5 && (
                                    <li className="text-gray-500 italic">
                                        ... and {result[type].length - 5} more
                                    </li>
                                )}
                            </ul>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}