export interface AppSettings {
  defaultCount: number;
  defaultLocale: string;
  defaultSeed: string;
  defaultIncludeMeta: boolean;
  maxRunsPerGenerator: number;
}

const SETTINGS_KEY = "ec_fp_settings";

function getDefaults(): AppSettings {
  return {
    defaultCount: 10,
    defaultLocale: window.easycommerceFakerpressApi?.locale?.faker ?? "en_US",
    defaultSeed: "",
    defaultIncludeMeta: true,
    maxRunsPerGenerator: 10,
  };
}

export function getSettings(): AppSettings {
  try {
    const raw = localStorage.getItem(SETTINGS_KEY);
    return raw
      ? { ...getDefaults(), ...JSON.parse(raw) }
      : getDefaults();
  } catch {
    return getDefaults();
  }
}

export function saveSettings(settings: AppSettings): void {
  try {
    localStorage.setItem(SETTINGS_KEY, JSON.stringify(settings));
  } catch {
    // ignore quota errors
  }
}
