import { useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

import GeneratorBase from "../GeneratorBase";

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function OrderGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, any>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/orders/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating orders.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    order_status: {
      description: __("Order status distribution", "easycommerce-fakerpress"),
      type: "string",
      enum: ["pending", "processing", "completed", "cancelled", "refunded"],
      default: "completed",
    },
    date_range: {
      description: __(
        "Date range for order creation",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        start_date: { type: "string", format: "date" },
        end_date: { type: "string", format: "date" },
      },
    },
    payment_methods: {
      description: __("Payment method distribution", "easycommerce-fakerpress"),
      type: "array",
      items: {
        type: "string",
        enum: ["credit_card", "paypal", "bank_transfer", "cash"],
      },
      default: ["credit_card", "paypal"],
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Orders", "easycommerce-fakerpress")}
      description={__(
        "Create complete order histories with payments, shipping, and tax calculations. Test your checkout flow and order management system.",
        "easycommerce-fakerpress",
      )}
      type="order"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
