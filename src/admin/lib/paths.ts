/**
 * Dot-path utilities for reading and immutably updating nested objects.
 *
 * Example paths: "price_range", "inventory.manage_stock", "a.b.c"
 */

/**
 * Read a value at a dot-separated path from a plain object.
 * Returns `undefined` if any segment along the path is missing.
 */
export function getPath(obj: Record<string, any>, path: string): any {
  const parts = path.split(".");
  let cursor: any = obj;
  for (const part of parts) {
    if (cursor === null || cursor === undefined || typeof cursor !== "object") {
      return undefined;
    }
    cursor = cursor[part];
  }
  return cursor;
}

/**
 * Return a NEW object with the nested `path` set to `value`.
 * Intermediate objects are created as needed; existing sibling keys are
 * preserved via shallow-cloning at each level.
 */
export function setPath(
  obj: Record<string, any>,
  path: string,
  value: any,
): Record<string, any> {
  const parts = path.split(".");

  function recurse(
    current: Record<string, any>,
    segments: string[],
  ): Record<string, any> {
    const [head, ...tail] = segments;
    if (tail.length === 0) {
      return { ...current, [head]: value };
    }
    const nested =
      current[head] !== null &&
      current[head] !== undefined &&
      typeof current[head] === "object"
        ? current[head]
        : {};
    return { ...current, [head]: recurse(nested, tail) };
  }

  return recurse(obj, parts);
}
