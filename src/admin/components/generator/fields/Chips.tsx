import React from "react";
import { Icon } from "@/admin/lib/icons";

interface ChipsProps {
  options: string[];
  value: string[];
  onChange: (v: string[]) => void;
}

export function Chips({ options, value, onChange }: ChipsProps) {
  const toggle = (o: string) =>
    onChange(
      value.includes(o) ? value.filter((x) => x !== o) : [...value, o],
    );

  return (
    <div className="fp-chips">
      {options.map((o) => (
        <button
          key={o}
          type="button"
          onClick={() => toggle(o)}
          className={`fp-chip fp-focusable${value.includes(o) ? " on" : ""}`}
          data-chip-value={o}
        >
          {value.includes(o) && <Icon name="check" size={13} stroke={2.4} />}
          <span>{o}</span>
        </button>
      ))}
    </div>
  );
}
