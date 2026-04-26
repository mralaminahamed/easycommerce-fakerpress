import { Switch } from "@/admin/components/ui/switch";
import { Label } from "@/admin/components/ui/label";
import { toLabel } from "@/admin/lib/utils";
import type { ParameterConfig } from "@/admin/types";

interface BooleanFieldProps {
  paramName: string;
  config: ParameterConfig;
  value: boolean;
  disabled: boolean;
  onChange: (val: boolean) => void;
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
