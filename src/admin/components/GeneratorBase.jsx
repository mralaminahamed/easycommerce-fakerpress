import React, { useState } from 'react';
import { ChevronDownIcon, ChevronUpIcon } from '@heroicons/react/24/outline';
import { Listbox, Switch, Disclosure, Button, Combobox } from '@headlessui/react';

export default function GeneratorBase({ title, description, type, onGenerate, isLoading, result, error, parameterConfig = {}, children }) {
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
                        <Listbox
                            value={value || ''}
                            onChange={(val) => handleParameterChange(paramName, val)}
                            disabled={isLoading}
                        >
                            <div className="relative">
                                <Listbox.Button className="ecfp-input">
                                    {value ? value.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : `Select ${config.description}`}
                                </Listbox.Button>
                                <Listbox.Options className="absolute mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                                    <Listbox.Option value="" className="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Select {config.description}
                                    </Listbox.Option>
                                    {config.enum.map(option => (
                                        <Listbox.Option
                                            key={option}
                                            value={option}
                                            className={({ active }) => `px-4 py-2 text-sm text-gray-700 ${active ? 'bg-gray-100' : ''}`}
                                        >
                                            {option.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </Listbox.Option>
                                    ))}
                                </Listbox.Options>
                            </div>
                        </Listbox>
                    );
                }
                return (
                    <Combobox
                        value={value || ''}
                        onChange={(val) => handleParameterChange(paramName, val)}
                        disabled={isLoading}
                    >
                        <div className="relative">
                            <Combobox.Input
                                className="ecfp-input"
                                placeholder={config.description}
                                onChange={(e) => handleParameterChange(paramName, e.target.value)}
                            />
                        </div>
                    </Combobox>
                );

            case 'integer':
                return (
                    <Combobox
                        value={value || ''}
                        onChange={(val) => handleParameterChange(paramName, parseInt(val))}
                        disabled={isLoading}
                    >
                        <div className="relative">
                            <Combobox.Input
                                className="ecfp-input w-32"
                                type="number"
                                min={config.minimum || 0}
                                max={config.maximum || 1000}
                                placeholder={config.description}
                                onChange={(e) => handleParameterChange(paramName, parseInt(e.target.value))}
                            />
                        </div>
                    </Combobox>
                );

            case 'number':
                return (
                    <Combobox
                        value={value || ''}
                        onChange={(val) => handleParameterChange(paramName, parseFloat(val))}
                        disabled={isLoading}
                    >
                        <div className="relative">
                            <Combobox.Input
                                className="ecfp-input w-32"
                                type="number"
                                step="0.01"
                                min={config.minimum || 0}
                                max={config.maximum || 10000}
                                placeholder={config.description}
                                onChange={(e) => handleParameterChange(paramName, parseFloat(e.target.value))}
                            />
                        </div>
                    </Combobox>
                );

            case 'boolean':
                return (
                    <Switch.Group as="div" className="flex items-center">
                        <Switch
                            checked={value || false}
                            onChange={(checked) => handleParameterChange(paramName, checked)}
                            className={`relative inline-flex h-6 w-11 items-center rounded-full ${value ? 'bg-wp-blue' : 'bg-gray-200'}`}
                            disabled={isLoading}
                        >
                            <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition ${value ? 'translate-x-6' : 'translate-x-1'}`} />
                        </Switch>
                        <Switch.Label className="ml-2 text-sm text-gray-700">{config.description}</Switch.Label>
                    </Switch.Group>
                );

            case 'array':
                if (config.items && config.items.enum) {
                    const selectedValues = Array.isArray(value) ? value : (config.default || []);
                    return (
                        <div className="space-y-2">
                            <p className="text-sm text-gray-600">{config.description}</p>
                            <div className="grid grid-cols-2 gap-2">
                                {config.items.enum.map(option => (
                                    <Switch.Group key={option} as="div" className="flex items-center">
                                        <Switch
                                            checked={selectedValues.includes(option)}
                                            onChange={(checked) => {
                                                const newValues = checked
                                                    ? [...selectedValues, option]
                                                    : selectedValues.filter(v => v !== option);
                                                handleParameterChange(paramName, newValues);
                                            }}
                                            className={`relative inline-flex h-6 w-11 items-center rounded-full ${selectedValues.includes(option) ? 'bg-wp-blue' : 'bg-gray-200'}`}
                                            disabled={isLoading}
                                        >
                                            <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition ${selectedValues.includes(option) ? 'translate-x-6' : 'translate-x-1'}`} />
                                        </Switch>
                                        <Switch.Label className="ml-2 text-sm text-gray-700">
                                            {option.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </Switch.Label>
                                    </Switch.Group>
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

            <div className="space-y-6">
                {/* Basic Parameters */}
                <div className="bg-gray-50 p-4 rounded-lg">
                    <h4 className="font-medium text-gray-900 mb-4">Basic Settings</h4>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="ecfp-form-group">
                            <label htmlFor={`${type}-count`} className="ecfp-label">
                                Number of {type} to generate
                            </label>
                            <Combobox
                                value={count}
                                onChange={(val) => setCount(parseInt(val))}
                                disabled={isLoading}
                            >
                                <div className="relative">
                                    <Combobox.Input
                                        type="number"
                                        id={`${type}-count`}
                                        min="1"
                                        max="100"
                                        className="ecfp-input w-32"
                                        onChange={(e) => setCount(parseInt(e.target.value))}
                                    />
                                </div>
                            </Combobox>
                        </div>

                        <div className="ecfp-form-group">
                            <label className="ecfp-label">Locale</label>
                            <Listbox
                                value={parameters.locale || 'en_US'}
                                onChange={(val) => handleParameterChange('locale', val)}
                                disabled={isLoading}
                            >
                                <div className="relative">
                                    <Listbox.Button className="ecfp-input">
                                        {parameters.locale ? parameters.locale.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Select Locale'}
                                    </Listbox.Button>
                                    <Listbox.Options className="absolute mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                                        {['en_US', 'en_GB', 'fr_FR', 'de_DE', 'es_ES', 'it_IT'].map(locale => (
                                            <Listbox.Option
                                                key={locale}
                                                value={locale}
                                                className={({ active }) => `px-4 py-2 text-sm text-gray-700 ${active ? 'bg-gray-100' : ''}`}
                                            >
                                                {locale === 'en_US' && 'English (US)'}
                                                {locale === 'en_GB' && 'English (UK)'}
                                                {locale === 'fr_FR' && 'French'}
                                                {locale === 'de_DE' && 'German'}
                                                {locale === 'es_ES' && 'Spanish'}
                                                {locale === 'it_IT' && 'Italian'}
                                            </Listbox.Option>
                                        ))}
                                    </Listbox.Options>
                                </div>
                            </Listbox>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div className="ecfp-form-group">
                            <label className="ecfp-label">Random Seed (optional)</label>
                            <Combobox
                                value={parameters.seed || ''}
                                onChange={(val) => handleParameterChange('seed', parseInt(val))}
                                disabled={isLoading}
                            >
                                <div className="relative">
                                    <Combobox.Input
                                        type="number"
                                        className="ecfp-input w-32"
                                        placeholder="For reproducible data"
                                        onChange={(e) => handleParameterChange('seed', parseInt(e.target.value))}
                                    />
                                </div>
                            </Combobox>
                        </div>

                        <div className="ecfp-form-group">
                            <Switch.Group as="div" className="flex items-center">
                                <Switch
                                    checked={parameters.include_meta !== false}
                                    onChange={(checked) => handleParameterChange('include_meta', checked)}
                                    className={`relative inline-flex h-6 w-11 items-center rounded-full ${parameters.include_meta !== false ? 'bg-wp-blue' : 'bg-gray-200'}`}
                                    disabled={isLoading}
                                >
                                    <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition ${parameters.include_meta !== false ? 'translate-x-6' : 'translate-x-1'}`} />
                                </Switch>
                                <Switch.Label className="ml-2 text-sm text-gray-700">Include additional metadata</Switch.Label>
                            </Switch.Group>
                        </div>
                    </div>
                </div>

                {/* Advanced Parameters Toggle */}
                {Object.keys(parameterConfig).length > 0 && (
                    <Disclosure>
                        {({ open }) => (
                            <>
                                <Disclosure.Button
                                    className="flex items-center justify-between w-full px-4 py-2 text-left text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-wp-blue"
                                    onClick={() => setShowAdvanced(!showAdvanced)}
                                >
                                    <span>Advanced Parameters</span>
                                    {open ? (
                                        <ChevronUpIcon className="w-4 h-4" />
                                    ) : (
                                        <ChevronDownIcon className="w-4 h-4" />
                                    )}
                                </Disclosure.Button>
                                <Disclosure.Panel className="mt-4 bg-white border border-gray-200 rounded-lg p-4 space-y-4">
                                    {Object.entries(parameterConfig).map(([paramName, config]) => (
                                        <div key={paramName} className="ecfp-form-group">
                                            <label className="ecfp-label">
                                                {paramName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                            </label>
                                            {renderParameterField(paramName, config)}
                                        </div>
                                    ))}
                                </Disclosure.Panel>
                            </>
                        )}
                    </Disclosure>
                )}

                {/* Custom Children Content */}
                {children}

                <Button
                    type="submit"
                    disabled={isLoading}
                    onClick={handleSubmit}
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
                </Button>
            </div>

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
