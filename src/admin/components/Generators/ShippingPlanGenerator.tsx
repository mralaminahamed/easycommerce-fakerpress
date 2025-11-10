import { useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

import GeneratorBase from "../GeneratorBase";

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function ShippingPlanGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, any>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/shipping-plans/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating shipping plans.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    shipping_type: {
      description: __(
        "Type of shipping methods to generate",
        "easycommerce-fakerpress",
      ),
      type: "string",
      enum: ["flat_rate", "free_shipping", "local_pickup"],
      default: "flat_rate",
    },
    zones_count: {
      description: __("Number of shipping zones", "easycommerce-fakerpress"),
      type: "integer",
      minimum: 1,
      maximum: 10,
      default: 3,
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Shipping Plans", "easycommerce-fakerpress")}
      description={__(
        "Generate shipping methods, zones, and rate tables. Test delivery calculations and logistics workflows.",
        "easycommerce-fakerpress",
      )}
      type="shipping-plan"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
