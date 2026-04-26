import { __ } from "@wordpress/i18n";
import { BooleanField } from "./fields/BooleanField";
import { ChipField } from "./fields/ChipField";
import { NumberField } from "./fields/NumberField";
import { RangeField } from "./fields/RangeField";
import { SelectField } from "./fields/SelectField";
import { TextField } from "./fields/TextField";
import type { ParameterConfig } from "@/admin/types";

interface ParamsPanelProps {
  parameterConfig: Record<string, ParameterConfig>;
  params: Record<string, any>;
  disabled: boolean;
  onChange: (paramName: string, value: any) => void;
}

function toSectionLabel(key: string): string {
  return key.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
}

function isRangeObject(config: ParameterConfig): boolean {
  if (config.type !== "object" || !config.properties) return false;
  const keys = Object.keys(config.properties);
  return keys.length === 2 && keys.includes("min") && keys.includes("max");
}

function shouldShow(
  paramName: string,
  config: ParameterConfig,
  params: Record<string, any>,
): boolean {
  if (!config.dependsOn) return true;
  return Object.entries(config.dependsOn).every(([dep, val]) => {
    const topKey = dep.includes(".") ? dep.split(".")[0] : dep;
    return params[topKey] === val;
  });
}

function getDefaultValue(config: ParameterConfig): any {
  if (config.default !== undefined) return config.default;
  if (config.type === "boolean") return false;
  if (config.type === "array") return [];
  if (config.type === "object") return {};
  if (config.type === "integer" || config.type === "number") return "";
  return "";
}

export function ParamsPanel({
  parameterConfig,
  params,
  disabled,
  onChange,
}: ParamsPanelProps) {
  const entries = Object.entries(parameterConfig);

  if (entries.length === 0) {
    return (
      <p className="text-sm text-gray-400 italic py-4">
        {__(
          "No advanced parameters for this generator.",
          "easycommerce-fakerpress",
        )}
      </p>
    );
  }

  const renderField = (
    paramName: string,
    config: ParameterConfig,
    value: any,
    changeKey: string,
  ): React.ReactNode => {
    switch (config.type) {
      case "boolean":
        return (
          <BooleanField
            paramName={paramName}
            config={config}
            value={value ?? getDefaultValue(config)}
            disabled={disabled}
            onChange={(v) => onChange(changeKey, v)}
          />
        );

      case "integer":
      case "number":
        return (
          <NumberField
            paramName={paramName}
            config={config}
            value={value ?? getDefaultValue(config)}
            disabled={disabled}
            onChange={(v) => onChange(changeKey, v)}
          />
        );

      case "string":
        if (config.enum) {
          return (
            <SelectField
              paramName={paramName}
              config={config}
              value={value ?? getDefaultValue(config)}
              disabled={disabled}
              onChange={(v) => onChange(changeKey, v)}
            />
          );
        }
        return (
          <TextField
            paramName={paramName}
            config={config}
            value={value ?? getDefaultValue(config)}
            disabled={disabled}
            onChange={(v) => onChange(changeKey, v)}
          />
        );

      case "array":
        if (config.items?.enum) {
          return (
            <ChipField
              paramName={paramName}
              config={config}
              value={value ?? getDefaultValue(config)}
              disabled={disabled}
              onChange={(v) => onChange(changeKey, v)}
            />
          );
        }
        return null;

      case "object":
        if (isRangeObject(config)) {
          const minDefault = config.properties!.min.default ?? 0;
          const maxDefault = config.properties!.max.default ?? 100;
          return (
            <RangeField
              paramName={paramName}
              config={config}
              value={value ?? { min: minDefault, max: maxDefault }}
              disabled={disabled}
              onChange={(v) => onChange(changeKey, v)}
            />
          );
        }
        // Nested object: render child fields
        return (
          <div className="space-y-4">
            {Object.entries(config.properties ?? {}).map(
              ([propName, propConfig]) => {
                const fullKey = `${changeKey}.${propName}`;
                const nestedValue = params[changeKey.split(".")[0]]?.[propName];
                if (!shouldShow(propName, propConfig, params)) return null;
                return (
                  <div key={propName}>
                    {renderField(propName, propConfig, nestedValue, fullKey)}
                  </div>
                );
              },
            )}
          </div>
        );

      default:
        return null;
    }
  };

  return (
    <div className="space-y-6">
      {entries.map(([paramName, config]) => {
        if (!shouldShow(paramName, config, params)) return null;
        const value = params[paramName];
        return (
          <div key={paramName}>
            <div className="border-t border-gray-100 pt-4 mb-3">
              <h4 className="text-xs font-semibold uppercase tracking-wide text-gray-400">
                {toSectionLabel(paramName)}
              </h4>
            </div>
            {renderField(paramName, config, value, paramName)}
          </div>
        );
      })}
    </div>
  );
}
