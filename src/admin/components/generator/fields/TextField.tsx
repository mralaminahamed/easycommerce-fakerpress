import { Input } from "@/admin/components/ui/input";
import { Label } from "@/admin/components/ui/label";
import { toLabel } from "@/admin/lib/utils";
import type { ParameterConfig } from "@/admin/types";

interface TextFieldProps {
  paramName: string;
  config: ParameterConfig;
  value: string;
  disabled: boolean;
  onChange: (val: string) => void;
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
