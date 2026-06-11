import React from "react";

interface SectionLabelProps {
	children: React.ReactNode;
	right?: React.ReactNode;
}

export function SectionLabel({ children, right }: SectionLabelProps) {
	return (
		<div className="fp-seclabel">
			<span>{children}</span>
			{right}
		</div>
	);
}
