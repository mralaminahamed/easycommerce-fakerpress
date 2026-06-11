import React from "react";
import { __ } from "@wordpress/i18n";

import { Icon } from "@/admin/lib/icons";

interface LocalePickerProps {
  onClose: () => void;
  locale: string;
  setLocale: (l: string) => void;
}

export function LocalePicker({ onClose, locale, setLocale }: LocalePickerProps) {
  const all = window.easycommerceFakerpressApi?.locale?.allLocales ?? {};
  // Sorted list of human labels; fall back to the current locale if none provided.
  const labels = Object.values(all).filter(Boolean).sort();
  const options = labels.length > 0 ? labels : [locale];

  return (
    <div
      className="fp-overlay"
      style={{
        alignItems: "flex-start",
        justifyContent: "center",
        paddingTop: "14vh",
      }}
      onMouseDown={onClose}
      data-testid="locale-picker"
    >
      <div
        className="fp-cmd-box"
        style={{ width: "min(420px,92vw)" }}
        onMouseDown={(e) => e.stopPropagation()}
      >
        <div className="fp-cmd-input-row">
          <Icon name="globe" size={18} />
          <span style={{ fontWeight: 600 }}>
            {__("Default locale", "easycommerce-fakerpress")}
          </span>
        </div>
        <div className="fp-cmd-results">
          {options.map((l) => (
            <div
              key={l}
              className={`fp-cmd-item${l === locale ? " sel" : ""}`}
              onClick={() => {
                setLocale(l);
                onClose();
              }}
            >
              <Icon name="globe" size={16} className="fp-cmd-ic" />
              <span>{l}</span>
              {l === locale && (
                <Icon
                  name="check"
                  size={16}
                  style={{ marginLeft: "auto", color: "var(--accent)" }}
                />
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
