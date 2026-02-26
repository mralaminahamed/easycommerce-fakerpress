import apiFetch from "@wordpress/api-fetch";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import type { GeneratorResult } from "@/admin/types";
import GeneratorBase from "@/admin/components/GeneratorBase";

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
    customer_types: {
      description: __(
        "Types of customers to generate",
        "easycommerce-fakerpress",
      ),
      type: "array",
      items: {
        type: "string",
        enum: ["regular", "vip", "wholesale", "guest", "returning"],
      },
      default: ["regular", "returning"],
    },
    demographics: {
      description: __("Demographic distribution", "easycommerce-fakerpress"),
      type: "object",
      properties: {
        age_groups: {
          description: __("Age group distribution", "easycommerce-fakerpress"),
          type: "array",
          items: {
            type: "string",
            enum: ["18-25", "26-35", "36-45", "46-55", "56-65", "65+"],
          },
          default: ["26-35", "36-45", "46-55"],
        },
        gender_distribution: {
          description: __(
            "Gender distribution weight",
            "easycommerce-fakerpress",
          ),
          type: "object",
          properties: {
            male: { type: "integer", minimum: 0, maximum: 100, default: 45 },
            female: { type: "integer", minimum: 0, maximum: 100, default: 45 },
            other: { type: "integer", minimum: 0, maximum: 100, default: 10 },
          },
        },
      },
    },
    address_preferences: {
      description: __(
        "Address generation preferences",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        include_billing: {
          description: __(
            "Include billing addresses",
            "easycommerce-fakerpress",
          ),
          type: "boolean",
          default: true,
        },
        include_shipping: {
          description: __(
            "Include shipping addresses",
            "easycommerce-fakerpress",
          ),
          type: "boolean",
          default: true,
        },
        different_addresses_ratio: {
          description: __(
            "Percentage with different billing/shipping (0–100)",
            "easycommerce-fakerpress",
          ),
          type: "integer",
          minimum: 0,
          maximum: 100,
          default: 30,
        },
      },
    },
    purchase_history: {
      description: __("Purchase history simulation", "easycommerce-fakerpress"),
      type: "object",
      properties: {
        simulate_history: {
          description: __(
            "Generate purchase history metadata",
            "easycommerce-fakerpress",
          ),
          type: "boolean",
          default: true,
        },
        loyalty_tiers: {
          description: __(
            "Include loyalty tier assignments",
            "easycommerce-fakerpress",
          ),
          type: "boolean",
          default: true,
        },
      },
    },
    contact_preferences: {
      description: __(
        "Contact and communication preferences",
        "easycommerce-fakerpress",
      ),
      type: "object",
      properties: {
        phone_numbers: {
          description: __("Include phone numbers", "easycommerce-fakerpress"),
          type: "boolean",
          default: true,
        },
        marketing_opt_in_ratio: {
          description: __(
            "Percentage opted in for marketing (0–100)",
            "easycommerce-fakerpress",
          ),
          type: "integer",
          minimum: 0,
          maximum: 100,
          default: 65,
        },
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
