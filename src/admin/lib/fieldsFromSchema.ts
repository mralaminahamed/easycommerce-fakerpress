import type { ParameterConfig } from "@/admin/types";

export type FieldType =
  | "toggle"
  | "chips"
  | "range"
  | "select"
  | "number"
  | "text";

export interface FieldDescriptor {
  /** Dot-path into the params object, e.g. "price_range" or "inventory.manage_stock" */
  key: string;
  type: FieldType;
  label: string;
  section: string;
  /** Available options for select / chips fields */
  options?: string[];
  /** Lower bound for range / number fields */
  min?: number;
  /** Upper bound for range / number fields */
  max?: number;
  prefix?: string;
  suffix?: string;
  default?: unknown;
  /** If present the render layer should hide this field unless the condition is met */
  dependsOn?: Record<string, unknown>;
}

export interface FieldSection {
  name: string;
  fields: FieldDescriptor[];
}

// ---------------------------------------------------------------------------
// Internal helpers
// ---------------------------------------------------------------------------

/**
 * Convert a snake_case or kebab-case identifier to Title Case.
 * e.g. "price_range" → "Price Range", "manage-stock" → "Manage Stock"
 */
export function humanize(key: string): string {
  return key
    .split(/[_\-]/)
    .filter(Boolean)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
}

/**
 * Return true when the `properties` object is EXACTLY `{ min, max }` and both
 * are numeric (type "integer" | "number").
 */
function isMinMaxRange(
  properties: Record<string, ParameterConfig>,
): boolean {
  const keys = Object.keys(properties);
  if (keys.length !== 2 || !properties.min || !properties.max) return false;
  const numericTypes = new Set(["integer", "number"]);
  return (
    numericTypes.has(properties.min.type) &&
    numericTypes.has(properties.max.type)
  );
}

/**
 * Derive a single FieldDescriptor (or an array of them when the schema node
 * is an object that must be recursed) for one `key → config` entry.
 *
 * @param key       - The dot-path key for this field (already includes parent prefix)
 * @param config    - The ParameterConfig node
 * @param section   - The section name this field belongs to
 */
function descriptorFromNode(
  key: string,
  config: ParameterConfig,
  section: string,
): FieldDescriptor | FieldDescriptor[] {
  const label = config.title ?? humanize(key.split(".").pop()!);

  // ── string + enum → select ─────────────────────────────────────────────
  if (config.type === "string" && config.enum) {
    return {
      key,
      type: "select",
      label,
      section,
      options: config.enum,
      default: config.default,
      ...(config.dependsOn ? { dependsOn: config.dependsOn } : {}),
    };
  }

  // ── array → chips ─────────────────────────────────────────────────────
  if (config.type === "array") {
    return {
      key,
      type: "chips",
      label,
      section,
      options: config.items?.enum ?? [],
      default: config.default ?? config.items?.default ?? [],
      ...(config.dependsOn ? { dependsOn: config.dependsOn } : {}),
    };
  }

  // ── object ─────────────────────────────────────────────────────────────
  if (config.type === "object" && config.properties) {
    // Exact { min, max } numeric → range
    if (isMinMaxRange(config.properties)) {
      const minProp = config.properties.min;
      const maxProp = config.properties.max;
      return {
        key,
        type: "range",
        label,
        section,
        min: minProp.default ?? minProp.minimum,
        max: maxProp.default ?? maxProp.maximum,
        default: config.default,
        ...(config.dependsOn ? { dependsOn: config.dependsOn } : {}),
      };
    }

    // Any other object shape → recurse into properties
    const childSection = config.title ?? humanize(key.split(".").pop()!);
    const children: FieldDescriptor[] = [];
    for (const [childKey, childConfig] of Object.entries(config.properties)) {
      const fullKey = `${key}.${childKey}`;
      const result = descriptorFromNode(fullKey, childConfig, childSection);
      if (Array.isArray(result)) {
        children.push(...result);
      } else {
        children.push(result);
      }
    }
    return children;
  }

  // ── boolean → toggle ──────────────────────────────────────────────────
  if (config.type === "boolean") {
    return {
      key,
      type: "toggle",
      label,
      section,
      default: !!config.default,
      ...(config.dependsOn ? { dependsOn: config.dependsOn } : {}),
    };
  }

  // ── integer / number (no enum) → number ───────────────────────────────
  if (config.type === "integer" || config.type === "number") {
    return {
      key,
      type: "number",
      label,
      section,
      ...(config.minimum !== undefined ? { min: config.minimum } : {}),
      ...(config.maximum !== undefined ? { max: config.maximum } : {}),
      default: config.default,
      ...(config.dependsOn ? { dependsOn: config.dependsOn } : {}),
    };
  }

  // ── string (no enum) / anything else → text ───────────────────────────
  return {
    key,
    type: "text",
    label,
    section,
    default: config.default,
    ...(config.dependsOn ? { dependsOn: config.dependsOn } : {}),
  };
}

// ---------------------------------------------------------------------------
// Public API
// ---------------------------------------------------------------------------

/**
 * Convert a generator's `parameterConfig` map into an ordered list of
 * FieldSections, each containing typed FieldDescriptors ready for rendering.
 *
 * Mapping rules
 * - string + enum       → select
 * - array               → chips  (options = items.enum ?? [])
 * - object {min, max}   → range  (min/max come from the child defaults/minimums)
 * - object (other)      → recurse; children share the parent's humanized name
 *                         as their section
 * - boolean             → toggle
 * - integer / number    → number  (carries min/max/default)
 * - string (no enum)    → text
 */
export function fieldsFromSchema(
  config: Record<string, ParameterConfig>,
): FieldSection[] {
  /** Map from section name → fields collected for that section */
  const sectionMap = new Map<string, FieldDescriptor[]>();

  const pushField = (field: FieldDescriptor): void => {
    const bucket = sectionMap.get(field.section);
    if (bucket) {
      bucket.push(field);
    } else {
      sectionMap.set(field.section, [field]);
    }
  };

  for (const [key, nodeConfig] of Object.entries(config)) {
    // Section for top-level entry: use title or humanize the key
    const topSection = nodeConfig.title ?? humanize(key);

    const result = descriptorFromNode(key, nodeConfig, topSection);

    if (Array.isArray(result)) {
      // Object that was recursed: all child fields already carry their own
      // section (= humanized parent key), but we want them grouped under the
      // top-level parent's section name so they stay together.
      // The recursed section is already set to the humanized parent key by
      // descriptorFromNode, which is exactly what we want.
      result.forEach(pushField);
    } else {
      pushField(result);
    }
  }

  return Array.from(sectionMap.entries()).map(([name, fields]) => ({
    name,
    fields,
  }));
}
