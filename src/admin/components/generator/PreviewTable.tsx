import React from "react";
import { useState, useEffect, useRef } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";

import { fetchPreview } from "@/admin/lib/preview";
import { Badge } from "@/admin/components/ui/badge";
import { StatusPill } from "@/admin/components/ui/status-pill";
import { Icon } from "@/admin/lib/icons";

import type { PreviewCell, PreviewColumn } from "@/admin/lib/preview";

interface PreviewTableProps {
  route: string;
  params: Record<string, unknown>;
  count: number;
  /** Raw seed string from the run bar ("" = random). */
  seed: string;
  meta: boolean;
  locale: string;
  /** Bumped by the Shuffle button to force a re-roll. */
  shuffleN: number;
}

const KIND_CLASS: Record<string, string> = {
  mono: "cell-mono",
  money: "cell-money",
  num: "cell-num",
  stars: "cell-stars",
};

// ---------------------------------------------------------------------------
// Single preview cell
// ---------------------------------------------------------------------------

function Cell({ cell }: { cell?: PreviewCell }) {
  if (!cell) return <td>—</td>;
  if (cell.kind === "badge") {
    return (
      <td>
        <Badge kind="neutral">{cell.v}</Badge>
      </td>
    );
  }
  if (cell.kind === "status") {
    return (
      <td>
        <StatusPill>{cell.v}</StatusPill>
      </td>
    );
  }
  return <td className={KIND_CLASS[cell.kind ?? ""] ?? ""}>{cell.v}</td>;
}

// ---------------------------------------------------------------------------
// PreviewTable — debounced fetch from the read-only /preview route
// ---------------------------------------------------------------------------

export function PreviewTable({
  route,
  params,
  count,
  seed,
  meta,
  locale,
  shuffleN,
}: PreviewTableProps) {
  const visible = Math.min(count, 12);

  const [columns, setColumns] = useState<PreviewColumn[]>([]);
  const [rows, setRows] = useState<Record<string, PreviewCell>[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  // Serialise the inputs that affect the preview so the effect only refires
  // when something meaningful changes.
  const token = JSON.stringify(params) + "|" + seed + "|" + meta + "|" + shuffleN;

  useEffect(() => {
    let cancelled = false;
    const timer = setTimeout(() => {
      setLoading(true);
      setError(null);

      const body: Record<string, unknown> = {
        ...params,
        count: visible,
        locale,
        include_meta: meta,
      };
      const trimmed = seed.trim();
      if (trimmed) {
        const n = parseInt(trimmed, 10);
        if (!Number.isNaN(n)) body.seed = n;
      }

      fetchPreview(route, body)
        .then((data) => {
          if (cancelled) return;
          setColumns(data.columns ?? []);
          setRows(data.rows ?? []);
          setLoading(false);
        })
        .catch((err: unknown) => {
          if (cancelled) return;
          setError(
            err instanceof Error
              ? err.message
              : __("Could not load preview.", "easycommerce-fakerpress"),
          );
          setLoading(false);
        });
    }, 400);

    return () => {
      cancelled = true;
      clearTimeout(timer);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [route, token, visible, locale]);

  // Columns rendered, plus an optional metadata column.
  const cols: PreviewColumn[] = meta
    ? [...columns, { key: "_meta", label: __("Metadata", "easycommerce-fakerpress") }]
    : columns;

  const seedLabel = seed.trim()
    ? sprintf(
        /* translators: %s: seed value */
        __("seed: %s", "easycommerce-fakerpress"),
        seed.trim(),
      )
    : __("seed: random", "easycommerce-fakerpress");

  return (
    <div className="fp-table-card" data-testid="preview-table">
      <div className="fp-table-scroll">
        {error ? (
          <div className="fp-preview-state fp-preview-error">
            <Icon name="alert" size={18} />
            <span>{error}</span>
          </div>
        ) : columns.length === 0 ? (
          <div className="fp-preview-state">
            {loading
              ? __("Loading preview…", "easycommerce-fakerpress")
              : __("No preview available.", "easycommerce-fakerpress")}
          </div>
        ) : (
          <table className="fp-table" style={loading ? { opacity: 0.55 } : undefined}>
            <thead>
              <tr>
                {cols.map((c) => (
                  <th key={c.key}>{c.label}</th>
                ))}
              </tr>
            </thead>
            <tbody key={token}>
              {rows.map((row, i) => (
                <tr key={i} style={{ animationDelay: `${i * 18}ms` }}>
                  {cols.map((c) =>
                    c.key === "_meta" ? (
                      <td
                        key={c.key}
                        className="cell-mono"
                        style={{ color: "var(--text-faint)" }}
                      >
                        created · src:faker
                      </td>
                    ) : (
                      <Cell key={c.key} cell={row[c.key]} />
                    ),
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
      <div className="fp-table-foot">
        <span>
          {sprintf(
            /* translators: %1$s: visible row count, %2$s: total count */
            __("Live preview · showing %1$s of %2$s", "easycommerce-fakerpress"),
            visible.toLocaleString(),
            count.toLocaleString(),
          )}
        </span>
        <span className="mono">{seedLabel}</span>
      </div>
    </div>
  );
}
