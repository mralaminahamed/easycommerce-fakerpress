import { Input } from "@/admin/components/ui/input";
import { Label } from "@/admin/components/ui/label";
import type { ParameterConfig } from "@/admin/types";

interface TextFieldProps {
  paramName: string;
  config: ParameterConfig;
  value: string;
  disabled: boolean;
  onChange: (val: string) => void;
}

function toLabel(paramName: string, config: ParameterConfig): string {
  if (config.title) return config.title;
  const base = paramName.includes(".") ? paramName.split(".")[1] : paramName;
  return base.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
}

export function TextField({
  paramName,
  config,
  value,
  disabled,
  onChange,
}: TextFieldProps) {
  return (
    <div className="space-y-1.5">
      <Label className="text-sm font-medium text-gray-700">
        {toLabel(paramName, config)}
      </Label>
      <Input
        value={value}
        placeholder={config.description}
        disabled={disabled}
        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
          onChange(e.target.value)
        }
      />
    </div>
  );
}
