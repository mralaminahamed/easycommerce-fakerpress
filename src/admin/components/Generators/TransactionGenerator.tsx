import { useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

import GeneratorBase from "../GeneratorBase";

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function TransactionGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, any>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/transactions/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating transactions.",
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
        "Type of customers for transactions",
        "easycommerce-fakerpress",
      ),
      type: "string",
      enum: [
        "all",
        "specific",
        "existing_customers_only",
        "new_customers_only",
      ],
      default: "all",
    },
    specific_customer_id: {
      description: __(
        'Specific customer ID for transactions (when customer_type is "specific")',
        "easycommerce-fakerpress",
      ),
      type: "integer",
      minimum: 1,
      dependsOn: { customer_type: "specific" },
    },
    order_status_filter: {
      description: __(
        "Filter orders by status for transaction generation",
        "easycommerce-fakerpress",
      ),
      type: "array",
      items: {
        type: "string",
        enum: [
          "pending",
          "processing",
          "completed",
          "cancelled",
          "on_hold",
          "refunded",
        ],
      },
    },
    transaction_types: {
      description: __(
        "Types of transactions to generate",
        "easycommerce-fakerpress",
      ),
      type: "array",
      items: {
        type: "string",
        enum: ["payment", "refund", "adjustment", "fee", "commission"],
      },
      default: ["payment", "refund"],
    },
    payment_gateways: {
      description: __(
        "Payment gateways to use for transactions",
        "easycommerce-fakerpress",
      ),
      type: "array",
      items: {
        type: "string",
        enum: [
          "stripe",
          "paypal",
          "square",
          "authorize_net",
          "braintree",
          "razorpay",
          "mollie",
        ],
      },
      default: ["stripe", "paypal", "square"],
    },
    date_range: {
      description: __(
        "Date range for transaction generation",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        start: {
          description: __(
            "Start date (YYYY-MM-DD format)",
            "easycommerce-fakerpress",
          ),
          type: "string",
          format: "date",
        },
        end: {
          description: __(
            "End date (YYYY-MM-DD format)",
            "easycommerce-fakerpress",
          ),
          type: "string",
          format: "date",
        },
      },
    },
    amount_range: {
      description: __("Transaction amount range", "easycommerce-fakerpress"),
      type: "object",
      properties: {
        min: { type: "number", minimum: 0, default: 1 },
        max: { type: "number", minimum: 1, default: 1000 },
      },
    },
    status_distribution: {
      description: __(
        "Transaction status distribution",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        success_rate: {
          type: "integer",
          minimum: 0,
          maximum: 100,
          default: 85,
        },
        pending_rate: {
          type: "integer",
          minimum: 0,
          maximum: 100,
          default: 10,
        },
        failed_rate: { type: "integer", minimum: 0, maximum: 100, default: 5 },
      },
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Transactions", "easycommerce-fakerpress")}
      description={__(
        "Create realistic payment transaction history with different gateways, amounts, and status distributions.",
        "easycommerce-fakerpress",
      )}
      type="transaction"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
