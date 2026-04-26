import type { StoredRun } from "@/admin/types";
import { getSettings } from "@/admin/lib/settings";

export function getRuns(type: string): StoredRun[] {
  try {
    const raw = localStorage.getItem(`ec_fp_runs_${type}`);
    return raw ? (JSON.parse(raw) as StoredRun[]) : [];
  } catch {
    return [];
  }
}

export function addRun(type: string, run: StoredRun): void {
  try {
    const runs = getRuns(type);
    runs.unshift(run);
    const max = getSettings().maxRunsPerGenerator;
    if (runs.length > max) {
      runs.length = max;
    }
    localStorage.setItem(`ec_fp_runs_${type}`, JSON.stringify(runs));
  } catch {
    // ignore write failures
  }
}

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
