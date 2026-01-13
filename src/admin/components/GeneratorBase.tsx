import { ChevronDown } from "lucide-react";
import { motion, Variants } from "framer-motion";
import { Button } from "./ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "./ui/card";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "./ui/select";
import { Switch } from "./ui/switch";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "./ui/collapsible";

import { useState, RawHTML } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";

// TypeScript interfaces
interface ParameterConfig {
  type: string;
  title?: string;
  description?: string;
  default?: any;
  enum?: string[];
  minimum?: number;
  maximum?: number;
  items?: {
    type?: string;
    enum?: string[];
  };
  properties?: Record<string, ParameterConfig>;
  dependsOn?: Record<string, any>;
}

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

interface GeneratorBaseProps {
  title: string;
  description: string;
  type: string;
  onGenerate: (params: Record<string, any>) => void;
  isLoading: boolean;
  result?: GeneratorResult | null;
  error?: string | null;
  parameterConfig?: Record<string, ParameterConfig>;
  children?: React.ReactNode;
}

// Extend window object for WordPress API
declare global {
  interface Window {
    easycommerceFakerpressApi?: {
      locale?: {
        faker?: string;
        label?: string;
        wordpress?: string;
        allLocales?: Record<string, string>;
      };
    };
  }
}

export default function GeneratorBase({
  title,
  description,
  type,
  onGenerate,
  isLoading,
  result,
  error,
  parameterConfig = {},
  children,
}: GeneratorBaseProps) {
  const [count, setCount] = useState<number>(10);
  const [parameters, setParameters] = useState<Record<string, any>>({});

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const allParams = { count, ...parameters };
    onGenerate(allParams);
  };

  const handleParameterChange = (paramName: string, value: any) => {
    setParameters((prev) => {
      if (paramName.includes(".")) {
        // Handle nested object properties
        const [objectName, propName] = paramName.split(".");
        return {
          ...prev,
          [objectName]: {
            ...(prev[objectName] || {}),
            [propName]: value,
          },
        };
      }
      return {
        ...prev,
        [paramName]: value,
      };
    });
  };

  const getFieldLabel = (
    paramName: string,
    config: ParameterConfig,
  ): string => {
    // Use config.title if available, otherwise use config.description, fallback to formatted paramName
    if (config.title) {
      return config.title;
    }
    if (
      config.description &&
      !config.description.toLowerCase().includes("type") &&
      !config.description.toLowerCase().includes("options")
    ) {
      return config.description;
    }
    // For nested parameters, use only the property name part
    const displayName = paramName.includes(".")
      ? paramName.split(".")[1]
      : paramName;
    return displayName
      .replace(/_/g, " ")
      .replace(/\b\w/g, (l: string) => l.toUpperCase());
  };

  const shouldShowParameter = (
    paramName: string,
    config: ParameterConfig,
  ): boolean => {
    if (!config.dependsOn) {
      return true;
    }

    // Check if all dependencies are met
    for (const [depParam, requiredValue] of Object.entries(config.dependsOn)) {
      const currentValue = parameters[depParam];
      if (currentValue !== requiredValue) {
        return false;
      }
    }

    return true;
  };

  const renderParameterField = (
    paramName: string,
    config: ParameterConfig,
  ): React.ReactNode => {
    let value: any;

    if (paramName.includes(".")) {
      // Handle nested object properties
      const [objectName, propName] = paramName.split(".");
      value = parameters[objectName]?.[propName] ?? config.default;
    } else {
      value = parameters[paramName] ?? config.default;
    }

    switch (config.type) {
      case "string":
        if (config.enum) {
          return (
            <div className="space-y-2">
              <Label className="text-sm font-medium text-gray-700">
                {getFieldLabel(paramName, config)}
              </Label>
              <Select
                value={value || ""}
                onValueChange={(val: string) =>
                  handleParameterChange(paramName, val)
                }
                disabled={isLoading}
              >
                <SelectTrigger className="w-full">
                  <SelectValue
                    placeholder={
                      /* translators: %s: Field description (e.g., status, type) */
                      sprintf(
                        __("Select %s", "easycommerce-fakerpress"),
                        config.description || "option",
                      )
                    }
                  />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="none">
                    {
                      /* translators: %s: Field description (e.g., status, type) */
                      sprintf(
                        __("Select %s", "easycommerce-fakerpress"),
                        config.description || "option",
                      )
                    }
                  </SelectItem>
                  {config.enum.map((option: string) => (
                    <SelectItem key={option} value={option}>
                      {option
                        .replace(/_/g, " ")
                        .replace(/\b\w/g, (l: string) => l.toUpperCase())}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          );
        }
        return (
          <div className="space-y-2">
            <Label className="text-sm font-medium text-gray-700">
              {getFieldLabel(paramName, config)}
            </Label>
            <Input
              value={value || ""}
              placeholder={config.description}
              disabled={isLoading}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                handleParameterChange(paramName, e.target.value)
              }
            />
          </div>
        );

      case "integer":
        return (
          <div className="space-y-2">
            <Label className="text-sm font-medium text-gray-700">
              {getFieldLabel(paramName, config)}
            </Label>
            <Input
              type="number"
              value={value || ""}
              className="w-32"
              min={config.minimum || 0}
              max={config.maximum || 1000}
              placeholder={config.description}
              disabled={isLoading}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                handleParameterChange(paramName, parseInt(e.target.value))
              }
            />
          </div>
        );

      case "number":
        return (
          <div className="space-y-2">
            <Label className="text-sm font-medium text-gray-700">
              {getFieldLabel(paramName, config)}
            </Label>
            <Input
              type="number"
              step="0.01"
              value={value || ""}
              className="w-32"
              min={config.minimum || 0}
              max={config.maximum || 10000}
              placeholder={config.description}
              disabled={isLoading}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                handleParameterChange(paramName, parseFloat(e.target.value))
              }
            />
          </div>
        );

      case "boolean":
        return (
          <div className="flex items-center relative">
            <Switch
              checked={value || false}
              onCheckedChange={(checked: boolean) =>
                handleParameterChange(paramName, checked)
              }
              disabled={isLoading}
            />
            <Label className="ml-3 text-sm font-medium text-gray-700">
              {config.title ||
                config.description ||
                getFieldLabel(paramName, config)}
            </Label>
          </div>
        );

      case "array":
        if (config.items && config.items.enum) {
          const selectedValues = Array.isArray(value)
            ? value
            : config.default || [];
          return (
            <div className="space-y-2 relative">
              <Label className="block text-sm font-medium text-gray-700">
                {config.title ||
                  config.description ||
                  getFieldLabel(paramName, config)}
              </Label>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                {config.items.enum.map((option: string) => (
                  <div key={option} className="flex items-center">
                    <Switch
                      checked={selectedValues.includes(option)}
                      onCheckedChange={(checked: boolean) => {
                        const newValues = checked
                          ? [...selectedValues, option]
                          : selectedValues.filter((v: string) => v !== option);
                        handleParameterChange(paramName, newValues);
                      }}
                      disabled={isLoading}
                    />
                    <Label className="ml-3 text-sm font-medium text-gray-700">
                      {option
                        .replace(/_/g, " ")
                        .replace(/\b\w/g, (l) => l.toUpperCase())}
                    </Label>
                  </div>
                ))}
              </div>
            </div>
          );
        }
        return null;

      case "object":
        return (
          <div className="space-y-4">
            {config.properties &&
              Object.entries(config.properties).map(
                ([propName, propConfig]) => {
                  const fullParamName = `${paramName}.${propName}`;
                  if (!shouldShowParameter(fullParamName, propConfig)) {
                    return null;
                  }

                  return (
                    <div key={propName}>
                      {renderParameterField(fullParamName, propConfig)}
                    </div>
                  );
                },
              )}
          </div>
        );

      default:
        return null;
    }
  };

  // Animation variants
  const containerVariants: Variants = {
    hidden: { opacity: 0, y: 20 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 0.6,
        ease: [0.4, 0.0, 0.2, 1], // easeOut cubic-bezier
        staggerChildren: 0.1,
      },
    },
  };

  const itemVariants: Variants = {
    hidden: { opacity: 0, y: 10 },
    visible: {
      opacity: 1,
      y: 0,
      transition: { duration: 0.4, ease: [0.4, 0.0, 0.2, 1] },
    },
  };

  const buttonVariants = {
    idle: { scale: 1 },
    hover: { scale: 1.02 },
    tap: { scale: 0.98 },
  };

  return (
    <motion.div
      className="max-w-7xl mx-auto px-6 space-y-8"
      variants={containerVariants}
      initial="hidden"
      animate="visible"
    >
      <motion.div className="text-center mb-8" variants={itemVariants}>
        <motion.h1
          className="text-4xl font-bold text-gray-900 tracking-tight mb-4"
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
        >
          {title}
        </motion.h1>
        <motion.p
          className="text-xl text-gray-600 leading-relaxed max-w-3xl mx-auto"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.4 }}
        >
          {description}
        </motion.p>
        <motion.div
          className="mt-6 h-1 w-32 bg-linear-to-r from-blue-500 to-purple-600 rounded-full mx-auto"
          initial={{ width: 0 }}
          animate={{ width: 128 }}
          transition={{ delay: 0.6, duration: 0.8 }}
        />
      </motion.div>

      {/* Data Validation Status */}

      <div className="space-y-6">
        {/* Basic Parameters */}
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">
              {__("Basic Settings", "easycommerce-fakerpress")}
            </CardTitle>
            <CardDescription>
              {__(
                "Configure the basic parameters for your data generation.",
                "easycommerce-fakerpress",
              )}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div className="space-y-1 relative">
                <Label
                  htmlFor={`${type}-count`}
                  className="block text-sm font-medium text-gray-700"
                >
                  {
                    /* translators: %s: Resource type (e.g., products, customers, orders) */
                    sprintf(
                      __("Number of %s to generate", "easycommerce-fakerpress"),
                      type,
                    )
                  }
                </Label>
                <Input
                  type="number"
                  id={`${type}-count`}
                  value={count}
                  min="1"
                  max="100"
                  className="w-32"
                  disabled={isLoading}
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                    setCount(parseInt(e.target.value))
                  }
                />
              </div>

              <div className="space-y-1 relative">
                <Label className="block text-sm font-medium text-gray-700">
                  {__("Locale", "easycommerce-fakerpress")}
                </Label>
                <Select
                  value={
                    parameters.locale ||
                    window.easycommerceFakerpressApi?.locale?.faker ||
                    "en_US"
                  }
                  onValueChange={(val: string) =>
                    handleParameterChange("locale", val)
                  }
                  disabled={isLoading}
                >
                  <SelectTrigger className="w-full">
                    <SelectValue
                      placeholder={__(
                        "Select Locale",
                        "easycommerce-fakerpress",
                      )}
                    />
                  </SelectTrigger>
                  <SelectContent>
                    {Object.entries(
                      window.easycommerceFakerpressApi?.locale?.allLocales ||
                        {},
                    ).map(([localeCode, localeLabel]) => (
                      <SelectItem key={localeCode} value={localeCode}>
                        {localeLabel}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
              <div className="space-y-1 relative">
                <Label className="block text-sm font-medium text-gray-700">
                  {__("Random Seed (optional)", "easycommerce-fakerpress")}
                </Label>
                <Input
                  type="number"
                  value={parameters.seed || ""}
                  className="w-32"
                  placeholder={__(
                    "For reproducible data",
                    "easycommerce-fakerpress",
                  )}
                  disabled={isLoading}
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                    handleParameterChange("seed", parseInt(e.target.value))
                  }
                />
              </div>

              <div className="flex items-center relative">
                <Switch
                  checked={parameters.include_meta !== false}
                  onCheckedChange={(checked: boolean) =>
                    handleParameterChange("include_meta", checked)
                  }
                  disabled={isLoading}
                />
                <Label className="ml-3 text-sm font-medium text-gray-700">
                  {__("Include additional metadata", "easycommerce-fakerpress")}
                </Label>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Advanced Parameters Toggle */}
        {Object.keys(parameterConfig).length > 0 && (
          <div className="border-t border-gray-200 pt-6 mt-6">
            <Collapsible className="w-full">
              <CollapsibleTrigger className="flex items-center justify-between w-full text-left group pb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">
                    {__("Advanced Parameters", "easycommerce-fakerpress")}
                  </h3>
                  <p className="text-sm text-gray-600 mt-1">
                    {__(
                      "Fine-tune your data generation with advanced configuration options.",
                      "easycommerce-fakerpress",
                    )}
                  </p>
                </div>
                <motion.div
                  animate={{ rotate: 0 }}
                  whileHover={{ rotate: 180 }}
                  transition={{ duration: 0.2 }}
                >
                  <ChevronDown
                    className="h-5 w-5 text-gray-500 group-hover:text-blue-500 transition-colors"
                    aria-hidden="true"
                  />
                </motion.div>
              </CollapsibleTrigger>

              <CollapsibleContent className="space-y-6">
                {Object.entries(parameterConfig).map(
                  ([paramName, config], index) => {
                    if (!shouldShowParameter(paramName, config)) {
                      return null;
                    }

                    return (
                      <motion.div
                        key={paramName}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: index * 0.1 }}
                      >
                        {renderParameterField(paramName, config)}
                      </motion.div>
                    );
                  },
                )}
              </CollapsibleContent>
            </Collapsible>
          </div>
        )}
        {/* Custom Children Content */}
        {children}

        <motion.div variants={buttonVariants} whileHover="hover" whileTap="tap">
          <Button
            disabled={isLoading}
            onClick={handleSubmit}
            size="lg"
            className="w-full sm:w-auto"
          >
            {isLoading ? (
              <>
                <svg
                  className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    className="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    strokeWidth="4"
                  ></circle>
                  <path
                    className="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                {__("Generating…", "easycommerce-fakerpress")}
              </>
            ) : (
              __("Generate", "easycommerce-fakerpress")
            )}
          </Button>
        </motion.div>

        {error && (
          <div className="mt-4 rounded-md bg-red-50 p-4">
            <div className="flex">
              <div className="shrink-0">
                <svg
                  className="h-5 w-5 text-red-400"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  aria-hidden="true"
                >
                  <path
                    fillRule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clipRule="evenodd"
                  />
                </svg>
              </div>
              <div className="ml-3">
                <h3 className="text-sm font-medium text-red-800">
                  {__("Error", "easycommerce-fakerpress")}
                </h3>
                <RawHTML className="mt-2 text-sm text-red-700 [&_a]:text-red-900 [&_a]:font-bold [&_a]:underline [&_a:hover]:text-red-800">
                  {error}
                </RawHTML>
              </div>
            </div>
          </div>
        )}

        {result && (
          <div className="mt-4 rounded-md bg-green-50 p-4 border border-green-400">
            <div className="flex">
              <div className="shrink-0">
                <svg
                  className="h-5 w-5 text-green-400"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  aria-hidden="true"
                >
                  <path
                    fillRule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clipRule="evenodd"
                  />
                </svg>
              </div>
              <div className="ml-3">
                <h3 className="text-sm font-medium text-green-800 m-0">
                  {result.message}
                </h3>
                {/*

							<h3 className="text-sm font-medium text-green-800">{ __( 'Success!', 'easycommerce-fakerpress' ) }</h3>
							<div className="mt-2 text-sm text-green-700">
								{ /\* translators: %1$d: Number of items generated, %2$s: Resource type (e.g., products, customers, orders) *\/
								sprintf( __( 'Generated %1$d %2$s.', 'easycommerce-fakerpress' ), result.generated, type ) }
							</div>

							*/}

                {/*
							{ ( () => {
								// Get the plural form of the type to match backend response structure
								const pluralType = `${ type }s`;
								const items = result[ pluralType ] || [];

								return items.length > 0 && (
									<div className="mt-4">
										<h4 className="text-sm font-medium text-gray-900">{ __( 'Generated items:', 'easycommerce-fakerpress' ) }</h4>
										<ul className="mt-2 text-sm text-gray-600 divide-y divide-gray-200">
											{ items.slice( 0, 5 ).map( ( item, index ) => (
												<li key={ index } className="flex justify-between py-2">
													<span>{ item.name || item.title || item.code || item.id }</span>
													<span className="text-gray-500">
														{ item.email || item.price || item.total || item.amount || item.status }
													</span>
												</li>
											) ) }
											{ items.length > 5 && (
												<li className="py-2 text-gray-500 italic">
													{ //\* translators: %d: Number of additional items not shown in the list \*\/
														sprintf( __( '… and %d more', 'easycommerce-fakerpress' ), items.length - 5 ) }
												</li>
											) }
										</ul>
									</div>
								);
							} )() }
								*/}
              </div>
            </div>
          </div>
        )}
      </div>
    </motion.div>
  );
}
