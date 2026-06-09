import React from "react";
import { __ } from "@wordpress/i18n";
import { Icon } from "@/admin/lib/icons";
import { SectionLabel } from "@/admin/components/ui/section-label";
import { fieldsFromSchema } from "@/admin/lib/fieldsFromSchema";
import { getPath } from "@/admin/lib/paths";
import { Field } from "@/admin/components/generator/FieldSection";
import type { Generator } from "@/admin/types";

// ---------------------------------------------------------------------------
// Dependency notes keyed by generator route
// ---------------------------------------------------------------------------

const DEP: Record<string, string> = {
  refunds: "Targets existing completed / processing orders",
  "product-reviews": "Auto-linked to existing products & customers",
  "product-variations": "Applied to existing variable products",
  transaction: "Generated against existing orders",
  "cart-sessions": "Uses your existing products & customers",
};

// ---------------------------------------------------------------------------
// ConfigColumn
// ---------------------------------------------------------------------------

interface ConfigColumnProps {
  generator: Generator;
  params: Record<string, any>;
  setField: (key: string, value: any) => void;
}

/**
 * Left column of the Generator page — icon/name, description, optional
 * dependency note, and the full field sections derived from parameterConfig.
 */
export function ConfigColumn({
  generator,
  params,
  setField,
}: ConfigColumnProps): JSX.Element {
  const depNote = DEP[generator.route];
  const sections = fieldsFromSchema(generator.parameterConfig ?? {});
  const hasFields =
    sections.length > 0 && sections.some((s) => s.fields.length > 0);

  return (
    <div className="fp-config-col">
      {/* Header: icon + name + optional "Popular" tag */}
      <div className="fp-config-head">
        <div className="fp-config-ic">
          <Icon name={generator.iconName} size={22} />
        </div>
        <div>
          <div className="fp-config-title">{generator.name}</div>
          {generator.popular && (
            <span
              className="fp-tag"
              style={{ marginTop: 6, display: "inline-block" }}
            >
              {__("Popular", "easycommerce-fakerpress")}
            </span>
          )}
        </div>
      </div>

      {/* Description */}
      <p className="fp-config-desc">{generator.description}</p>

      {/* Dependency note */}
      {depNote && (
        <div className="fp-dep">
          <Icon name="info" size={15} />
          {__(depNote, "easycommerce-fakerpress")}
        </div>
      )}

      {/* Field sections */}
      {!hasFields ? (
        <div className="fp-field-section">
          <p style={{ color: "var(--text-faint)", fontSize: 13 }}>
            {__(
              "No extra options — just set a count and generate.",
              "easycommerce-fakerpress",
            )}
          </p>
        </div>
      ) : (
        sections.map((section, si) => {
          const allNum =
            section.fields.length === 2 &&
            section.fields.every((f) => f.type === "number");

          const dup =
            section.fields.length === 1 &&
            section.fields[0].label.toLowerCase() ===
              section.name.toLowerCase() &&
            section.fields[0].type !== "toggle";

          return (
            <div key={si} className="fp-field-section">
              <SectionLabel>{section.name}</SectionLabel>

              {allNum ? (
                <div className="fp-field-2col">
                  {section.fields.map((f) => (
                    <Field
                      key={f.key}
                      f={f}
                      value={getPath(params, f.key)}
                      onChange={(v) => setField(f.key, v)}
                    />
                  ))}
                </div>
              ) : (
                section.fields.map((f) => (
                  <Field
                    key={f.key}
                    f={f}
                    value={getPath(params, f.key)}
                    onChange={(v) => setField(f.key, v)}
                    hideLabel={dup}
                  />
                ))
              )}
            </div>
          );
        })
      )}
    </div>
  );
}
