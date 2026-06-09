import type { StoredRun, GlobalRun } from "@/admin/types";
import { getSettings } from "@/admin/lib/settings";

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const RECENT_RUNS_KEY = "ec_fp_recent_runs";
const MAX_RECENT_RUNS = 30;

// ---------------------------------------------------------------------------
// Per-route run history (existing)
// ---------------------------------------------------------------------------

export function getRuns(type: string): StoredRun[] {
  try {
    const raw = localStorage.getItem(`ec_fp_runs_${type}`);
    return raw ? (JSON.parse(raw) as StoredRun[]) : [];
  } catch {
    return [];
  }
}

export function addRun(
  type: string,
  run: StoredRun,
  options?: { locale?: string; seed?: string }
): void {
  try {
    // --- per-route list (existing behaviour) ---
    const runs = getRuns(type);
    runs.unshift(run);
    const max = getSettings().maxRunsPerGenerator;
    if (runs.length > max) {
      runs.length = max;
    }
    localStorage.setItem(`ec_fp_runs_${type}`, JSON.stringify(runs));

    // --- global recent-runs list (new) ---
    const globalRun: GlobalRun = {
      route: type,
      count: run.count,
      timestamp: run.timestamp,
      success: run.success,
      ...(options?.locale !== undefined && { locale: options.locale }),
      ...(options?.seed !== undefined && { seed: options.seed }),
    };
    const recent = getRecentRunsRaw();
    recent.unshift(globalRun);
    if (recent.length > MAX_RECENT_RUNS) {
      recent.length = MAX_RECENT_RUNS;
    }
    localStorage.setItem(RECENT_RUNS_KEY, JSON.stringify(recent));
  } catch {
    // ignore write failures
  }
}

// ---------------------------------------------------------------------------
// Per-route stats (existing)
// ---------------------------------------------------------------------------

export function getStats(type: string): number {
  try {
    return parseInt(localStorage.getItem(`ec_fp_stats_${type}`) ?? "0", 10);
  } catch {
    return 0;
  }
}

export function incrementStats(type: string, count: number): void {
  try {
    localStorage.setItem(`ec_fp_stats_${type}`, String(getStats(type) + count));
  } catch {
    // ignore write failures
  }
}

export function getTotalStats(): number {
  let total = 0;
  for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    if (key?.startsWith("ec_fp_stats_")) {
      total += parseInt(localStorage.getItem(key) ?? "0", 10);
    }
  }
  return total;
}

// ---------------------------------------------------------------------------
// New: per-route cumulative counts
// ---------------------------------------------------------------------------

/**
 * Returns a map of route → cumulative generated count (from ec_fp_stats_* keys).
 */
export function getCounts(): Record<string, number> {
  const result: Record<string, number> = {};
  try {
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key?.startsWith("ec_fp_stats_")) {
        const route = key.slice("ec_fp_stats_".length);
        result[route] = parseInt(localStorage.getItem(key) ?? "0", 10);
      }
    }
  } catch {
    // ignore read failures
  }
  return result;
}

// ---------------------------------------------------------------------------
// New: total generated (successful runs only)
// ---------------------------------------------------------------------------

/**
 * Sum of counts from all successful runs recorded in the global recent-runs
 * list plus per-route stats for any routes not represented there.
 *
 * Strategy: ec_fp_stats_* keys are incremented by the caller for every
 * successful run, so summing them gives the authoritative total.
 */
export function getTotalGenerated(): number {
  return getTotalStats();
}

// ---------------------------------------------------------------------------
// New: global recent runs
// ---------------------------------------------------------------------------

/** Internal helper — reads the raw global list without a cap. */
function getRecentRunsRaw(): GlobalRun[] {
  try {
    const raw = localStorage.getItem(RECENT_RUNS_KEY);
    return raw ? (JSON.parse(raw) as GlobalRun[]) : [];
  } catch {
    return [];
  }
}

/**
 * Returns the most-recent `limit` runs across ALL generators, newest first.
 */
export function getRecentRuns(limit: number): GlobalRun[] {
  try {
    const runs = getRecentRunsRaw();
    return runs.slice(0, limit);
  } catch {
    return [];
  }
}

// ---------------------------------------------------------------------------
// New: clearStats
// ---------------------------------------------------------------------------

/**
 * Clears all storage keys owned by this module:
 *   ec_fp_runs_*   — per-route run history
 *   ec_fp_stats_*  — per-route cumulative counts
 *   ec_fp_recent_runs — global recent-runs list
 *
 * Does NOT touch unrelated keys such as fp_theme, fp_accent, fp_density,
 * fp_nav_collapsed, fp_locale, or ec_fp_settings.
 */
export function clearStats(): void {
  try {
    const keysToRemove: string[] = [];
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (
        key?.startsWith("ec_fp_runs_") ||
        key?.startsWith("ec_fp_stats_") ||
        key === RECENT_RUNS_KEY
      ) {
        keysToRemove.push(key);
      }
    }
    for (const key of keysToRemove) {
      localStorage.removeItem(key);
    }
  } catch {
    // ignore failures
  }
}
