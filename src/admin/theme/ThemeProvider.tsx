import React from "react";
import { useState, useEffect, useRef, useCallback } from "@wordpress/element";
import {
	ThemeContext,
	type Theme,
	type Accent,
	type Density,
	type CustomColors,
} from "./useTheme";

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

function loadCustomColors(): CustomColors {
	try {
		const raw = localStorage.getItem("fp_custom_colors");
		if (!raw) return {};
		const parsed = JSON.parse(raw);
		return parsed && typeof parsed === "object" ? parsed : {};
	} catch {
		return {};
	}
}

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
	const [customColors, setCustomColors] = useState<CustomColors>(loadCustomColors);

	useEffect(() => {
		save("fp_theme", theme);
		save("fp_accent", accent);
		save("fp_density", density);
	}, [theme, accent, density]);

	useEffect(() => {
		save("fp_custom_colors", JSON.stringify(customColors));
	}, [customColors]);

	const setCustomColor = useCallback((token: string, value: string) => {
		setCustomColors((prev) => {
			const next = { ...prev };
			if (value) {
				next[token] = value;
			} else {
				delete next[token];
			}
			return next;
		});
	}, []);

	const resetCustomColors = useCallback(() => setCustomColors({}), []);

	// Apply each override as an inline CSS custom property on the root. Tokens
	// derived via color-mix (e.g. --accent-hover) recompute automatically.
	const rootStyle: React.CSSProperties = {};
	for (const [token, value] of Object.entries(customColors)) {
		(rootStyle as Record<string, string>)[`--${token}`] = value;
	}

	return (
		<ThemeContext.Provider
			value={{
				theme,
				accent,
				density,
				customColors,
				setTheme,
				setAccent,
				setDensity,
				setCustomColor,
				resetCustomColors,
			}}
		>
			<div
				ref={rootRef}
				className="fp-root"
				data-theme={theme}
				data-accent={accent}
				data-density={density}
				style={rootStyle}
			>
				{children}
			</div>
		</ThemeContext.Provider>
	);
}
