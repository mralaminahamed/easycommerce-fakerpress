import { useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

import GeneratorBase from "../GeneratorBase";

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function CustomerGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, any>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/customers/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating customers.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    customer_type: {
      description: __(
        "Type of customers to generate",
        "easycommerce-fakerpress",
      ),
      type: "string",
      enum: ["individual", "business", "mixed"],
      default: "mixed",
    },
    registration_date_range: {
      description: __(
        "Date range for customer registration",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        start_date: { type: "string", format: "date" },
        end_date: { type: "string", format: "date" },
      },
    },
    order_history: {
      description: __(
        "Customer order history configuration",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        min_orders: { type: "integer", minimum: 0, default: 0 },
        max_orders: { type: "integer", minimum: 1, default: 50 },
        average_order_value: { type: "number", minimum: 10, default: 75 },
      },
    },
    contact_preferences: {
      description: __(
        "Customer contact and communication preferences",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        marketing_emails: { type: "boolean", default: true },
        sms_notifications: { type: "boolean", default: false },
        newsletter_subscription: { type: "boolean", default: true },
      },
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Customers", "easycommerce-fakerpress")}
      description={__(
        "Create fake customer profiles with addresses, purchase history, and contact preferences. Essential for testing user accounts and customer management.",
        "easycommerce-fakerpress",
      )}
      type="customer"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
