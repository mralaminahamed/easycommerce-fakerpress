import React from "react";
import { useNavigate } from "react-router-dom";
import { Icon } from "@/admin/lib/icons";
import { useTheme } from "@/admin/theme/useTheme";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

export interface TopbarProps {
  crumb: string;
  locale: string;
  onOpenLocale: () => void;
  onOpenTweaks: () => void;
  batchCount: number;
  onOpenBatch: () => void;
}

// ---------------------------------------------------------------------------
// Topbar
// ---------------------------------------------------------------------------

export function Topbar({
  crumb,
  locale,
  onOpenLocale,
  onOpenTweaks,
  batchCount,
  onOpenBatch,
}: TopbarProps) {
  const navigate = useNavigate();
  const { theme, setTheme } = useTheme();

  return (
    <header className="fp-topbar" data-testid="topbar">
      {/* Breadcrumbs */}
      <div className="fp-crumbs">
        <button
          className="fp-btn fp-btn-ghost fp-btn-sm"
          onClick={() => navigate("/")}
          style={{ padding: "0 8px", marginLeft: -6 }}
        >
          FakerPress
        </button>
        <Icon name="chevright" size={14} style={{ color: "var(--text-faint)" }} />
        <span className="fp-crumb-cur">{crumb}</span>
      </div>

      {/* Right controls */}
      <div className="fp-top-right">
        {batchCount > 0 && (
          <button className="fp-btn fp-btn-soft fp-btn-sm" onClick={onOpenBatch} data-testid="batch-chip">
            <Icon name="layers" size={15} />
            Batch
            <span className="fp-badge tone-accent" style={{ height: 18, marginLeft: 2 }}>
              {batchCount}
            </span>
          </button>
        )}

        <button className="fp-locale-pill fp-focusable" onClick={onOpenLocale}>
          <Icon name="globe" size={14} />
          {locale.split(" (")[0]}
        </button>

        <button
          className="fp-icon-btn fp-focusable"
          onClick={() => setTheme(theme === "dark" ? "light" : "dark")}
          title="Toggle theme"
          data-testid="theme-toggle"
        >
          <Icon name={theme === "dark" ? "sun" : "moon"} size={17} />
        </button>

        <button className="fp-icon-btn fp-focusable" onClick={onOpenTweaks} title="Tweaks" data-testid="tweaks-button">
          <Icon name="sliders" size={17} />
        </button>
      </div>
    </header>
  );
}

export default Topbar;
