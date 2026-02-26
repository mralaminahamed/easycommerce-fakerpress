import apiFetch from "@wordpress/api-fetch";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import type { GeneratorResult } from "@/admin/types";
import GeneratorBase from "@/admin/components/GeneratorBase";

export default function TaxClassGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, any>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/tax_classes/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating tax classes.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    tax_types: {
      description: __(
        "Types of tax classes to generate",
        "easycommerce-fakerpress",
      ),
      type: "array",
      items: {
        type: "string",
        enum: ["standard", "reduced", "zero", "exempt", "digital"],
      },
      default: ["standard", "reduced", "zero"],
    },
    jurisdictions: {
      description: __(
        "Tax jurisdictions to generate rates for",
        "easycommerce-fakerpress",
      ),
      type: "array",
      items: {
        type: "string",
        enum: ["country", "state", "city", "county", "postcode"],
      },
      default: ["country", "state"],
    },
    rate_ranges: {
      description: __("Tax rate ranges by type", "easycommerce-fakerpress"),
      type: "object",
      properties: {
        standard: {
          description: __("Standard tax rate range", "easycommerce-fakerpress"),
          type: "object",
          properties: {
            min: {
              type: "number",
              minimum: 0,
              maximum: 50,
              default: 5,
            },
            max: {
              type: "number",
              minimum: 0,
              maximum: 50,
              default: 25,
            },
          },
        },
        reduced: {
          description: __("Reduced tax rate range", "easycommerce-fakerpress"),
          type: "object",
          properties: {
            min: {
              type: "number",
              minimum: 0,
              maximum: 20,
              default: 1,
            },
            max: {
              type: "number",
              minimum: 0,
              maximum: 20,
              default: 10,
            },
          },
        },
      },
    },
    location_coverage: {
      description: __(
        "Geographic coverage for tax rates",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        countries: {
          description: __(
            "Countries to generate tax rates for",
            "easycommerce-fakerpress",
          ),
          type: "array",
          items: {
            type: "string",
          },
          default: ["US", "CA", "GB", "AU", "DE"],
        },
        include_compound: {
          description: __(
            "Include compound tax rates",
            "easycommerce-fakerpress",
          ),
          type: "boolean",
          default: true,
        },
      },
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Tax Classes", "easycommerce-fakerpress")}
      description={__(
        "Create tax classes with location-based rates for different jurisdictions and product types.",
        "easycommerce-fakerpress",
      )}
      type="tax_class"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
