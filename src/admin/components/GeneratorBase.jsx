import React, { useState } from 'react';
import { ChevronDownIcon, ChevronUpIcon } from '@heroicons/react/24/outline';

export default function GeneratorBase({
    title,
    description,
    type,
    onGenerate,
    isLoading,
    result,
    error,
    parameterConfig = {},
    children
}) {
    const [count, setCount] = useState(10);
    const [showAdvanced, setShowAdvanced] = useState(false);
    const [parameters, setParameters] = useState({});

    const handleSubmit = (e) => {
        e.preventDefault();
        const allParams = { count, ...parameters };
        onGenerate(allParams);
    };

    const handleParameterChange = (paramName, value) => {
        setParameters(prev => ({
            ...prev,
            [paramName]: value
        }));
    };

    const renderParameterField = (paramName, config) => {
        const value = parameters[paramName] || config.default;

        switch (config.type) {
            case 'string':
                if (config.enum) {
                    return (
                        <select
                            value={value || ''}
                            onChange={(e) => handleParameterChange(paramName, e.target.value)}
                            className="ecfp-input"
                            disabled={isLoading}
                        >
                            <option value="">Select {config.description}</option>
                            {config.enum.map(option => (
                                <option key={option} value={option}>
                                    {option.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </option>
                            ))}
                        </select>
                    );
                }
                return (
                    <input
                        type="text"
                        value={value || ''}
                        onChange={(e) => handleParameterChange(paramName, e.target.value)}
                        className="ecfp-input"
                        placeholder={config.description}
                        disabled={isLoading}
                    />
                );

            case 'integer':
                return (
                    <input
                        type="number"
                        value={value || ''}
                        onChange={(e) => handleParameterChange(paramName, parseInt(e.target.value))}
                        min={config.minimum || 0}
                        max={config.maximum || 1000}
                        className="ecfp-input w-32"
                        placeholder={config.description}
                        disabled={isLoading}
                    />
                );

            case 'number':
                return (
                    <input
                        type="number"
                        step="0.01"
                        value={value || ''}
                        onChange={(e) => handleParameterChange(paramName, parseFloat(e.target.value))}
                        min={config.minimum || 0}
                        max={config.maximum || 10000}
                        className="ecfp-input w-32"
                        placeholder={config.description}
                        disabled={isLoading}
                    />
                );

            case 'boolean':
                return (
                    <label className="inline-flex items-center">
                        <input
                            type="checkbox"
                            checked={value || false}
                            onChange={(e) => handleParameterChange(paramName, e.target.checked)}
                            className="form-checkbox h-4 w-4 text-wp-blue"
                            disabled={isLoading}
                        />
                        <span className="ml-2 text-sm text-gray-700">{config.description}</span>
                    </label>
                );

            case 'array':
                if (config.items && config.items.enum) {
                    const selectedValues = Array.isArray(value) ? value : (config.default || []);
                    return (
                        <div className="space-y-2">
                            <p className="text-sm text-gray-600">{config.description}</p>
                            <div className="grid grid-cols-2 gap-2">
                                {config.items.enum.map(option => (
                                    <label key={option} className="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={selectedValues.includes(option)}
                                            onChange={(e) => {
                                                const newValues = e.target.checked
                                                    ? [...selectedValues, option]
                                                    : selectedValues.filter(v => v !== option);
                                                handleParameterChange(paramName, newValues);
                                            }}
                                            className="form-checkbox h-4 w-4 text-wp-blue"
                                            disabled={isLoading}
                                        />
                                        <span className="ml-2 text-sm text-gray-700">
                                            {option.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </span>
                                    </label>
                                ))}
                            </div>
                        </div>
                    );
                }
                return null;

            case 'object':
                return (
                    <div className="border border-gray-200 rounded-lg p-4 space-y-3">
                        <h4 className="font-medium text-gray-900">{config.description}</h4>
                        {config.properties && Object.entries(config.properties).map(([propName, propConfig]) => (
                            <div key={propName} className="ecfp-form-group">
                                <label className="ecfp-label">
                                    {propName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </label>
                                {renderParameterField(`${paramName}.${propName}`, propConfig)}
                            </div>
                        ))}
                    </div>
                );

            default:
                return null;
        }
    };

    return (
        <div>
            <div className="mb-6">
                <h3 className="text-lg font-medium text-gray-900">{title}</h3>
                <p className="mt-1 text-sm text-gray-600">{description}</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Basic Parameters */}
                <div className="bg-gray-50 p-4 rounded-lg">
                    <h4 className="font-medium text-gray-900 mb-4">Basic Settings</h4>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                        <div className="ecfp-form-group">
                            <label className="ecfp-label">Locale</label>
                            <select
                                value={parameters.locale || 'en_US'}
                                onChange={(e) => handleParameterChange('locale', e.target.value)}
                                className="ecfp-input"
                                disabled={isLoading}
                            >
                                <option value="en_US">English (US)</option>
                                <option value="en_GB">English (UK)</option>
                                <option value="fr_FR">French</option>
                                <option value="de_DE">German</option>
                                <option value="es_ES">Spanish</option>
                                <option value="it_IT">Italian</option>
                            </select>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div className="ecfp-form-group">
                            <label className="ecfp-label">Random Seed (optional)</label>
                            <input
                                type="number"
                                value={parameters.seed || ''}
                                onChange={(e) => handleParameterChange('seed', parseInt(e.target.value))}
                                className="ecfp-input w-32"
                                placeholder="For reproducible data"
                                disabled={isLoading}
                            />
                        </div>

                        <div className="ecfp-form-group">
                            <label className="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    checked={parameters.include_meta !== false}
                                    onChange={(e) => handleParameterChange('include_meta', e.target.checked)}
                                    className="form-checkbox h-4 w-4 text-wp-blue"
                                    disabled={isLoading}
                                />
                                <span className="ml-2 text-sm text-gray-700">Include additional metadata</span>
                            </label>
                        </div>
                    </div>
                </div>

                {/* Advanced Parameters Toggle */}
                {Object.keys(parameterConfig).length > 0 && (
                    <div>
                        <button
                            type="button"
                            onClick={() => setShowAdvanced(!showAdvanced)}
                            className="flex items-center justify-between w-full px-4 py-2 text-left text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-wp-blue"
                        >
                            <span>Advanced Parameters</span>
                            {showAdvanced ? (
                                <ChevronUpIcon className="w-4 h-4" />
                            ) : (
                                <ChevronDownIcon className="w-4 h-4" />
                            )}
                        </button>

                        {showAdvanced && (
                            <div className="mt-4 bg-white border border-gray-200 rounded-lg p-4 space-y-4">
                                {Object.entries(parameterConfig).map(([paramName, config]) => (
                                    <div key={paramName} className="ecfp-form-group">
                                        <label className="ecfp-label">
                                            {paramName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </label>
                                        {renderParameterField(paramName, config)}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* Custom Children Content */}
                {children}

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
                    {result.data && result.data[type] && result.data[type].length > 0 && (
                        <div className="mt-2">
                            <h4 className="font-medium">Generated items:</h4>
                            <ul className="mt-1 text-sm">
                                {result.data[type].slice(0, 5).map((item, index) => (
                                    <li key={index} className="flex justify-between py-1">
                                        <span>{item.name || item.title || item.code || item.id}</span>
                                        <span className="text-gray-600">
                                            {item.email || item.price || item.total || item.amount || item.status}
                                        </span>
                                    </li>
                                ))}
                                {result.data[type].length > 5 && (
                                    <li className="text-gray-500 italic">
                                        ... and {result.data[type].length - 5} more
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
