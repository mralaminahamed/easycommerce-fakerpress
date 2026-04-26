import { __, sprintf } from "@wordpress/i18n";
import { Label } from "@/admin/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/admin/components/ui/select";
import type { ParameterConfig } from "@/admin/types";

interface SelectFieldProps {
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

export function SelectField({
  paramName,
  config,
  value,
  disabled,
  onChange,
}: SelectFieldProps) {
  const label = toLabel(paramName, config);

  return (
    <div className="space-y-1.5">
      <Label className="text-sm font-medium text-gray-700">{label}</Label>
      <Select value={value || ""} onValueChange={onChange} disabled={disabled}>
        <SelectTrigger className="w-full">
          <SelectValue
            placeholder={sprintf(
              /* translators: %s: field label */
              __("Select %s", "easycommerce-fakerpress"),
              label,
            )}
          />
        </SelectTrigger>
        <SelectContent>
          {(config.enum ?? []).map((option) => (
            <SelectItem key={option} value={option}>
              {option.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase())}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  );
}
