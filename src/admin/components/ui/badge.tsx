import React from "react";

import { cn } from "@/admin/lib/utils";

// Back-compat shim — legacy callers that used badgeVariants directly
export function badgeVariants() {
	return "";
}

export interface BadgeProps extends React.HTMLAttributes<HTMLSpanElement> {
	kind?: string;
	soft?: boolean;
}

export function Badge({ children, kind = "neutral", soft, className, ...rest }: BadgeProps) {
	return (
		<span
			className={cn(`fp-badge tone-${kind}`, soft ? "soft" : "", className)}
			{...rest}
		>
			{children}
		</span>
	);
}
