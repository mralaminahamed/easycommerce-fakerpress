import { Switch } from "@/admin/components/ui/switch";
import { Label } from "@/admin/components/ui/label";
import type { ParameterConfig } from "@/admin/types";

interface BooleanFieldProps {
  paramName: string;
  config: ParameterConfig;
  value: boolean;
  disabled: boolean;
  onChange: (val: boolean) => void;
}

function toLabel(paramName: string, config: ParameterConfig): string {
  if (config.title) return config.title;
  const base = paramName.includes(".") ? paramName.split(".")[1] : paramName;
  return base.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
}

export function BooleanField({
  paramName,
  config,
  value,
  disabled,
  onChange,
}: BooleanFieldProps) {
  return (
    <div className="flex items-center gap-3">
      <Switch
        id={paramName}
        checked={value}
        onCheckedChange={onChange}
        disabled={disabled}
      />
      <Label htmlFor={paramName} className="text-sm font-medium text-gray-700 cursor-pointer">
        {toLabel(paramName, config)}
      </Label>
    </div>
  );
}
