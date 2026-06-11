import React from "react";
import { useState, useEffect, useRef } from "@wordpress/element";
import { useNavigate } from "react-router-dom";
import { __ } from "@wordpress/i18n";

import { generators } from "@/admin/lib/generators";
import { Icon } from "@/admin/lib/icons";

interface CommandPaletteProps {
  onClose: () => void;
}

interface CmdItem {
  key: string;
  name: string;
  grp: string;
  ic: string;
  path: string;
}

export function CommandPalette({ onClose }: CommandPaletteProps) {
  const navigate = useNavigate();
  const [q, setQ] = useState("");
  const [sel, setSel] = useState(0);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    inputRef.current?.focus();
  }, []);

  const items: CmdItem[] = [
    { key: "dashboard", name: __("Overview", "easycommerce-fakerpress"), grp: __("Pages", "easycommerce-fakerpress"), ic: "dashboard", path: "/" },
    { key: "settings", name: __("Settings", "easycommerce-fakerpress"), grp: __("Pages", "easycommerce-fakerpress"), ic: "settings", path: "/settings" },
    { key: "plugins", name: __("Our Plugins", "easycommerce-fakerpress"), grp: __("Pages", "easycommerce-fakerpress"), ic: "plug", path: "/plugins" },
    ...generators.map((g) => ({
      key: `gen:${g.route}`,
      name: g.name,
      grp: sprintfGroup(g.category),
      ic: g.iconName,
      path: `/generator/${g.route}`,
    })),
  ];

  const ql = q.trim().toLowerCase();
  const filtered = ql
    ? items.filter(
        (i) =>
          i.name.toLowerCase().includes(ql) || i.grp.toLowerCase().includes(ql),
      )
    : items;

  useEffect(() => {
    setSel(0);
  }, [q]);

  const choose = (i: number) => {
    const item = filtered[i];
    if (item) {
      navigate(item.path);
      onClose();
    }
  };

  const onKey = (e: React.KeyboardEvent) => {
    if (e.key === "ArrowDown") {
      e.preventDefault();
      setSel((s) => Math.min(filtered.length - 1, s + 1));
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      setSel((s) => Math.max(0, s - 1));
    } else if (e.key === "Enter") {
      e.preventDefault();
      choose(sel);
    } else if (e.key === "Escape") {
      onClose();
    }
  };

  let lastGrp: string | null = null;

  return (
    <div
      className="fp-overlay fp-cmd-overlay"
      onMouseDown={onClose}
      data-testid="command-palette"
    >
      <div
        className="fp-cmd-box"
        onMouseDown={(e) => e.stopPropagation()}
        onKeyDown={onKey}
      >
        <div className="fp-cmd-input-row">
          <Icon name="search" size={18} />
          <input
            ref={inputRef}
            className="fp-cmd-input"
            placeholder={__(
              "Search generators and pages…",
              "easycommerce-fakerpress",
            )}
            value={q}
            onChange={(e) => setQ(e.target.value)}
          />
          <span className="kbd-key">ESC</span>
        </div>
        <div className="fp-cmd-results">
          {filtered.length === 0 ? (
            <div className="fp-empty" style={{ padding: 28 }}>
              {__("No matches", "easycommerce-fakerpress")}
            </div>
          ) : (
            filtered.map((it, i) => {
              const head = it.grp !== lastGrp ? (lastGrp = it.grp) : null;
              return (
                <React.Fragment key={it.key}>
                  {head && (
                    <div className="fp-cmd-group-label">{it.grp}</div>
                  )}
                  <div
                    className={`fp-cmd-item${i === sel ? " sel" : ""}`}
                    onMouseEnter={() => setSel(i)}
                    onClick={() => choose(i)}
                  >
                    <Icon name={it.ic} size={17} className="fp-cmd-ic" />
                    <span>{it.name}</span>
                    {i === sel && <span className="grp">↵</span>}
                  </div>
                </React.Fragment>
              );
            })
          )}
        </div>
        <div className="fp-cmd-foot">
          <span>
            <span className="kbd-key">↑</span> <span className="kbd-key">↓</span>{" "}
            {__("navigate", "easycommerce-fakerpress")}
          </span>
          <span>
            <span className="kbd-key">↵</span>{" "}
            {__("open", "easycommerce-fakerpress")}
          </span>
        </div>
      </div>
    </div>
  );
}

/** Suffix a generator category with " generator" for the palette group label. */
function sprintfGroup(category: string): string {
  return `${category} ${__("generator", "easycommerce-fakerpress")}`;
}
