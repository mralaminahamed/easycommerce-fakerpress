import { Label } from "@/admin/components/ui/label";
import { Chip } from "@/admin/components/ui/chip";
import { toLabel } from "@/admin/lib/utils";
import type { ParameterConfig } from "@/admin/types";

interface ChipFieldProps {
  paramName: string;
  config: ParameterConfig;
  value: string[];
  disabled: boolean;
  onChange: (val: string[]) => void;
}

export function ChipField({
  paramName,
  config,
  value,
  disabled,
  onChange,
}: ChipFieldProps) {
  const options = config.items?.enum ?? [];

  const toggle = (option: string, selected: boolean) => {
    if (!selected && value.length === 1 && value[0] === option) {
      return; // at-least-one constraint
    }
    onChange(
      selected ? [...value, option] : value.filter((v) => v !== option),
    );
  };

  return (
    <div className="space-y-2">
      <Label className="text-sm font-medium text-gray-700">
        {toLabel(paramName, config)}
      </Label>
      <div className="flex flex-wrap gap-2">
        {options.map((option) => (
          <Chip
            key={option}
            label={option
              .replace(/_/g, " ")
              .replace(/\b\w/g, (l) => l.toUpperCase())}
            value={option}
            selected={value.includes(option)}
            disabled={disabled}
            onChange={(sel) => toggle(option, sel)}
          />
        ))}
      </div>
    </div>
  );
}
