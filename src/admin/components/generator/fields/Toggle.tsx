import React from "react";

interface ToggleProps {
  checked: boolean;
  onChange: (v: boolean) => void;
  label?: string;
  hint?: string;
}

export function Toggle({ checked, onChange, label, hint }: ToggleProps) {
  return (
    <label className="fp-toggle-row">
      <button
        type="button"
        role="switch"
        aria-checked={!!checked}
        onClick={() => onChange(!checked)}
        className={`fp-switch fp-focusable${checked ? " on" : ""}`}
      >
        <span className="fp-knob" />
      </button>
      {label && (
        <span className="fp-toggle-label">
          {label}
          {hint && <span className="fp-toggle-hint">{hint}</span>}
        </span>
      )}
    </label>
  );
}
