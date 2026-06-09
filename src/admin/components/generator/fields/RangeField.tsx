import React, { useRef } from "@wordpress/element";
import { NumberField } from "./NumberField";

interface RangeValue {
  lo: number;
  hi: number;
}

interface RangeFieldProps {
  value: RangeValue;
  min: number;
  max: number;
  prefix?: string;
  suffix?: string;
  onChange: (v: RangeValue) => void;
}

export function RangeField({
  value,
  min,
  max,
  prefix,
  suffix,
  onChange,
}: RangeFieldProps) {
  const { lo, hi } = value;
  const trackRef = useRef<HTMLDivElement>(null);

  const pct = (v: number) => ((v - min) / (max - min)) * 100;

  const drag =
    (which: "lo" | "hi") =>
    (e: React.PointerEvent<HTMLButtonElement>) => {
      e.preventDefault();

      const move = (ev: PointerEvent) => {
        const track = trackRef.current;
        if (!track) return;
        const r = track.getBoundingClientRect();
        let v = Math.round(min + ((ev.clientX - r.left) / r.width) * (max - min));
        v = Math.max(min, Math.min(max, v));
        if (which === "lo") {
          onChange({ lo: Math.min(v, hi), hi });
        } else {
          onChange({ lo, hi: Math.max(v, lo) });
        }
      };

      const up = () => {
        window.removeEventListener("pointermove", move);
        window.removeEventListener("pointerup", up);
      };

      window.addEventListener("pointermove", move);
      window.addEventListener("pointerup", up);
    };

  return (
    <div className="fp-range">
      <div className="fp-range-inputs">
        <NumberField
          value={lo}
          prefix={prefix}
          suffix={suffix}
          onChange={(v) => onChange({ lo: Math.min(+(v) || min, hi), hi })}
        />
        <span className="fp-range-dash">–</span>
        <NumberField
          value={hi}
          prefix={prefix}
          suffix={suffix}
          onChange={(v) => onChange({ lo, hi: Math.max(+(v) || min, lo) })}
        />
      </div>
      <div className="fp-track" ref={trackRef}>
        <div
          className="fp-track-fill"
          style={{ left: pct(lo) + "%", right: 100 - pct(hi) + "%" }}
        />
        <button
          type="button"
          className="fp-thumb fp-focusable"
          style={{ left: pct(lo) + "%" }}
          onPointerDown={drag("lo")}
          aria-label="min"
        />
        <button
          type="button"
          className="fp-thumb fp-focusable"
          style={{ left: pct(hi) + "%" }}
          onPointerDown={drag("hi")}
          aria-label="max"
        />
      </div>
    </div>
  );
}
