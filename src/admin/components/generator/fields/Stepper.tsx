import React from "react";
import { Icon } from "@/admin/lib/icons";

interface StepperProps {
  value: number;
  onChange: (v: number) => void;
  min?: number;
  max?: number;
  step?: number;
  testId?: string;
}

export function Stepper({
  value,
  onChange,
  min = 1,
  max = 100000,
  step = 1,
  testId,
}: StepperProps) {
  const clamp = (v: number) => Math.max(min, Math.min(max, v));

  return (
    <div className="fp-stepper">
      <button
        type="button"
        className="fp-step-btn fp-focusable"
        onClick={() => onChange(clamp(value - step))}
        aria-label="decrease"
        data-testid={testId ? `${testId}-dec` : undefined}
      >
        <Icon name="minus" size={15} />
      </button>
      <input
        className="fp-step-input tnum"
        value={value}
        inputMode="numeric"
        data-testid={testId ? `${testId}-input` : undefined}
        onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
          const n = parseInt(e.target.value.replace(/\D/g, ""), 10);
          onChange(isNaN(n) ? min : clamp(n));
        }}
      />
      <button
        type="button"
        className="fp-step-btn fp-focusable"
        onClick={() => onChange(clamp(value + step))}
        aria-label="increase"
        data-testid={testId ? `${testId}-inc` : undefined}
      >
        <Icon name="plus" size={15} />
      </button>
    </div>
  );
}
