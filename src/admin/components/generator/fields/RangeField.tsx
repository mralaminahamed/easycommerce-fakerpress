import { useState } from "@wordpress/element";
import { Input } from "@/admin/components/ui/input";
import { Label } from "@/admin/components/ui/label";
import type { ParameterConfig } from "@/admin/types";

interface RangeValue {
  min: number;
  max: number;
}

interface RangeFieldProps {
  paramName: string;
  config: ParameterConfig;
  value: RangeValue;
  disabled: boolean;
  onChange: (val: RangeValue) => void;
}

function toLabel(paramName: string, config: ParameterConfig): string {
  if (config.title) return config.title;
  const base = paramName.includes(".") ? paramName.split(".")[1] : paramName;
  return base.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
}

export function RangeField({
  paramName,
  config,
  value,
  disabled,
  onChange,
}: RangeFieldProps) {
  const [error, setError] = useState<string | null>(null);

  const handleChange = (key: "min" | "max", raw: string) => {
    const n = parseFloat(raw);
    const next: RangeValue = { ...value, [key]: isNaN(n) ? 0 : n };
    setError(next.min > next.max ? "Min must be ≤ Max" : null);
    onChange(next);
  };

  return (
    <div className="space-y-1.5">
      <Label className="text-sm font-medium text-gray-700">
        {toLabel(paramName, config)}
      </Label>
      <div className="flex items-end gap-2">
        <div className="space-y-1">
          <Input
            type="number"
            value={value.min ?? ""}
            disabled={disabled}
            data-testid="range-min"
            className="w-24"
            onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
              handleChange("min", e.target.value)
            }
          />
          <span className="text-xs text-gray-400 block text-center">Min</span>
        </div>
        <span className="text-gray-400 pb-5 select-none">–</span>
        <div className="space-y-1">
          <Input
            type="number"
            value={value.max ?? ""}
            disabled={disabled}
            data-testid="range-max"
            className="w-24"
            onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
              handleChange("max", e.target.value)
            }
          />
          <span className="text-xs text-gray-400 block text-center">Max</span>
        </div>
      </div>
      {error && <p className="text-xs text-red-500">{error}</p>}
    </div>
  );
}
