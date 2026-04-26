import { useState, useCallback, useEffect } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import {
  Save,
  Trash2,
  RefreshCw,
  GitBranch,
  ExternalLink,
  CheckCircle,
  AlertCircle,
  Info,
  Database,
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

const PLUGIN_VERSION = "2.0.4";
const GITHUB_URL = "https://github.com/mralaminahamed/easycommerce-fakerpress";
const SAMPLE_DATA_REPO_URL =
  "https://github.com/mralaminahamed/easycommerce-fakerpress-sample-data";
const SUPPORT_URL =
  "https://github.com/mralaminahamed/easycommerce-fakerpress/issues";
const DOCS_URL =
  "https://github.com/mralaminahamed/easycommerce-fakerpress#readme";

interface SyncStatus {
  exists: boolean;
  last_synced: string | null;
  repo_url: string;
}

export default function SettingsPage() {
  const [settings, setSettings] = useState(getSettings);
  const [saved, setSaved] = useState(false);

  // Sample data sync state
  const [syncStatus, setSyncStatus] = useState<SyncStatus | null>(null);
  const [statusLoading, setStatusLoading] = useState(true);
  const [syncing, setSyncing] = useState(false);
  const [syncResult, setSyncResult] = useState<{
    ok: boolean;
    message: string;
  } | null>(null);

  const nonce = window.easycommerceFakerpressApi?.restNonce ?? "";
  const restUrl = window.easycommerceFakerpressApi?.restUrl ?? "";
  const allLocales =
    window.easycommerceFakerpressApi?.locale?.allLocales ?? {};

  // Fetch sync status on mount
  useEffect(() => {
    fetch(`${restUrl}download-sample`, {
      headers: { "X-WP-Nonce": nonce },
    })
      .then((r) => r.json())
      .then((data: SyncStatus) => setSyncStatus(data))
      .catch(() => setSyncStatus(null))
      .finally(() => setStatusLoading(false));
  }, [restUrl, nonce]);

  const handleSave = () => {
    saveSettings(settings);
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  };

  const handleSync = useCallback(
    async (force = false) => {
      setSyncing(true);
      setSyncResult(null);
      try {
        const res = await fetch(`${restUrl}download-sample`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": nonce,
          },
          body: JSON.stringify({ force }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json?.message ?? `HTTP ${res.status}`);
        setSyncResult({ ok: true, message: json.message });
        // Refresh status
        const statusRes = await fetch(`${restUrl}download-sample`, {
          headers: { "X-WP-Nonce": nonce },
        });
        if (statusRes.ok) setSyncStatus(await statusRes.json());
      } catch (err) {
        setSyncResult({
          ok: false,
          message:
            err instanceof Error
              ? err.message
              : __("Sync failed.", "easycommerce-fakerpress"),
        });
      } finally {
        setSyncing(false);
      }
    },
    [restUrl, nonce],
  );

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

  const formatDate = (iso: string | null) => {
    if (!iso) return null;
    try {
      return new Date(iso).toLocaleDateString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    } catch {
      return iso;
    }
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
                placeholder={__(
                  "random (leave blank)",
                  "easycommerce-fakerpress",
                )}
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
                  {__(
                    "Include Metadata by Default",
                    "easycommerce-fakerpress",
                  )}
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

        {/* Sample data sync */}
        <div className="bg-white rounded-xl border border-gray-200 p-5">
          <div className="flex items-start gap-3 mb-4">
            <div className="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
              <Database className="w-4 h-4 text-indigo-500" />
            </div>
            <div>
              <h2 className="text-sm font-semibold text-gray-900">
                {__("Sample Data", "easycommerce-fakerpress")}
              </h2>
              <p className="text-xs text-gray-400 mt-0.5">
                {__(
                  "Locale-specific reference data (product names, customer tags, addresses, etc.) used by generators to produce realistic output.",
                  "easycommerce-fakerpress",
                )}
              </p>
            </div>
          </div>

          {/* Status */}
          <div className="rounded-lg bg-gray-50 border border-gray-100 p-3 mb-4 flex items-center gap-3">
            {statusLoading ? (
              <RefreshCw className="w-4 h-4 text-gray-400 animate-spin shrink-0" />
            ) : syncStatus?.exists ? (
              <CheckCircle className="w-4 h-4 text-green-500 shrink-0" />
            ) : (
              <AlertCircle className="w-4 h-4 text-amber-500 shrink-0" />
            )}
            <div className="min-w-0">
              {statusLoading ? (
                <p className="text-sm text-gray-500">
                  {__("Checking status…", "easycommerce-fakerpress")}
                </p>
              ) : syncStatus?.exists ? (
                <>
                  <p className="text-sm font-medium text-gray-900">
                    {__("Sample data is synced", "easycommerce-fakerpress")}
                  </p>
                  {syncStatus.last_synced && (
                    <p className="text-xs text-gray-400">
                      {sprintf(
                        /* translators: %s: date string */
                        __("Last updated: %s", "easycommerce-fakerpress"),
                        formatDate(syncStatus.last_synced),
                      )}
                    </p>
                  )}
                </>
              ) : (
                <>
                  <p className="text-sm font-medium text-gray-900">
                    {__("Sample data not found", "easycommerce-fakerpress")}
                  </p>
                  <p className="text-xs text-gray-400">
                    {__(
                      "Sync to download locale-specific reference data.",
                      "easycommerce-fakerpress",
                    )}
                  </p>
                </>
              )}
            </div>
          </div>

          {/* Sync result */}
          {syncResult && (
            <div
              className={`flex items-center gap-2 rounded-md p-3 mb-4 text-sm ${
                syncResult.ok
                  ? "bg-green-50 border border-green-200 text-green-700"
                  : "bg-red-50 border border-red-200 text-red-700"
              }`}
            >
              {syncResult.ok ? (
                <CheckCircle className="w-4 h-4 shrink-0" />
              ) : (
                <AlertCircle className="w-4 h-4 shrink-0" />
              )}
              {syncResult.message}
            </div>
          )}

          <div className="flex flex-wrap gap-2 mb-4">
            <Button
              onClick={() => handleSync(false)}
              disabled={syncing}
              className="gap-2"
            >
              <RefreshCw
                className={`w-4 h-4 ${syncing ? "animate-spin" : ""}`}
              />
              {syncing
                ? __("Syncing…", "easycommerce-fakerpress")
                : __("Sync Now", "easycommerce-fakerpress")}
            </Button>
            <Button
              variant="outline"
              onClick={() => handleSync(true)}
              disabled={syncing}
              className="gap-2"
            >
              <RefreshCw className="w-4 h-4" />
              {__("Force Re-sync", "easycommerce-fakerpress")}
            </Button>
          </div>

          <div className="pt-3 border-t border-gray-100">
            <a
              href={SAMPLE_DATA_REPO_URL}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-900 transition-colors"
            >
              <GitBranch className="w-3.5 h-3.5" />
              {__("View sample data repository", "easycommerce-fakerpress")}
              <ExternalLink className="w-3 h-3" />
            </a>
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
          <div className="flex flex-wrap gap-6">
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
