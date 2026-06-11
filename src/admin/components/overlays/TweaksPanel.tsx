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
  const { theme, setTheme, accent, setAccent, density, setDensity } = useTheme();

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
