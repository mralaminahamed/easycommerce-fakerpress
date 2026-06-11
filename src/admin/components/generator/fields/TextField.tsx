import React from "react";

interface TextFieldProps {
  value: string;
  onChange: (v: string) => void;
  ph?: string;
}

export function TextField({ value, onChange, ph }: TextFieldProps) {
  return (
    <input
      className="fp-input fp-focusable"
      value={value}
      placeholder={ph}
      onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
        onChange(e.target.value)
      }
    />
  );
}
