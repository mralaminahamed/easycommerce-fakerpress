import apiFetch from "@wordpress/api-fetch";

export interface PreviewCell {
  v: string | number;
  kind?: string;
}

export interface PreviewColumn {
  key: string;
  label: string;
}

export interface PreviewData {
  columns: PreviewColumn[];
  rows: Record<string, PreviewCell>[];
}

/**
 * Fetch read-only preview rows from a generator's `/preview` REST route.
 * No data is persisted — the server builds rows from faker + sample data only.
 */
export function fetchPreview(
  route: string,
  params: Record<string, unknown>,
): Promise<PreviewData> {
  return apiFetch({
    path: `/easycommerce-fakerpress/v1/${route}/preview`,
    method: "POST",
    data: params,
  }) as Promise<PreviewData>;
}
