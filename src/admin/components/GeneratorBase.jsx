import React, {useState} from 'react';
import {ChevronDownIcon, ChevronUpIcon} from '@heroicons/react/24/outline';
import {Button, Disclosure, DisclosureButton, DisclosurePanel, Field, Input, Label, Listbox, ListboxButton, ListboxOption, ListboxOptions, Switch} from '@headlessui/react';
import {RawHTML} from '@wordpress/element';
import {__, sprintf} from '@wordpress/i18n';

export default function GeneratorBase({title, description, type, onGenerate, isLoading, result, error, parameterConfig = {}, children}) {
    const [count, setCount] = useState(10);
    const [showAdvanced, setShowAdvanced] = useState(false);
    const [parameters, setParameters] = useState({});

    const handleSubmit = (e) => {
        e.preventDefault();
        const allParams = {count, ...parameters};
        onGenerate(allParams);
    };

    const handleParameterChange = (paramName, value) => {
        setParameters(prev => {
            if (paramName.includes('.')) {
                // Handle nested object properties
                const [objectName, propName] = paramName.split('.');
                return {
                    ...prev,
                    [objectName]: {
                        ...(prev[objectName] || {}),
                        [propName]: value
                    }
                };
            }
            return {
                ...prev,
                [paramName]: value
            };
        });
    };

    const getFieldLabel = (paramName, config) => {
        // Use config.title if available, otherwise use config.description, fallback to formatted paramName
        if (config.title) {
            return config.title;
        }
        if (config.description && !config.description.toLowerCase().includes('type') && !config.description.toLowerCase().includes('options')) {
            return config.description;
        }
        // For nested parameters, use only the property name part
        const displayName = paramName.includes('.') ? paramName.split('.')[1] : paramName;
        return displayName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    };

    const renderParameterField = (paramName, config) => {
        let value;

        if (paramName.includes('.')) {
            // Handle nested object properties
            const [objectName, propName] = paramName.split('.');
            value = parameters[objectName]?.[propName] ?? config.default;
        } else {
            value = parameters[paramName] ?? config.default;
        }

        switch (config.type) {
            case 'string':
                if (config.enum) {
                    return (
                        <Field className="relative">
                            <Label className="block text-sm font-medium text-gray-700">
                                {getFieldLabel(paramName, config)}
                            </Label>
                            <Listbox
                                value={value || ''}
                                onChange={(val) => handleParameterChange(paramName, val)}
                                disabled={isLoading}
                            >
                                <ListboxButton className="relative w-full mt-1 cursor-pointer rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left text-sm data-[focus]:ring-2 data-[focus]:ring-wp-admin-primary data-[disabled]:bg-gray-100 transition-colors">
                                    <span className={value ? 'text-gray-900' : 'text-gray-500'}>
                                        {value ? value.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : sprintf(__('Select %s', 'easycommerce-fakerpress'), config.description)}
                                    </span>
                                    <span className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                        <ChevronDownIcon className="h-5 w-5 text-gray-400" aria-hidden="true"/>
                                    </span>
                                </ListboxButton>
                                <ListboxOptions className="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg max-h-60 overflow-auto ring-1 ring-black ring-opacity-5">
                                    <ListboxOption value="" className="cursor-pointer select-none px-4 py-2 text-sm text-gray-500 data-[focus]:bg-blue-50 data-[focus]:text-wp-admin-highlight">
                                        {sprintf(__('Select %s', 'easycommerce-fakerpress'), config.description)}
                                    </ListboxOption>
                                    {config.enum.map(option => (
                                        <ListboxOption
                                            key={option}
                                            value={option}
                                            className="cursor-pointer select-none px-4 py-2 text-sm data-[focus]:bg-blue-50 data-[focus]:text-wp-admin-highlight"
                                        >
                                            {option.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </ListboxOption>
                                    ))}
                                </ListboxOptions>
                            </Listbox>
                        </Field>
                    );
                }
                return (
                    <Field className="relative">
                        <Label className="block text-sm font-medium text-gray-700">
                            {getFieldLabel(paramName, config)}
                        </Label>
                        <Input
                            value={value || ''}
                            className="mt-1 w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 placeholder-gray-400 data-[focus]:ring-2 data-[focus]:ring-wp-admin-primary data-[disabled]:bg-gray-100 transition-colors"
                            placeholder={config.description}
                            disabled={isLoading}
                            onChange={(e) => handleParameterChange(paramName, e.target.value)}
                        />
                    </Field>
                );

            case 'integer':
                return (
                    <Field className="relative">
                        <Label className="block text-sm font-medium text-gray-700">
                            {getFieldLabel(paramName, config)}
                        </Label>
                        <Input
                            type="number"
                            value={value || ''}
                            className="mt-1 w-32 rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 placeholder-gray-400 data-[focus]:ring-2 data-[focus]:ring-wp-admin-primary data-[disabled]:bg-gray-100 transition-colors"
                            min={config.minimum || 0}
                            max={config.maximum || 1000}
                            placeholder={config.description}
                            disabled={isLoading}
                            onChange={(e) => handleParameterChange(paramName, parseInt(e.target.value))}
                        />
                    </Field>
                );

            case 'number':
                return (
                    <Field className="relative">
                        <Label className="block text-sm font-medium text-gray-700">
                            {getFieldLabel(paramName, config)}
                        </Label>
                        <Input
                            type="number"
                            step="0.01"
                            value={value || ''}
                            className="mt-1 w-32 rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 placeholder-gray-400 data-[focus]:ring-2 data-[focus]:ring-wp-admin-primary data-[disabled]:bg-gray-100 transition-colors"
                            min={config.minimum || 0}
                            max={config.maximum || 10000}
                            placeholder={config.description}
                            disabled={isLoading}
                            onChange={(e) => handleParameterChange(paramName, parseFloat(e.target.value))}
                        />
                    </Field>
                );

            case 'boolean':
                return (
                    <Field className="flex items-center relative">
                        <Switch
                            checked={value || false}
                            onChange={(checked) => handleParameterChange(paramName, checked)}
                            className={`${value ? 'bg-wp-admin-primary' : 'bg-gray-200'} relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-wp-admin-primary focus:ring-offset-2 disabled:bg-gray-300`}
                            disabled={isLoading}
                        >
                            <span className={`${value ? 'translate-x-6' : 'translate-x-1'} inline-block h-4 w-4 transform rounded-full bg-white transition-transform`}/>
                        </Switch>
                        <Label className="ml-3 text-sm font-medium text-gray-700">
                            {config.title || config.description || getFieldLabel(paramName, config)}
                        </Label>
                    </Field>
                );

            case 'array':
                if (config.items && config.items.enum) {
                    const selectedValues = Array.isArray(value) ? value : (config.default || []);
                    return (
                        <Field className="space-y-2 relative">
                            <Label className="block text-sm font-medium text-gray-700">
                                {config.title || config.description || getFieldLabel(paramName, config)}
                            </Label>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {config.items.enum.map(option => (
                                    <Field key={option} className="flex items-center">
                                        <Switch
                                            checked={selectedValues.includes(option)}
                                            onChange={(checked) => {
                                                const newValues = checked ? [...selectedValues, option] : selectedValues.filter(v => v !== option);
                                                handleParameterChange(paramName, newValues);
                                            }}
                                            className={`${selectedValues.includes(option) ? 'bg-wp-admin-primary' : 'bg-gray-200'} relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-wp-admin-primary focus:ring-offset-2 disabled:bg-gray-300`}
                                            disabled={isLoading}
                                        >
                                            <span className={`${selectedValues.includes(option) ? 'translate-x-6' : 'translate-x-1'} inline-block h-4 w-4 transform rounded-full bg-white transition-transform`}/>
                                        </Switch>
                                        <Label className="ml-3 text-sm font-medium text-gray-700">
                                            {option.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </Label>
                                    </Field>
                                ))}
                            </div>
                        </Field>
                    );
                }
                return null;

            case 'object':
                return (
                    <Field className="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-4 relative">
                        {config.properties && Object.entries(config.properties).map(([propName, propConfig]) => (
                            <div key={propName} className="space-y-1 relative">
                                {renderParameterField(`${paramName}.${propName}`, propConfig)}
                            </div>
                        ))}
                    </Field>
                );

            default:
                return null;
        }
    };

    return (
        <div className="space-y-6">
            <div className="mb-8">
                <h3 className="text-xl font-semibold text-gray-900 tracking-tight">{title}</h3>
                <p className="mt-1 text-sm text-gray-500 leading-6">{description}</p>
            </div>

            <div className="space-y-6">
                {/* Basic Parameters */}
                <div className="rounded-lg bg-gray-50 p-6 shadow-sm">
                    <h4 className="text-sm font-semibold text-gray-900 mb-4">{__('Basic Settings', 'easycommerce-fakerpress')}</h4>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <Field className="space-y-1 relative">
                            <Label htmlFor={`${type}-count`} className="block text-sm font-medium text-gray-700">
                                {sprintf(__('Number of %s to generate', 'easycommerce-fakerpress'), type)}
                            </Label>
                            <Input
                                type="number"
                                id={`${type}-count`}
                                value={count}
                                min="1"
                                max="100"
                                className="w-32 rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 placeholder-gray-400 data-[focus]:ring-2 data-[focus]:ring-wp-admin-primary data-[disabled]:bg-gray-100 transition-colors"
                                disabled={isLoading}
                                onChange={(e) => setCount(parseInt(e.target.value))}
                            />
                        </Field>

                        <Field className="space-y-1 relative">
                            <Label className="block text-sm font-medium text-gray-700">{__('Locale', 'easycommerce-fakerpress')}</Label>
                            <Listbox
                                value={parameters.locale || 'en_US'}
                                onChange={(val) => handleParameterChange('locale', val)}
                                disabled={isLoading}
                            >
                                <ListboxButton className="relative w-full mt-1 cursor-pointer rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left text-sm data-[focus]:ring-2 data-[focus]:ring-wp-admin-primary data-[disabled]:bg-gray-100 transition-colors">
                                    <span className={parameters.locale ? 'text-gray-900' : 'text-gray-500'}>
                                        {parameters.locale ? parameters.locale.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : __('Select Locale', 'easycommerce-fakerpress')}
                                    </span>
                                    <span className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                        <ChevronDownIcon className="h-5 w-5 text-gray-400" aria-hidden="true"/>
                                    </span>
                                </ListboxButton>
                                <ListboxOptions className="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg max-h-60 overflow-auto ring-1 ring-black ring-opacity-5">
                                    {['en_US', 'en_GB', 'fr_FR', 'de_DE', 'es_ES', 'it_IT'].map(locale => (
                                        <ListboxOption
                                            key={locale}
                                            value={locale}
                                            className="cursor-pointer select-none px-4 py-2 text-sm data-[focus]:bg-blue-50 data-[focus]:text-wp-admin-highlight"
                                        >
                                            {locale === 'en_US' && __('English (US)', 'easycommerce-fakerpress')}
                                            {locale === 'en_GB' && __('English (UK)', 'easycommerce-fakerpress')}
                                            {locale === 'fr_FR' && __('French', 'easycommerce-fakerpress')}
                                            {locale === 'de_DE' && __('German', 'easycommerce-fakerpress')}
                                            {locale === 'es_ES' && __('Spanish', 'easycommerce-fakerpress')}
                                            {locale === 'it_IT' && __('Italian', 'easycommerce-fakerpress')}
                                        </ListboxOption>
                                    ))}
                                </ListboxOptions>
                            </Listbox>
                        </Field>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                        <Field className="space-y-1 relative">
                            <Label className="block text-sm font-medium text-gray-700">{__('Random Seed (optional)', 'easycommerce-fakerpress')}</Label>
                            <Input
                                type="number"
                                value={parameters.seed || ''}
                                className="w-32 rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 placeholder-gray-400 data-[focus]:ring-2 data-[focus]:ring-wp-admin-primary data-[disabled]:bg-gray-100 transition-colors"
                                placeholder={__('For reproducible data', 'easycommerce-fakerpress')}
                                disabled={isLoading}
                                onChange={(e) => handleParameterChange('seed', parseInt(e.target.value))}
                            />
                        </Field>

                        <Field className="flex items-center relative">
                            <Switch
                                checked={parameters.include_meta !== false}
                                onChange={(checked) => handleParameterChange('include_meta', checked)}
                                className={`${parameters.include_meta !== false ? 'bg-wp-admin-primary' : 'bg-gray-200'} relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-wp-admin-primary focus:ring-offset-2 disabled:bg-gray-300`}
                                disabled={isLoading}
                            >
                                <span className={`${parameters.include_meta !== false ? 'translate-x-6' : 'translate-x-1'} inline-block h-4 w-4 transform rounded-full bg-white transition-transform`}/>
                            </Switch>
                            <Label className="ml-3 text-sm font-medium text-gray-700">{__('Include additional metadata', 'easycommerce-fakerpress')}</Label>
                        </Field>
                    </div>
                </div>

                {/* Advanced Parameters Toggle */}
                {Object.keys(parameterConfig).length > 0 && (
                    <Disclosure>
                        <DisclosureButton
                            className="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 data-[focus]:ring-2 data-[focus]:ring-wp-admin-primary transition-colors"
                            onClick={() => setShowAdvanced(!showAdvanced)}
                        >
                            <span>{__('Advanced Parameters', 'easycommerce-fakerpress')}</span>
                            {showAdvanced ? (
                                <ChevronUpIcon className="h-5 w-5 text-gray-500" aria-hidden="true"/>
                            ) : (
                                <ChevronDownIcon className="h-5 w-5 text-gray-500" aria-hidden="true"/>
                            )}
                        </DisclosureButton>
                        <DisclosurePanel className="mt-4 rounded-lg border border-gray-200 bg-white p-6 space-y-4 shadow-sm">
                            {Object.entries(parameterConfig).map(([paramName, config]) => (
                                <div key={paramName} className="space-y-1 relative">
                                    {renderParameterField(paramName, config)}
                                </div>
                            ))}
                        </DisclosurePanel>
                    </Disclosure>
                )}

                {/* Custom Children Content */}
                {children}

                <Button
                    disabled={isLoading}
                    onClick={handleSubmit}
                    className="inline-flex items-center px-4 py-2 rounded-md bg-wp-admin-primary text-white font-medium text-sm hover:bg-wp-admin-secondary data-[focus]:ring-2 data-[focus]:ring-wp-admin-primary data-[focus]:ring-offset-2 data-[disabled]:bg-wp-admin-accent data-[disabled]:cursor-not-allowed transition-colors"
                >
                    {isLoading ? (
                        <>
                            <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {__('Generating...', 'easycommerce-fakerpress')}
                        </>
                    ) : (
                        sprintf(__('Generate %s', 'easycommerce-fakerpress'), type)
                    )}
                </Button>
            </div>

            {error && (
                <div className="mt-4 rounded-md bg-red-50 p-4">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd"/>
                            </svg>
                        </div>
                        <div className="ml-3">
                            <h3 className="text-sm font-medium text-red-800">{__('Error', 'easycommerce-fakerpress')}</h3>
                            <RawHTML className="mt-2 text-sm text-red-700 [&_a]:text-red-900 [&_a]:font-bold [&_a]:underline [&_a:hover]:text-red-800">{error}</RawHTML>
                        </div>
                    </div>
                </div>
            )}

            {result && (
                <div className="mt-4 rounded-md bg-green-50 p-4">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd"/>
                            </svg>
                        </div>
                        <div className="ml-3">
                            <h3 className="text-sm font-medium text-green-800">{__('Success!', 'easycommerce-fakerpress')}</h3>
                            <div className="mt-2 text-sm text-green-700">
                                {sprintf(__('Generated %1$d %2$s.', 'easycommerce-fakerpress'), result.generated, type)}
                            </div>
                            {result.data && result.data[type] && result.data[type].length > 0 && (
                                <div className="mt-4">
                                    <h4 className="text-sm font-medium text-gray-900">{__('Generated items:', 'easycommerce-fakerpress')}</h4>
                                    <ul className="mt-2 text-sm text-gray-600 divide-y divide-gray-200">
                                        {result.data[type].slice(0, 5).map((item, index) => (
                                            <li key={index} className="flex justify-between py-2">
                                                <span>{item.name || item.title || item.code || item.id}</span>
                                                <span className="text-gray-500">
                                                    {item.email || item.price || item.total || item.amount || item.status}
                                                </span>
                                            </li>
                                        ))}
                                        {result.data[type].length > 5 && (
                                            <li className="py-2 text-gray-500 italic">
                                                {sprintf(__('... and %d more', 'easycommerce-fakerpress'), result.data[type].length - 5)}
                                            </li>
                                        )}
                                    </ul>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
