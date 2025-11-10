import { useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

import GeneratorBase from "../GeneratorBase";

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function ProductVariationGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, any>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/product-variations/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating product variations.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    variation_type: {
      description: __(
        "Type of product variations to generate",
        "easycommerce-fakerpress",
      ),
      type: "string",
      enum: ["size", "color", "material", "mixed"],
      default: "mixed",
    },
    attributes_count: {
      description: __(
        "Number of attributes per variation",
        "easycommerce-fakerpress",
      ),
      type: "integer",
      minimum: 1,
      maximum: 5,
      default: 2,
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Product Variations", "easycommerce-fakerpress")}
      description={__(
        "Create complex product variations with size, color, and material options. Essential for testing variable product functionality.",
        "easycommerce-fakerpress",
      )}
      type="product-variation"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
