import type { LucideIcon } from "lucide-react";
import { cn } from "@/admin/lib/utils";

const colorMap: Record<string, { border: string; icon: string }> = {
  blue:   { border: "border-b-blue-500",   icon: "text-blue-500" },
  purple: { border: "border-b-purple-500", icon: "text-purple-500" },
  indigo: { border: "border-b-indigo-500", icon: "text-indigo-500" },
  gray:   { border: "border-b-gray-400",   icon: "text-gray-400" },
};

interface StatCardProps {
  icon: LucideIcon;
  label: string;
  value: number;
  accentColor?: "blue" | "purple" | "indigo" | "gray";
  testId?: string;
}

export function StatCard({
  icon: Icon,
  label,
  value,
  accentColor = "blue",
  testId,
}: StatCardProps) {
  const colors = colorMap[accentColor] ?? colorMap.blue;
  const isEmpty = value === 0;

  return (
    <div
      data-testid={testId}
      className={cn(
        "rounded-xl bg-white shadow-sm p-5 border border-gray-100 border-b-4",
        colors.border,
      )}
    >
      <div className="flex items-center gap-2 mb-3">
        <Icon className={cn("w-4 h-4", colors.icon)} />
        <span className="text-xs font-semibold uppercase tracking-wide text-gray-400">
          {label}
        </span>
      </div>
      <p className="text-2xl font-semibold text-gray-900">
        {isEmpty ? "—" : value.toLocaleString()}
      </p>
      <p className="text-xs text-gray-400 mt-1">
        {isEmpty ? "Nothing generated yet" : "items generated"}
      </p>
    </div>
  );
}
