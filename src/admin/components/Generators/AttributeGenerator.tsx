import apiFetch from "@wordpress/api-fetch";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import type { GeneratorResult } from "@/admin/types";
import GeneratorBase from "@/admin/components/GeneratorBase";

export default function AttributeGenerator() {
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleGenerate = async (params: Record<string, unknown>) => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await apiFetch({
        path: "/easycommerce-fakerpress/v1/attributes/generate",
        method: "POST",
        data: params,
      });

      setResult(data as GeneratorResult);
    } catch (err) {
      const errorMessage =
        err instanceof Error
          ? err.message
          : __(
              "An error occurred while generating attributes.",
              "easycommerce-fakerpress",
            );
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const parameterConfig = {
    attribute_types: {
      description: __(
        "Types of attributes to generate",
        "easycommerce-fakerpress",
      ),
      type: "array",
      items: {
        type: "string",
        enum: ["Text", "Color", "Image"],
      },
      default: ["Text", "Color"],
    },
  };

  return (
    <GeneratorBase
      title={__("Generate Attributes", "easycommerce-fakerpress")}
      description={__(
        "Generate product attributes such as Text, Color, and Image types. Attributes can be used to define product variations and filtering options.",
        "easycommerce-fakerpress",
      )}
      type="attributes"
      onGenerate={handleGenerate}
      isLoading={isLoading}
      result={result}
      error={error}
      parameterConfig={parameterConfig}
    />
  );
}
