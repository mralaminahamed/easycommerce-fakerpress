import { Input } from "@/admin/components/ui/input";
import { Label } from "@/admin/components/ui/label";
import type { ParameterConfig } from "@/admin/types";

interface NumberFieldProps {
  paramName: string;
  config: ParameterConfig;
  value: number | "";
  disabled: boolean;
  onChange: (val: number) => void;
}

function toLabel(paramName: string, config: ParameterConfig): string {
  if (config.title) return config.title;
  const base = paramName.includes(".") ? paramName.split(".")[1] : paramName;
  return base.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
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
