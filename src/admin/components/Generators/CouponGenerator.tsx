import { useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

import GeneratorBase from "../GeneratorBase";

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function CouponGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, any>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/coupons/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating coupons.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    discount_type: {
      description: __(
        "Type of discount for coupons",
        "easycommerce-fakerpress",
      ),
      type: "string",
      enum: ["percentage", "fixed", "free_shipping"],
      default: "percentage",
    },
    discount_amount: {
      description: __("Discount amount range", "easycommerce-fakerpress"),
      type: "object",
      properties: {
        min: { type: "number", minimum: 1, default: 5 },
        max: { type: "number", minimum: 5, default: 50 },
      },
    },
    usage_limits: {
      description: __(
        "Usage restrictions for coupons",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        usage_limit: { type: "integer", minimum: 1, default: 100 },
        usage_limit_per_user: { type: "integer", minimum: 1, default: 1 },
      },
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Coupons", "easycommerce-fakerpress")}
      description={__(
        "Generate discount codes with various rules and restrictions. Perfect for testing promotional campaigns and discount logic.",
        "easycommerce-fakerpress",
      )}
      type="coupon"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
