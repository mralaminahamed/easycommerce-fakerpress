import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Save, Trash2 } from "lucide-react";
import { Button } from "@/admin/components/ui/button";
import { Input } from "@/admin/components/ui/input";
import { Label } from "@/admin/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/admin/components/ui/select";
import { getSettings, saveSettings } from "@/admin/lib/settings";

export default function SettingsPage() {
  const [settings, setSettings] = useState(getSettings);
  const [saved, setSaved] = useState(false);

  const allLocales =
    window.easycommerceFakerpressApi?.locale?.allLocales ?? {};

  const handleSave = () => {
    saveSettings(settings);
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  };

  const handleClearData = () => {
    if (
      !confirm(
        __(
          "Clear all locally stored run history and stats? This cannot be undone.",
          "easycommerce-fakerpress",
        ),
      )
    )
      return;

    const keysToRemove: string[] = [];
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key?.startsWith("ec_fp_") && key !== "ec_fp_settings") {
        keysToRemove.push(key);
      }
    }
    keysToRemove.forEach((k) => localStorage.removeItem(k));
    window.location.reload();
  };

  return (
    <div className="p-6 max-w-2xl">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">
          {__("Settings", "easycommerce-fakerpress")}
        </h1>
        <p className="text-sm text-gray-500 mt-1">
          {__(
            "Configure default behaviour for data generation.",
            "easycommerce-fakerpress",
          )}
        </p>
      </div>

      <div className="space-y-6">
        {/* Generation defaults */}
        <div className="bg-white rounded-xl border border-gray-200 p-5">
          <h2 className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-4">
            {__("Generation Defaults", "easycommerce-fakerpress")}
          </h2>

          <div className="space-y-5">
            <div className="space-y-1.5">
              <Label className="text-sm font-medium text-gray-700">
                {__("Default Count", "easycommerce-fakerpress")}
              </Label>
              <p className="text-xs text-gray-400">
                {__(
                  "Number of items pre-filled on every generator page.",
                  "easycommerce-fakerpress",
                )}
              </p>
              <Input
                type="number"
                min={1}
                max={100}
                value={settings.defaultCount}
                className="w-32"
                onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                  setSettings((s) => ({
                    ...s,
                    defaultCount: Math.min(
                      100,
                      Math.max(1, parseInt(e.target.value, 10) || 1),
                    ),
                  }))
                }
              />
            </div>

            <div className="space-y-1.5">
              <Label className="text-sm font-medium text-gray-700">
                {__("Default Locale", "easycommerce-fakerpress")}
              </Label>
              <p className="text-xs text-gray-400">
                {__(
                  "Faker locale used when generating data.",
                  "easycommerce-fakerpress",
                )}
              </p>
              <Select
                value={settings.defaultLocale}
                onValueChange={(v) =>
                  setSettings((s) => ({ ...s, defaultLocale: v }))
                }
              >
                <SelectTrigger className="w-64">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {Object.entries(allLocales).map(([code, label]) => (
                    <SelectItem key={code} value={code}>
                      {label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="mt-5 pt-4 border-t border-gray-100">
            <Button onClick={handleSave} className="gap-2">
              <Save className="w-4 h-4" />
              {saved
                ? __("Saved!", "easycommerce-fakerpress")
                : __("Save Settings", "easycommerce-fakerpress")}
            </Button>
          </div>
        </div>

        {/* Danger zone */}
        <div className="bg-white rounded-xl border border-red-200 p-5">
          <h2 className="text-xs font-semibold uppercase tracking-wide text-red-500 mb-1">
            {__("Danger Zone", "easycommerce-fakerpress")}
          </h2>
          <p className="text-xs text-gray-500 mb-4">
            {__("These actions cannot be undone.", "easycommerce-fakerpress")}
          </p>
          <Button
            variant="destructive"
            onClick={handleClearData}
            className="gap-2"
          >
            <Trash2 className="w-4 h-4" />
            {__("Clear Run History & Stats", "easycommerce-fakerpress")}
          </Button>
          <p className="text-xs text-gray-400 mt-2">
            {__(
              "Removes locally stored generation stats and run history. Does not delete data in your database.",
              "easycommerce-fakerpress",
            )}
          </p>
        </div>
      </div>
    </div>
  );
}
