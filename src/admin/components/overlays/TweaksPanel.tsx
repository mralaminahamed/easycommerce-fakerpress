import React from "react";
import { __ } from "@wordpress/i18n";

import { useTheme, type Accent, type Theme, type Density } from "@/admin/theme/useTheme";
import { Icon } from "@/admin/lib/icons";

interface TweaksPanelProps {
  onClose: () => void;
}

const ACCENTS: { id: Accent; c: string }[] = [
  { id: "indigo", c: "oklch(0.55 0.205 277)" },
  { id: "blue", c: "oklch(0.55 0.205 256)" },
  { id: "violet", c: "oklch(0.56 0.225 300)" },
  { id: "emerald", c: "oklch(0.60 0.145 162)" },
  { id: "amber", c: "oklch(0.72 0.155 66)" },
];

/** Tokens the user can recolor. `token` is the CSS var name without `--`. */
const CUSTOM_TOKENS: { token: string; label: string }[] = [
  { token: "accent", label: __("Accent", "easycommerce-fakerpress") },
  { token: "bg-2", label: __("Background", "easycommerce-fakerpress") },
  { token: "surface", label: __("Surface", "easycommerce-fakerpress") },
  { token: "text", label: __("Text", "easycommerce-fakerpress") },
  { token: "border", label: __("Border", "easycommerce-fakerpress") },
  { token: "green", label: __("Success", "easycommerce-fakerpress") },
  { token: "amber", label: __("Warning", "easycommerce-fakerpress") },
  { token: "red", label: __("Danger", "easycommerce-fakerpress") },
];

const HEX = (n: number) => n.toString(16).padStart(2, "0");

/**
 * Resolve a design token's *base* value (for the given theme/accent) to a
 * #rrggbb string for `<input type="color">`.
 *
 * Reads from a detached `.fp-root` probe rather than the live app root, so the
 * result is the pure theme color — unaffected by any active custom override and
 * safe to call during render (no dependency on a committed inline style). The
 * tokens are authored in oklch(), which the color input can't display, so a 1×1
 * canvas rasterizes the value to sRGB (handles oklch/color-mix/hex/rgb alike).
 */
function resolveTokenHex(token: string, theme: string, accent: string): string {
  if (typeof document === "undefined") return "#000000";

  const probe = document.createElement("div");
  probe.className = "fp-root";
  probe.setAttribute("data-theme", theme);
  probe.setAttribute("data-accent", accent);
  probe.style.display = "none";
  document.body.appendChild(probe);
  const raw = getComputedStyle(probe).getPropertyValue(`--${token}`).trim();
  probe.remove();

  if (!raw) return "#000000";
  if (/^#[0-9a-f]{6}$/i.test(raw)) return raw.toLowerCase();

  const canvas = document.createElement("canvas");
  canvas.width = 1;
  canvas.height = 1;
  const ctx = canvas.getContext("2d", { willReadFrequently: true });
  if (!ctx) return "#000000";
  // A valid fillStyle assignment sticks; an invalid one leaves the prior value,
  // so seed with black first to fail safe if the browser rejected the token.
  ctx.fillStyle = "#000000";
  ctx.fillStyle = raw;
  ctx.fillRect(0, 0, 1, 1);
  const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
  return "#" + HEX(r) + HEX(g) + HEX(b);
}

interface SegOption<T> {
  v: T;
  label: string;
  ic?: string;
}

function Seg<T extends string>({
  value,
  options,
  onChange,
}: {
  value: T;
  options: SegOption<T>[];
  onChange: (v: T) => void;
}) {
  return (
    <div className="fp-seg">
      {options.map((o) => (
        <button
          key={o.v}
          type="button"
          className={`fp-seg-btn${value === o.v ? " on" : ""}`}
          onClick={() => onChange(o.v)}
        >
          {o.ic && <Icon name={o.ic} size={15} />}
          {o.label}
        </button>
      ))}
    </div>
  );
}

export function TweaksPanel({ onClose }: TweaksPanelProps) {
  const {
    theme,
    setTheme,
    accent,
    setAccent,
    density,
    setDensity,
    customColors,
    setCustomColor,
    resetCustomColors,
  } = useTheme();

  return (
    <>
      <div
        className="fp-overlay"
        style={{ background: "transparent", backdropFilter: "none" }}
        onMouseDown={onClose}
      />
      <aside className="fp-tweaks" data-testid="tweaks-panel">
        <div className="fp-tweaks-head">
          <Icon name="sliders" size={17} />
          <span className="fp-tweaks-title">
            {__("Tweaks", "easycommerce-fakerpress")}
          </span>
          <button
            type="button"
            className="fp-icon-btn"
            style={{ marginLeft: "auto", width: 30, height: 30, border: "none" }}
            aria-label={__("Close", "easycommerce-fakerpress")}
            onClick={onClose}
          >
            <Icon name="x" size={17} />
          </button>
        </div>
        <div className="fp-tweaks-body">
          {/* Sections ordered by how often they're changed: appearance (most),
              then accent, density, and custom colors (power-user, least). */}
          <div className="fp-tweak-sec">
            <div className="fp-tweak-label">
              {__("Appearance", "easycommerce-fakerpress")}
            </div>
            <Seg<Theme>
              value={theme}
              onChange={setTheme}
              options={[
                { v: "light", label: __("Light", "easycommerce-fakerpress"), ic: "sun" },
                { v: "dark", label: __("Dark", "easycommerce-fakerpress"), ic: "moon" },
              ]}
            />
          </div>
          <div className="fp-tweak-sec">
            <div className="fp-tweak-label">
              {__("Accent color", "easycommerce-fakerpress")}
            </div>
            <div className="fp-swatches">
              {ACCENTS.map((a) => (
                <button
                  key={a.id}
                  type="button"
                  className={`fp-swatch${accent === a.id ? " sel" : ""}`}
                  style={{ background: a.c }}
                  onClick={() => setAccent(a.id)}
                  title={a.id}
                  aria-label={a.id}
                />
              ))}
            </div>
          </div>
          <div className="fp-tweak-sec">
            <div className="fp-tweak-label">
              {__("Density", "easycommerce-fakerpress")}
            </div>
            <Seg<Density>
              value={density}
              onChange={setDensity}
              options={[
                { v: "comfortable", label: __("Comfortable", "easycommerce-fakerpress") },
                { v: "compact", label: __("Compact", "easycommerce-fakerpress") },
              ]}
            />
          </div>
          <div className="fp-tweak-sec">
            <div className="fp-tweak-label" style={{ display: "flex", alignItems: "center" }}>
              {__("Custom colors", "easycommerce-fakerpress")}
              {Object.keys(customColors).length > 0 && (
                <button
                  type="button"
                  className="fp-color-reset"
                  onClick={resetCustomColors}
                >
                  {__("Reset", "easycommerce-fakerpress")}
                </button>
              )}
            </div>
            <div className="fp-color-rows">
              {CUSTOM_TOKENS.map(({ token, label }) => {
                const overridden = token in customColors;
                const value = overridden
                  ? customColors[token]
                  : resolveTokenHex(token, theme, accent);
                return (
                  <div key={token} className="fp-color-row">
                    <span className="fp-color-name">{label}</span>
                    {overridden && (
                      <button
                        type="button"
                        className="fp-color-revert"
                        title={__("Revert", "easycommerce-fakerpress")}
                        aria-label={__("Revert", "easycommerce-fakerpress")}
                        onClick={() => setCustomColor(token, "")}
                      >
                        <Icon name="refresh" size={13} />
                      </button>
                    )}
                    <label
                      className={`fp-color-swatch${overridden ? " on" : ""}`}
                      style={{ background: value }}
                    >
                      <input
                        type="color"
                        value={value}
                        onChange={(e) => setCustomColor(token, e.target.value)}
                      />
                    </label>
                  </div>
                );
              })}
            </div>
          </div>
          <p
            style={{
              fontSize: 12,
              color: "var(--text-faint)",
              marginTop: 16,
              lineHeight: 1.5,
            }}
          >
            {__(
              "These controls preview how the FakerPress admin would adapt to store-owner theme preferences.",
              "easycommerce-fakerpress",
            )}
          </p>
        </div>
      </aside>
    </>
  );
}
