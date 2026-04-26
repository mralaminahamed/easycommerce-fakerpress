import { useState, useCallback } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import {
  Save,
  Trash2,
  RefreshCw,
  GitBranch,
  ExternalLink,
  CheckCircle,
  XCircle,
  Play,
  Info,
} from "lucide-react";
import { Button } from "@/admin/components/ui/button";
import { Input } from "@/admin/components/ui/input";
import { Label } from "@/admin/components/ui/label";
import { Switch } from "@/admin/components/ui/switch";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/admin/components/ui/select";
import { getSettings, saveSettings } from "@/admin/lib/settings";
import type { SampleDataManifest, SampleDataset } from "@/admin/types";

const PLUGIN_VERSION = "2.0.4";
const GITHUB_URL = "https://github.com/mralaminahamed/easycommerce-fakerpress";
const SUPPORT_URL =
  "https://github.com/mralaminahamed/easycommerce-fakerpress/issues";
const DOCS_URL =
  "https://github.com/mralaminahamed/easycommerce-fakerpress#readme";

type RunState = "idle" | "running" | "done" | "error";

interface DatasetRunState {
  state: RunState;
  message?: string;
}

export default function SettingsPage() {
  const [settings, setSettings] = useState(getSettings);
  const [saved, setSaved] = useState(false);

  // Sample data state
  const [syncing, setSyncing] = useState(false);
  const [syncError, setSyncError] = useState<string | null>(null);
  const [manifest, setManifest] = useState<SampleDataManifest | null>(null);
  const [runStates, setRunStates] = useState<Record<string, DatasetRunState>>(
    {},
  );

  const allLocales =
    window.easycommerceFakerpressApi?.locale?.allLocales ?? {};
  const nonce = window.easycommerceFakerpressApi?.restNonce ?? "";
  const restUrl = window.easycommerceFakerpressApi?.restUrl ?? "";

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

  const handleClearSettings = () => {
    if (
      !confirm(
        __(
          "Reset all settings to defaults? This cannot be undone.",
          "easycommerce-fakerpress",
        ),
      )
    )
      return;
    localStorage.removeItem("ec_fp_settings");
    window.location.reload();
  };

  const handleSync = useCallback(async () => {
    setSyncing(true);
    setSyncError(null);
    setManifest(null);
    setRunStates({});
    try {
      const res = await fetch(settings.sampleDataUrl);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = (await res.json()) as SampleDataManifest;
      if (!Array.isArray(data.datasets)) throw new Error("Invalid manifest");
      setManifest(data);
    } catch (err) {
      setSyncError(
        err instanceof Error ? err.message : __("Fetch failed", "easycommerce-fakerpress"),
      );
    } finally {
      setSyncing(false);
    }
  }, [settings.sampleDataUrl]);

  const handleRunDataset = useCallback(
    async (dataset: SampleDataset) => {
      setRunStates((s) => ({ ...s, [dataset.id]: { state: "running" } }));
      try {
        const body: Record<string, any> = {
          count: dataset.count,
          locale: settings.defaultLocale,
          include_meta: settings.defaultIncludeMeta,
          ...(dataset.params ?? {}),
        };
        if (settings.defaultSeed) body.seed = parseInt(settings.defaultSeed, 10);

        const res = await fetch(
          `${restUrl}${dataset.generator}/generate`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-WP-Nonce": nonce,
            },
            body: JSON.stringify(body),
          },
        );
        const json = await res.json();
        if (!res.ok) throw new Error(json?.message ?? `HTTP ${res.status}`);
        setRunStates((s) => ({
          ...s,
          [dataset.id]: { state: "done", message: json.message },
        }));
      } catch (err) {
        setRunStates((s) => ({
          ...s,
          [dataset.id]: {
            state: "error",
            message:
              err instanceof Error
                ? err.message
                : __("Generation failed.", "easycommerce-fakerpress"),
          },
        }));
      }
    },
    [restUrl, nonce, settings],
  );

  const handleRunAll = useCallback(async () => {
    if (!manifest) return;
    for (const dataset of manifest.datasets) {
      await handleRunDataset(dataset);
    }
  }, [manifest, handleRunDataset]);

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
            {/* Default count */}
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

            {/* Default locale */}
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

            {/* Default seed */}
            <div className="space-y-1.5">
              <Label className="text-sm font-medium text-gray-700">
                {__("Default Seed", "easycommerce-fakerpress")}
              </Label>
              <p className="text-xs text-gray-400">
                {__(
                  "Fixed seed for reproducible runs. Leave blank for random output each time.",
                  "easycommerce-fakerpress",
                )}
              </p>
              <Input
                type="number"
                value={settings.defaultSeed}
                placeholder={__("random (leave blank)", "easycommerce-fakerpress")}
                className="w-48"
                onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                  setSettings((s) => ({ ...s, defaultSeed: e.target.value }))
                }
              />
            </div>

            {/* Include metadata */}
            <div className="flex items-start gap-3">
              <Switch
                id="settings-include-meta"
                checked={settings.defaultIncludeMeta}
                onCheckedChange={(v) =>
                  setSettings((s) => ({ ...s, defaultIncludeMeta: v }))
                }
                className="mt-0.5"
              />
              <div>
                <Label
                  htmlFor="settings-include-meta"
                  className="text-sm font-medium text-gray-700 cursor-pointer"
                >
                  {__("Include Metadata by Default", "easycommerce-fakerpress")}
                </Label>
                <p className="text-xs text-gray-400 mt-0.5">
                  {__(
                    "Pre-check the Include Metadata toggle on every generator.",
                    "easycommerce-fakerpress",
                  )}
                </p>
              </div>
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

        {/* Run history */}
        <div className="bg-white rounded-xl border border-gray-200 p-5">
          <h2 className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-4">
            {__("Run History", "easycommerce-fakerpress")}
          </h2>

          <div className="space-y-1.5">
            <Label className="text-sm font-medium text-gray-700">
              {__("Max Runs Per Generator", "easycommerce-fakerpress")}
            </Label>
            <p className="text-xs text-gray-400">
              {__(
                "How many recent runs to store in the sidebar history per generator type.",
                "easycommerce-fakerpress",
              )}
            </p>
            <Input
              type="number"
              min={5}
              max={50}
              value={settings.maxRunsPerGenerator}
              className="w-32"
              onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                setSettings((s) => ({
                  ...s,
                  maxRunsPerGenerator: Math.min(
                    50,
                    Math.max(5, parseInt(e.target.value, 10) || 10),
                  ),
                }))
              }
            />
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

        {/* Sample data */}
        <div className="bg-white rounded-xl border border-gray-200 p-5">
          <h2 className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">
            {__("Sample Data", "easycommerce-fakerpress")}
          </h2>
          <p className="text-xs text-gray-500 mb-4">
            {__(
              "Fetch a sample data manifest from GitHub and run presets directly.",
              "easycommerce-fakerpress",
            )}
          </p>

          <div className="space-y-3">
            <div className="space-y-1.5">
              <Label className="text-sm font-medium text-gray-700">
                {__("Manifest URL", "easycommerce-fakerpress")}
              </Label>
              <div className="flex gap-2">
                <Input
                  value={settings.sampleDataUrl}
                  className="flex-1 font-mono text-xs"
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                    setSettings((s) => ({ ...s, sampleDataUrl: e.target.value }))
                  }
                />
                <Button
                  variant="outline"
                  onClick={handleSync}
                  disabled={syncing || !settings.sampleDataUrl}
                  className="gap-2 shrink-0"
                >
                  <RefreshCw
                    className={`w-4 h-4 ${syncing ? "animate-spin" : ""}`}
                  />
                  {syncing
                    ? __("Syncing…", "easycommerce-fakerpress")
                    : __("Sync Now", "easycommerce-fakerpress")}
                </Button>
              </div>
            </div>

            {syncError && (
              <div className="flex items-center gap-2 rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                <XCircle className="w-4 h-4 shrink-0" />
                {syncError}
              </div>
            )}

            {manifest && (
              <div className="space-y-3 pt-1">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-900">
                      {manifest.name}
                    </p>
                    {manifest.description && (
                      <p className="text-xs text-gray-400 mt-0.5">
                        {manifest.description}
                      </p>
                    )}
                  </div>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={handleRunAll}
                    disabled={Object.values(runStates).some(
                      (r) => r.state === "running",
                    )}
                    className="gap-1.5 shrink-0"
                  >
                    <Play className="w-3.5 h-3.5" />
                    {__("Run All", "easycommerce-fakerpress")}
                  </Button>
                </div>

                <div className="divide-y divide-gray-100 border border-gray-100 rounded-lg overflow-hidden">
                  {manifest.datasets.map((ds) => {
                    const rs = runStates[ds.id] ?? { state: "idle" };
                    return (
                      <div
                        key={ds.id}
                        className="flex items-center gap-3 px-4 py-3 bg-white"
                      >
                        <div className="flex-1 min-w-0">
                          <p className="text-sm font-medium text-gray-900 truncate">
                            {ds.label}
                          </p>
                          {ds.description && (
                            <p className="text-xs text-gray-400 truncate">
                              {ds.description}
                            </p>
                          )}
                          {rs.state === "done" && (
                            <p className="text-xs text-green-600 mt-0.5">
                              {rs.message}
                            </p>
                          )}
                          {rs.state === "error" && (
                            <p className="text-xs text-red-600 mt-0.5">
                              {rs.message}
                            </p>
                          )}
                        </div>
                        <div className="flex items-center gap-2 shrink-0">
                          <span className="text-xs text-gray-400">
                            {sprintf(
                              /* translators: %d: item count */
                              __("%d items", "easycommerce-fakerpress"),
                              ds.count,
                            )}
                          </span>
                          {rs.state === "done" ? (
                            <CheckCircle className="w-4 h-4 text-green-500" />
                          ) : rs.state === "error" ? (
                            <XCircle className="w-4 h-4 text-red-500" />
                          ) : (
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => handleRunDataset(ds)}
                              disabled={rs.state === "running"}
                              className="gap-1.5 h-7 px-2 text-xs"
                            >
                              <Play
                                className={`w-3 h-3 ${rs.state === "running" ? "animate-pulse" : ""}`}
                              />
                              {rs.state === "running"
                                ? __("Running…", "easycommerce-fakerpress")
                                : __("Run", "easycommerce-fakerpress")}
                            </Button>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* About */}
        <div className="bg-white rounded-xl border border-gray-200 p-5">
          <h2 className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-4">
            {__("About", "easycommerce-fakerpress")}
          </h2>

          <div className="flex items-start gap-3 mb-4">
            <div className="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
              <Info className="w-5 h-5 text-blue-500" />
            </div>
            <div>
              <p className="text-sm font-semibold text-gray-900">
                {__("EasyCommerce FakerPress", "easycommerce-fakerpress")}
              </p>
              <p className="text-xs text-gray-400">
                {sprintf(
                  /* translators: %s: version number */
                  __("Version %s", "easycommerce-fakerpress"),
                  PLUGIN_VERSION,
                )}
              </p>
            </div>
          </div>

          <div className="flex flex-wrap gap-2">
            <a
              href={GITHUB_URL}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-gray-900 border border-gray-200 rounded-md px-3 py-1.5 hover:border-gray-300 transition-colors"
            >
              <GitBranch className="w-3.5 h-3.5" />
              {__("GitHub", "easycommerce-fakerpress")}
            </a>
            <a
              href={DOCS_URL}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-gray-900 border border-gray-200 rounded-md px-3 py-1.5 hover:border-gray-300 transition-colors"
            >
              <ExternalLink className="w-3.5 h-3.5" />
              {__("Documentation", "easycommerce-fakerpress")}
            </a>
            <a
              href={SUPPORT_URL}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-gray-900 border border-gray-200 rounded-md px-3 py-1.5 hover:border-gray-300 transition-colors"
            >
              <ExternalLink className="w-3.5 h-3.5" />
              {__("Support", "easycommerce-fakerpress")}
            </a>
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
          <div className="flex flex-wrap gap-3">
            <div>
              <Button
                variant="destructive"
                onClick={handleClearData}
                className="gap-2"
              >
                <Trash2 className="w-4 h-4" />
                {__("Clear Run History & Stats", "easycommerce-fakerpress")}
              </Button>
              <p className="text-xs text-gray-400 mt-1.5">
                {__(
                  "Removes locally stored generation stats and run history. Does not delete data in your database.",
                  "easycommerce-fakerpress",
                )}
              </p>
            </div>
            <div>
              <Button
                variant="outline"
                onClick={handleClearSettings}
                className="gap-2 border-red-200 text-red-600 hover:bg-red-50"
              >
                <Trash2 className="w-4 h-4" />
                {__("Reset Settings to Defaults", "easycommerce-fakerpress")}
              </Button>
              <p className="text-xs text-gray-400 mt-1.5">
                {__(
                  "Resets all settings to their default values.",
                  "easycommerce-fakerpress",
                )}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
