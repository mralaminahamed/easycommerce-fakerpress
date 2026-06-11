import { createContext, useContext } from "@wordpress/element";

export type Theme = "light" | "dark";
export type Accent = "indigo" | "blue" | "violet" | "emerald" | "amber";
export type Density = "comfortable" | "compact";

/** Map of CSS token name (without the leading `--`) → user-picked color. */
export type CustomColors = Record<string, string>;

export interface ThemeState {
	theme: Theme;
	accent: Accent;
	density: Density;
	/** User overrides for individual design tokens (e.g. { accent: "#ff0000" }). */
	customColors: CustomColors;
	setTheme: (t: Theme) => void;
	setAccent: (a: Accent) => void;
	setDensity: (d: Density) => void;
	/** Override (or, with empty value, clear) a single token. */
	setCustomColor: (token: string, value: string) => void;
	/** Clear every custom color override. */
	resetCustomColors: () => void;
}

export const ThemeContext = createContext<ThemeState | null>(null);

export function useTheme(): ThemeState {
	const ctx = useContext(ThemeContext);
	if (!ctx) throw new Error("useTheme must be used within ThemeProvider");
	return ctx;
}
