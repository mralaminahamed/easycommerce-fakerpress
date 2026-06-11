import React from "react";

import { Icon } from "@/admin/lib/icons";
import { cn } from "@/admin/lib/utils";

// ── variant / size maps ─────────────────────────────────────────────────────
// FP canonical variants
type FpVariant = "primary" | "outline" | "ghost" | "soft" | "danger";
// FP canonical sizes
type FpSize = "sm" | "md" | "lg";

// Legacy CVA variant names (kept for back-compat with existing callers)
type LegacyVariant = "default" | "destructive" | "secondary" | "link";
// Legacy CVA size names
type LegacySize = "default" | "icon";

type ButtonVariant = FpVariant | LegacyVariant;
type ButtonSize = FpSize | LegacySize;

function toFpVariant(v: ButtonVariant | null | undefined): FpVariant {
	switch (v) {
		case "default":
		case "secondary":
			return "primary";
		case "destructive":
			return "danger";
		case "link":
			return "ghost";
		case "primary":
		case "outline":
		case "ghost":
		case "soft":
		case "danger":
			return v;
		default:
			return "outline";
	}
}

function toFpSize(s: ButtonSize | null | undefined): FpSize {
	switch (s) {
		case "default":
		case "icon":
		case "md":
			return "md";
		case "sm":
			return "sm";
		case "lg":
			return "lg";
		default:
			return "md";
	}
}

// ── back-compat buttonVariants export ──────────────────────────────────────
// Legacy callers (and VariantProps importers) that used buttonVariants() remain
// compilable. Returns empty string — callers that rendered via cn() will just
// pick up an empty class token, which is harmless.
export function buttonVariants(opts?: { variant?: ButtonVariant; size?: ButtonSize; className?: string }): string {
	return opts?.className ?? "";
}

// ── prop types ──────────────────────────────────────────────────────────────
export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
	variant?: ButtonVariant;
	size?: ButtonSize;
	/** FP icon name to render before children */
	icon?: string;
	/** FP icon name to render after children */
	iconRight?: string;
	/** Radix asChild back-compat (no-op — renders a plain button) */
	asChild?: boolean;
}

// ── component ───────────────────────────────────────────────────────────────
const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
	(
		{
			className,
			variant,
			size,
			icon,
			iconRight,
			asChild: _asChild,
			children,
			...rest
		},
		ref,
	) => {
		const fpVariant = toFpVariant(variant);
		const fpSize = toFpSize(size);
		const isIconOnly = size === "icon";

		return (
			<button
				ref={ref}
				className={cn(
					"fp-btn",
					`fp-btn-${fpVariant}`,
					`fp-btn-${fpSize}`,
					"fp-focusable",
					isIconOnly && "fp-btn-icon",
					className,
				)}
				{...rest}
			>
				{icon && typeof icon === "string" && (
					<Icon name={icon} size={fpSize === "sm" ? 15 : 16} />
				)}
				{children && <span>{children}</span>}
				{iconRight && typeof iconRight === "string" && (
					<Icon name={iconRight} size={fpSize === "sm" ? 15 : 16} />
				)}
			</button>
		);
	},
);
Button.displayName = "Button";

export { Button };
