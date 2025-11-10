import { useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

import GeneratorBase from "../GeneratorBase";

interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export default function CartSessionGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, any>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/cart-sessions/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating cart sessions.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    session_type: {
      description: __(
        "Type of cart sessions to generate",
        "easycommerce-fakerpress",
      ),
      type: "string",
      enum: ["abandoned", "active", "converted"],
      default: "abandoned",
    },
    items_count: {
      description: __("Number of items in cart", "easycommerce-fakerpress"),
      type: "integer",
      minimum: 1,
      maximum: 20,
      default: 3,
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Cart Sessions", "easycommerce-fakerpress")}
      description={__(
        "Create shopping cart abandonment scenarios and session data. Test cart recovery systems and analytics.",
        "easycommerce-fakerpress",
      )}
      type="cart-session"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
