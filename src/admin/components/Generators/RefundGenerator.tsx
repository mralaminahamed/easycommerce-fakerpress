import apiFetch from "@wordpress/api-fetch";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import type { GeneratorResult } from "@/admin/types";
import GeneratorBase from "@/admin/components/GeneratorBase";

export default function RefundGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, unknown>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/refunds/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating refunds.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    order_statuses: {
      description: __("Order statuses eligible for refund generation", "easycommerce-fakerpress"),
      type: "array",
      items: {
        type: "string",
        enum: ["completed", "processing", "pending", "cancelled"],
      },
      default: ["completed", "processing"],
    },
    payment_gateways: {
      description: __("Payment gateways for transaction IDs", "easycommerce-fakerpress"),
      type: "array",
      items: {
        type: "string",
        enum: ["stripe", "paypal", "square", "bank_transfer", "authorize_net"],
      },
      default: ["stripe", "paypal", "square"],
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Refunds", "easycommerce-fakerpress")}
      description={__(
        "Generate refund records against existing orders. Requires completed or processing orders to exist.",
        "easycommerce-fakerpress",
      )}
      type="refunds"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
