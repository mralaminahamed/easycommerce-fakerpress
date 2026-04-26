import { cva } from 'class-variance-authority';
import { clsx } from 'clsx';
import { type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { ParameterConfig } from "@/admin/types";

export function cn( ...inputs: ClassValue[] ) {
	return twMerge( clsx( inputs ) );
}

export { cva };

export function toLabel(paramName: string, config: ParameterConfig): string {
  if (config.title) return config.title;
  const base = paramName.includes(".") ? paramName.split(".")[1] : paramName;
  return base.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
}
