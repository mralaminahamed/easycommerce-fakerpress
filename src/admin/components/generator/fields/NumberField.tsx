import React from "react";

interface NumberFieldProps {
  value: number | string;
  onChange: (v: string) => void;
  prefix?: string;
  suffix?: string;
  ph?: string;
  width?: number;
}

export function NumberField({
  value,
  onChange,
  prefix,
  suffix,
  ph,
  width,
}: NumberFieldProps) {
  return (
    <div
      className="fp-input-wrap"
      style={width ? { maxWidth: width } : undefined}
    >
      {prefix && <span className="fp-affix">{prefix}</span>}
      <input
        className={`fp-input fp-focusable tnum${prefix ? " has-prefix" : ""}${suffix ? " has-suffix" : ""}`}
        value={value}
        placeholder={ph}
        inputMode="numeric"
        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
          onChange(e.target.value.replace(/[^\d.-]/g, ""))
        }
      />
      {suffix && <span className="fp-affix suf">{suffix}</span>}
    </div>
  );
}
