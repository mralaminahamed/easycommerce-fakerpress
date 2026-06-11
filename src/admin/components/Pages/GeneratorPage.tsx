import React from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useState, useEffect } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

import { generators } from "@/admin/lib/generators";
import { fieldsFromSchema } from "@/admin/lib/fieldsFromSchema";
import { setPath } from "@/admin/lib/paths";
import { getSettings } from "@/admin/lib/settings";
import { useStats } from "@/admin/providers/StatsProvider";
import { useToast } from "@/admin/providers/ToastProvider";
import { useBatch } from "@/admin/providers/BatchProvider";
import { ConfigColumn } from "@/admin/components/generator/ConfigColumn";
import { PreviewTable } from "@/admin/components/generator/PreviewTable";
import { RunBar } from "@/admin/components/generator/RunBar";
import { Button } from "@/admin/components/ui/button";
import { Icon } from "@/admin/lib/icons";

import type { GeneratorPageParams, GeneratorResult } from "@/admin/types";

// ---------------------------------------------------------------------------
// Build default params from a generator's parameterConfig schema
// ---------------------------------------------------------------------------

function buildDefaultParams(
  parameterConfig: Record<string, any>,
): Record<string, any> {
  const sections = fieldsFromSchema(parameterConfig);
  let acc: Record<string, any> = {};

  for (const section of sections) {
    for (const f of section.fields) {
      if (f.default === undefined) {
        // For range fields use { lo: min, hi: max }
        if (f.type === "range") {
          acc = setPath(acc, f.key, { lo: f.min ?? 0, hi: f.max ?? 100 });
        } else if (f.type === "chips") {
          acc = setPath(acc, f.key, []);
        }
        // Skip other fields with no default
        continue;
      }

      if (f.type === "range") {
        // default for range is { lo, hi } derived from min/max when no explicit default
        acc = setPath(acc, f.key, f.default ?? { lo: f.min ?? 0, hi: f.max ?? 100 });
      } else if (f.type === "chips") {
        acc = setPath(acc, f.key, f.default ?? []);
      } else {
        acc = setPath(acc, f.key, f.default);
      }
    }
  }

  return acc;
}

// ---------------------------------------------------------------------------
// GeneratorPage
// ---------------------------------------------------------------------------

export default function GeneratorPage() {
  const { type } = useParams<GeneratorPageParams>();
  const navigate = useNavigate();
  const { recordRun } = useStats();
  const { toast } = useToast();
  const { add: addToBatch } = useBatch();

  const generator = generators.find((g) => g.route === type);

  // Run/meta state — re-initialise when generator route changes
  const settings = getSettings();
  const [count, setCount] = useState<number>(settings.defaultCount);
  const [seed, setSeed] = useState<string>(settings.defaultSeed);
  const [meta, setMeta] = useState<boolean>(settings.defaultIncludeMeta);
  const locale =
    getSettings().defaultLocale ??
    window.easycommerceFakerpressApi?.locale?.faker ??
    "en_US";

  // Generator-specific params — re-initialise on route change
  const [params, setParams] = useState<Record<string, any>>(() =>
    buildDefaultParams(generator?.parameterConfig ?? {}),
  );

  // Shuffle state (incremented to reseed the preview)
  const [shuffleN, setShuffleN] = useState<number>(0);

  // Generating animation state
  const [generating, setGenerating] = useState<boolean>(false);
  const [progress, setProgress] = useState<number>(0);

  // Reset everything when generator route changes
  useEffect(() => {
    if (!generator) return;
    const s = getSettings();
    setCount(s.defaultCount);
    setSeed(s.defaultSeed);
    setMeta(s.defaultIncludeMeta);
    setParams(buildDefaultParams(generator.parameterConfig ?? {}));
    setShuffleN(0);
    setGenerating(false);
    setProgress(0);
  }, [generator?.route]); // eslint-disable-line react-hooks/exhaustive-deps

  // Redirect if generator not found
  if (!generator) {
    navigate("/");
    return null;
  }

  // setField — immutable path update
  const setField = (key: string, value: any) => {
    setParams((prev) => setPath(prev, key, value));
  };

  // doGenerate — animate progress, POST to generate endpoint, record run
  const doGenerate = () => {
    if (generating) return;

    setGenerating(true);
    setProgress(0);

    const t0 = Date.now();
    const dur = 1000 + Math.min(count, 200) * 4;

    let raf: number;

    const tick = () => {
      const p = Math.min(1, (Date.now() - t0) / dur);
      setProgress(p);
      if (p < 1) {
        raf = requestAnimationFrame(tick);
      }
    };

    raf = requestAnimationFrame(tick);

    // setTimeout guarantees completion even if rAF is throttled in a background tab
    const timer = setTimeout(async () => {
      cancelAnimationFrame(raf);
      setProgress(1);

      const body: Record<string, any> = {
        count,
        locale,
        include_meta: meta,
        ...params,
      };

      if (seed.trim()) {
        body.seed = parseInt(seed, 10);
      }

      try {
        const data = (await apiFetch({
          path: `/easycommerce-fakerpress/v1/${generator.route}/generate`,
          method: "POST",
          data: body,
        })) as GeneratorResult;

        recordRun(generator.route, count, true, data.message ?? "", {
          locale,
          seed,
        });
        toast(
          sprintf(
            /* translators: %1$s: count, %2$s: generator name */
            __("Generated %1$s %2$s", "easycommerce-fakerpress"),
            count.toLocaleString(),
            generatorLabel,
          ),
          __("Added to your EasyCommerce store", "easycommerce-fakerpress"),
        );
      } catch (err) {
        const errMsg =
          err instanceof Error
            ? err.message
            : __("An error occurred.", "easycommerce-fakerpress");

        recordRun(generator.route, count, false, errMsg, { locale, seed });
        toast(__("Generation failed", "easycommerce-fakerpress"), errMsg);
      } finally {
        setGenerating(false);
      }
    }, dur);

    // Cleanup if component unmounts mid-flight (React StrictMode / navigation)
    return () => {
      clearTimeout(timer);
      cancelAnimationFrame(raf);
    };
  };

  const onAddBatch = () => {
    addToBatch(generator.route, count);
    toast(
      sprintf(
        /* translators: %1$s: count, %2$s: generator name */
        __("Added %1$s %2$s to batch", "easycommerce-fakerpress"),
        count.toLocaleString(),
        generatorLabel,
      ),
    );
  };

  const generatorLabel = generator.name.toLowerCase();

  return (
    <div className="fp-gen-main fp-enter">
      <div className="fp-gen-body">
        <div className="fp-gen-wrap">
          {/* ---- Left: config column ---- */}
          <ConfigColumn
            generator={generator}
            params={params}
            setField={setField}
          />

          {/* ---- Right: preview column ---- */}
          <div className="fp-preview-col">
            {/* Preview header */}
            <div className="fp-preview-head">
              <span className="fp-preview-title">
                <span className="fp-live-dot" />
                {__("Live preview", "easycommerce-fakerpress")}
              </span>
              <div className="fp-preview-actions">
                <Button
                  variant="ghost"
                  size="sm"
                  icon="refresh"
                  type="button"
                  onClick={() => setShuffleN((n) => n + 1)}
                >
                  {__("Shuffle", "easycommerce-fakerpress")}
                </Button>
              </div>
            </div>

            <p className="fp-preview-note">
              {__(
                "Sample of what this run will create — updates as you change the settings.",
                "easycommerce-fakerpress",
              )}
            </p>

            {/* Preview area */}
            <div
              style={{
                position: "relative",
                flex: 1,
                minHeight: 0,
                display: "flex",
              }}
            >
              <PreviewTable
                route={generator.route}
                params={params}
                count={count}
                seed={seed}
                meta={meta}
                locale={locale}
                shuffleN={shuffleN}
              />

              {/* Generating overlay */}
              {generating && (
                <div className="fp-gen-progress">
                  <div className="fp-spinner" />
                  <div style={{ fontWeight: 550, fontSize: 14 }}>
                    {sprintf(
                      /* translators: %1$s: count, %2$s: generator name */
                      __("Generating %1$s %2$s…", "easycommerce-fakerpress"),
                      count.toLocaleString(),
                      generatorLabel,
                    )}
                  </div>
                  <div className="fp-progress-track">
                    <div
                      className="fp-progress-fill"
                      style={{ width: `${progress * 100}%` }}
                    />
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* ---- Sticky run bar ---- */}
      <RunBar
        count={count}
        seed={seed}
        meta={meta}
        onCount={setCount}
        onSeed={setSeed}
        onMeta={setMeta}
        onGenerate={doGenerate}
        onAddBatch={onAddBatch}
        generating={generating}
      />
    </div>
  );
}
