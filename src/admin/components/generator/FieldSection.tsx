import React from "react";
import type { FieldDescriptor } from "@/admin/lib/fieldsFromSchema";
import { Toggle } from "@/admin/components/generator/fields/Toggle";
import { Chips } from "@/admin/components/generator/fields/Chips";
import { RangeField } from "@/admin/components/generator/fields/RangeField";
import { FieldSelect } from "@/admin/components/generator/fields/FieldSelect";
import { NumberField } from "@/admin/components/generator/fields/NumberField";
import { TextField } from "@/admin/components/generator/fields/TextField";

interface FieldProps {
  f: FieldDescriptor;
  value: any;
  onChange: (v: any) => void;
  hideLabel?: boolean;
}

/**
 * Renders a single field control based on the FieldDescriptor type.
 * Dispatches to the appropriate Task-5 field component.
 */
export function Field({ f, value, onChange, hideLabel }: FieldProps): JSX.Element {
  if (f.type === "toggle") {
    // Toggle owns its own label rendering — ignore hideLabel.
    return (
      <div className="fp-field" data-param={f.key}>
        <Toggle checked={!!value} onChange={onChange} label={f.label} />
      </div>
    );
  }

  return (
    <div className="fp-field" data-param={f.key}>
      {!hideLabel && (
        <label className="fp-field-label">{f.label}</label>
      )}
      {f.type === "chips" ? (
        <Chips
          options={f.options ?? []}
          value={value ?? []}
          onChange={onChange}
        />
      ) : f.type === "range" ? (
        <RangeField
          value={value ?? { lo: f.min ?? 0, hi: f.max ?? 100 }}
          min={f.min ?? 0}
          max={f.max ?? 100}
          prefix={f.prefix}
          suffix={f.suffix}
          onChange={onChange}
        />
      ) : f.type === "select" ? (
        <FieldSelect
          value={value ?? (f.default as string) ?? ""}
          options={f.options ?? []}
          onChange={onChange}
          width={320}
        />
      ) : f.type === "number" ? (
        // NumberField accepts `number | string` as value and calls onChange with a string.
        // We accept strings from it as-is; callers that need a number must parse.
        <NumberField
          value={value ?? (f.default as number | string) ?? ""}
          prefix={f.prefix}
          suffix={f.suffix}
          onChange={(s) => onChange(s)}
          width={160}
        />
      ) : (
        // text (and any unrecognised type)
        <TextField
          value={value ?? ""}
          ph={(f as any).ph}
          onChange={onChange}
        />
      )}
    </div>
  );
}
