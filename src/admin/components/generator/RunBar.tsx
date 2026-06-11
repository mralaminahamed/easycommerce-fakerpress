import React from "react";
import { __, sprintf } from "@wordpress/i18n";
import { Button } from "@/admin/components/ui/button";
import { Stepper } from "@/admin/components/generator/fields/Stepper";
import { TextField } from "@/admin/components/generator/fields/TextField";
import { Toggle } from "@/admin/components/generator/fields/Toggle";

// ---------------------------------------------------------------------------
// RunBar — sticky bottom bar with count / seed / meta controls + action btns
// ---------------------------------------------------------------------------

interface RunBarProps {
  count: number;
  seed: string;
  meta: boolean;
  onCount: (v: number) => void;
  onSeed: (v: string) => void;
  onMeta: (v: boolean) => void;
  onGenerate: () => void;
  onAddBatch: () => void;
  generating: boolean;
}

export function RunBar({
  count,
  seed,
  meta,
  onCount,
  onSeed,
  onMeta,
  onGenerate,
  onAddBatch,
  generating,
}: RunBarProps): JSX.Element {
  const generateLabel =
    count === 1
      ? sprintf(
          /* translators: %s: formatted number */
          __("Generate %s item", "easycommerce-fakerpress"),
          count.toLocaleString(),
        )
      : sprintf(
          /* translators: %s: formatted number */
          __("Generate %s items", "easycommerce-fakerpress"),
          count.toLocaleString(),
        );

  return (
    <div className="fp-genbar" data-testid="generator-runbar">
      {/* Count group */}
      <div className="fp-genbar-ctl">
        <span className="lbl">{__("Count", "easycommerce-fakerpress")}</span>
        <Stepper value={count} onChange={onCount} min={1} max={100000} testId="count" />
      </div>

      <div className="fp-genbar-sep" />

      {/* Seed group */}
      <div className="fp-genbar-ctl">
        <span className="lbl">{__("Seed", "easycommerce-fakerpress")}</span>
        <div className="fp-seed-input">
          <TextField value={seed} ph="random" onChange={onSeed} />
        </div>
      </div>

      <div className="fp-genbar-sep" />

      {/* Metadata toggle */}
      <Toggle
        checked={meta}
        onChange={onMeta}
        label={__("Metadata", "easycommerce-fakerpress")}
      />

      <div className="fp-genbar-spacer" />

      {/* Add to batch */}
      <Button
        variant="outline"
        size="lg"
        icon="layers"
        onClick={onAddBatch}
        type="button"
        data-testid="add-to-batch"
      >
        {__("Add to batch", "easycommerce-fakerpress")}
      </Button>

      {/* Generate */}
      <Button
        variant="primary"
        size="lg"
        icon="sparkles"
        onClick={onGenerate}
        disabled={generating}
        type="button"
        data-testid="generate-btn"
      >
        {generateLabel}
      </Button>
    </div>
  );
}
