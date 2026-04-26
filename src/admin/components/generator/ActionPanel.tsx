import apiFetch from "@wordpress/api-fetch";
import { useState } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import { Minus, Plus } from "lucide-react";
import { Button } from "@/admin/components/ui/button";
import { Input } from "@/admin/components/ui/input";
import { Label } from "@/admin/components/ui/label";
import { Progress } from "@/admin/components/ui/progress";
import { Switch } from "@/admin/components/ui/switch";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/admin/components/ui/select";
import { addRun, getRuns, incrementStats } from "@/admin/lib/storage";
import type { Generator, GeneratorResult, StoredRun } from "@/admin/types";

interface ActionPanelProps {
  generator: Generator;
  count: number;
  locale: string;
  seed: string;
  includeMeta: boolean;
  onCountChange: (count: number) => void;
  onLocaleChange: (locale: string) => void;
  onSeedChange: (seed: string) => void;
  onIncludeMetaChange: (val: boolean) => void;
  extraParams: Record<string, any>;
}

function timeAgo(timestamp: number): string {
  const seconds = Math.floor((Date.now() - timestamp) / 1000);
  if (seconds < 60) return "just now";
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
  return `${Math.floor(seconds / 86400)}d ago`;
}

export function ActionPanel({
  generator,
  count,
  locale,
  seed,
  includeMeta,
  onCountChange,
  onLocaleChange,
  onSeedChange,
  onIncludeMetaChange,
  extraParams,
}: ActionPanelProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [result, setResult] = useState<GeneratorResult | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [recentRun, setRecentRun] = useState<StoredRun | null>(
    getRuns(generator.route)[0] ?? null,
  );

  const handleGenerate = async () => {
    setIsLoading(true);
    setError(null);
    setResult(null);

    const params: Record<string, any> = {
      count,
      locale,
      include_meta: includeMeta,
      ...extraParams,
    };
    if (seed.trim()) {
      params.seed = parseInt(seed, 10);
    }

    try {
      const data = (await apiFetch({
        path: `/easycommerce-fakerpress/v1/${generator.route}/generate`,
        method: "POST",
        data: params,
      })) as GeneratorResult;

      setResult(data);
      const run: StoredRun = {
        count,
        timestamp: Date.now(),
        success: true,
        message: data.message,
      };
      addRun(generator.route, run);
      incrementStats(generator.route, count);
      setRecentRun(run);
    } catch (err) {
      const msg =
        err instanceof Error
          ? err.message
          : __("An error occurred.", "easycommerce-fakerpress");
      setError(msg);
      const run: StoredRun = {
        count,
        timestamp: Date.now(),
        success: false,
        message: msg,
      };
      addRun(generator.route, run);
      setRecentRun(run);
    } finally {
      setIsLoading(false);
    }
  };

  const allLocales = window.easycommerceFakerpressApi?.locale?.allLocales ?? {};

  return (
    <div data-testid="action-panel" className="space-y-5 sticky top-8">
      {/* Count stepper */}
      <div className="space-y-1.5">
        <Label className="text-sm font-medium text-gray-700">
          {__("Count", "easycommerce-fakerpress")}
        </Label>
        <div className="flex items-center gap-2">
          <Button
            type="button"
            variant="outline"
            size="icon"
            data-testid="count-decrement"
            disabled={isLoading || count <= 1}
            onClick={() => onCountChange(Math.max(1, count - 1))}
          >
            <Minus className="w-4 h-4" />
          </Button>
          <Input
            type="number"
            value={count}
            min={1}
            max={100}
            disabled={isLoading}
            data-testid="count-input"
            className="w-20 text-center"
            onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
              onCountChange(
                Math.min(100, Math.max(1, parseInt(e.target.value, 10) || 1)),
              )
            }
          />
          <Button
            type="button"
            variant="outline"
            size="icon"
            data-testid="count-increment"
            disabled={isLoading || count >= 100}
            onClick={() => onCountChange(Math.min(100, count + 1))}
          >
            <Plus className="w-4 h-4" />
          </Button>
        </div>
      </div>

      {/* Locale */}
      <div className="space-y-1.5">
        <Label className="text-sm font-medium text-gray-700">
          {__("Locale", "easycommerce-fakerpress")}
        </Label>
        <Select value={locale} onValueChange={onLocaleChange} disabled={isLoading}>
          <SelectTrigger className="w-full">
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

      {/* Seed */}
      <div className="space-y-1.5">
        <Label className="text-sm font-medium text-gray-700">
          {__("Seed", "easycommerce-fakerpress")}
        </Label>
        <Input
          value={seed}
          placeholder={__("random (leave blank)", "easycommerce-fakerpress")}
          disabled={isLoading}
          onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
            onSeedChange(e.target.value)
          }
        />
      </div>

      {/* Include metadata */}
      <div className="flex items-center gap-3">
        <Switch
          id="action-include-meta"
          checked={includeMeta}
          onCheckedChange={onIncludeMetaChange}
          disabled={isLoading}
        />
        <Label
          htmlFor="action-include-meta"
          className="text-sm font-medium text-gray-700 cursor-pointer"
        >
          {__("Include metadata", "easycommerce-fakerpress")}
        </Label>
      </div>

      {/* Generate */}
      {isLoading ? (
        <Progress />
      ) : (
        <Button onClick={handleGenerate} size="lg" data-testid="generate-btn" className="w-full">
          {__("Generate", "easycommerce-fakerpress")}
        </Button>
      )}

      {/* Result */}
      {result && (
        <div data-testid="result-success" className="rounded-md bg-green-50 border border-green-200 p-3">
          <p className="text-sm font-medium text-green-800 m-0">
            {result.message}
          </p>
        </div>
      )}
      {error && (
        <div data-testid="result-error" className="rounded-md bg-red-50 border border-red-200 p-3">
          <p className="text-sm font-medium text-red-800 m-0">{error}</p>
        </div>
      )}

      {/* Last run — only shown when no current result/error */}
      {recentRun && !result && !error && (
        <div className="rounded-md bg-gray-50 border border-gray-200 p-3">
          <p className="text-xs text-gray-400 mb-1">
            {__("Last run", "easycommerce-fakerpress")}
          </p>
          <p
            className={`text-sm font-medium ${recentRun.success ? "text-green-700" : "text-red-700"}`}
          >
            {recentRun.success ? "✓" : "✗"}{" "}
            {recentRun.success
              ? sprintf(
                  /* translators: %d: count */
                  __("%d items", "easycommerce-fakerpress"),
                  recentRun.count,
                )
              : __("Error", "easycommerce-fakerpress")}{" "}
            · {timeAgo(recentRun.timestamp)}
          </p>
        </div>
      )}
    </div>
  );
}
