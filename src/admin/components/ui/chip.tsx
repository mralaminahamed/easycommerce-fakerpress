import { cn } from "@/admin/lib/utils";

interface ChipProps {
  label: string;
  selected: boolean;
  disabled?: boolean;
  onChange: (selected: boolean) => void;
}

export function Chip({ label, selected, disabled, onChange }: ChipProps) {
  return (
    <button
      type="button"
      disabled={disabled}
      onClick={() => onChange(!selected)}
      className={cn(
        "rounded-full px-3 py-1 text-sm border cursor-pointer transition-colors select-none",
        selected
          ? "bg-blue-600 text-white border-blue-600"
          : "bg-white text-gray-600 border-gray-300 hover:border-blue-400",
        disabled && "opacity-50 cursor-not-allowed pointer-events-none",
      )}
    >
      {label}
    </button>
  );
}
