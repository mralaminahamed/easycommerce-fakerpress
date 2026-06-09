import { createContext, useContext } from "@wordpress/element";

export type Theme = "light" | "dark";
export type Accent = "indigo" | "blue" | "violet" | "emerald" | "amber";
export type Density = "comfortable" | "compact";

export interface ThemeState {
	theme: Theme;
	accent: Accent;
	density: Density;
	setTheme: (t: Theme) => void;
	setAccent: (a: Accent) => void;
	setDensity: (d: Density) => void;
}

export const ThemeContext = createContext<ThemeState | null>(null);

export function useTheme(): ThemeState {
	const ctx = useContext(ThemeContext);
	if (!ctx) throw new Error("useTheme must be used within ThemeProvider");
	return ctx;
}
