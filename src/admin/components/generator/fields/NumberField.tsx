import { Input } from "@/admin/components/ui/input";
import { Label } from "@/admin/components/ui/label";
import { toLabel } from "@/admin/lib/utils";
import type { ParameterConfig } from "@/admin/types";

interface NumberFieldProps {
  paramName: string;
  config: ParameterConfig;
  value: number | "";
  disabled: boolean;
  onChange: (val: number) => void;
}

export function NumberField({
  paramName,
  config,
  value,
  disabled,
  onChange,
}: NumberFieldProps) {
  const step = config.type === "number" ? "0.01" : "1";
  const parse = config.type === "number"
    ? parseFloat
    : (v: string) => parseInt(v, 10);

  return (
    <div className="space-y-1.5">
      <Label className="text-sm font-medium text-gray-700">
        {toLabel(paramName, config)}
      </Label>
      <Input
        type="number"
        step={step}
        value={value}
        min={config.minimum}
        max={config.maximum}
        placeholder={config.description}
        disabled={disabled}
        className="w-32"
        onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
          const n = parse(e.target.value);
          if (!isNaN(n)) onChange(n);
        }}
      />
    </div>
  );
}
