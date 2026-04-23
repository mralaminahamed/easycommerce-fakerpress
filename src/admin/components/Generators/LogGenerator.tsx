import apiFetch from "@wordpress/api-fetch";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import type { GeneratorResult } from "@/admin/types";
import GeneratorBase from "@/admin/components/GeneratorBase";

export default function LogGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, unknown>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/logs/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating logs.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    log_types: {
      description: __(
        "Log severity types to generate",
        "easycommerce-fakerpress",
      ),
      type: "array",
      items: {
        type: "string",
        enum: ["info", "warning", "error", "success"],
      },
      default: ["info", "warning", "error", "success"],
    },
    objects: {
      description: __(
        "Object types to generate log entries for",
        "easycommerce-fakerpress",
      ),
      type: "array",
      items: {
        type: "string",
        enum: [
          "order",
          "product",
          "customer",
          "coupon",
          "refund",
          "cart",
          "transaction",
          "system",
        ],
      },
      default: [
        "order",
        "product",
        "customer",
        "coupon",
        "refund",
        "cart",
        "transaction",
        "system",
      ],
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Logs", "easycommerce-fakerpress")}
      description={__(
        "Generate activity log entries for orders, products, customers, and system events. Useful for testing log views and audit trails.",
        "easycommerce-fakerpress",
      )}
      type="logs"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
