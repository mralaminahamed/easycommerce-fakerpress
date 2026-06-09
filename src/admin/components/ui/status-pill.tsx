import React from "react";

import { tone } from "@/admin/lib/tone";

interface StatusPillProps {
	children: React.ReactNode;
}

export function StatusPill({ children }: StatusPillProps) {
	const k = tone(String(children));
	return (
		<span className={`fp-status tone-${k}`}>
			<span className="fp-dot" />
			{children}
		</span>
	);
}
