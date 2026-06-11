import React, { useState, useRef, useEffect } from "@wordpress/element";
import { Icon } from "@/admin/lib/icons";

interface FieldSelectProps {
  value: string;
  options: string[];
  onChange: (v: string) => void;
  width?: number;
}

export function FieldSelect({ value, options, onChange, width }: FieldSelectProps) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const h = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener("mousedown", h);
    return () => document.removeEventListener("mousedown", h);
  }, []);

  return (
    <div
      className="fp-select"
      ref={ref}
      style={width ? { maxWidth: width } : undefined}
    >
      <button
        type="button"
        className={`fp-select-btn fp-focusable${open ? " open" : ""}`}
        onClick={() => setOpen((o) => !o)}
      >
        <span>{value}</span>
        <Icon name="updown" size={15} className="fp-select-caret" />
      </button>
      {open && (
        <div className="fp-select-pop fp-pop">
          {options.map((o) => (
            <button
              key={o}
              type="button"
              className={`fp-select-opt${o === value ? " sel" : ""}`}
              onClick={() => {
                onChange(o);
                setOpen(false);
              }}
            >
              <span>{o}</span>
              {o === value && <Icon name="check" size={15} stroke={2.2} />}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
