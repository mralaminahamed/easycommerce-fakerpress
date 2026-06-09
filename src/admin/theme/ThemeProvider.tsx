import React from "react";
import { useState, useEffect, useRef } from "@wordpress/element";
import { ThemeContext, type Theme, type Accent, type Density } from "./useTheme";

const LS = (k: string, d: string) => {
	try {
		return localStorage.getItem(k) ?? d;
	} catch {
		return d;
	}
};
const save = (k: string, v: string) => {
	try {
		localStorage.setItem(k, v);
	} catch {}
};

export function ThemeProvider({ children }: { children: React.ReactNode }) {
	const rootRef = useRef<HTMLDivElement>(null);
	const [theme, setTheme] = useState<Theme>(
		() => LS("fp_theme", "light") as Theme
	);
	const [accent, setAccent] = useState<Accent>(
		() => LS("fp_accent", "indigo") as Accent
	);
	const [density, setDensity] = useState<Density>(
		() => LS("fp_density", "comfortable") as Density
	);

	useEffect(() => {
		save("fp_theme", theme);
		save("fp_accent", accent);
		save("fp_density", density);
	}, [theme, accent, density]);

	return (
		<ThemeContext.Provider
			value={{ theme, accent, density, setTheme, setAccent, setDensity }}
		>
			<div
				ref={rootRef}
				className="fp-root"
				data-theme={theme}
				data-accent={accent}
				data-density={density}
			>
				{children}
			</div>
		</ThemeContext.Provider>
	);
}
