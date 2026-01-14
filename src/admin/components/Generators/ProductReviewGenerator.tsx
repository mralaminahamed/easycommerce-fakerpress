import apiFetch from "@wordpress/api-fetch";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import GeneratorBase from "../GeneratorBase";

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function ProductReviewGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, any>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/product-reviews/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating product reviews.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    count: {
      description: __(
        "Number of product reviews to generate",
        "easycommerce-fakerpress",
      ),
      type: "number",
      min: 1,
      max: 100,
      default: 10,
    },
  };

  return (
    <GeneratorBase
      title={__("Product Reviews", "easycommerce-fakerpress")}
      description={__(
        "Generate realistic product reviews with ratings and customer feedback. Reviews are automatically linked to existing products and customers.",
        "easycommerce-fakerpress",
      )}
      type="product-reviews"
      parameterConfig={parameterConfig}
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
    />
  );
}
