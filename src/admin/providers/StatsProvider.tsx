import React from "react";
import { createContext, useContext, useState, useCallback } from "@wordpress/element";
import {
  addRun,
  incrementStats,
  getCounts,
  getTotalGenerated,
  getRecentRuns,
  clearStats as storageClearStats,
} from "@/admin/lib/storage";
import type { GlobalRun, StoredRun } from "@/admin/types";

// ---------------------------------------------------------------------------
// Context shape
// ---------------------------------------------------------------------------

interface StatsState {
  counts: Record<string, number>;
  totalGenerated: number;
  recentRuns: GlobalRun[];
  recordRun: (
    route: string,
    count: number,
    success: boolean,
    message: string,
    opts?: { locale?: string; seed?: string }
  ) => void;
  clearStats: () => void;
}

const StatsContext = createContext<StatsState | null>(null);

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------

export function StatsProvider({ children }: { children: React.ReactNode }) {
  const [counts, setCounts] = useState<Record<string, number>>(() => getCounts());
  const [totalGenerated, setTotalGenerated] = useState<number>(() => getTotalGenerated());
  const [recentRuns, setRecentRuns] = useState<GlobalRun[]>(() => getRecentRuns(30));

  const refresh = useCallback(() => {
    setCounts(getCounts());
    setTotalGenerated(getTotalGenerated());
    setRecentRuns(getRecentRuns(30));
  }, []);

  const recordRun = useCallback(
    (
      route: string,
      count: number,
      success: boolean,
      message: string,
      opts?: { locale?: string; seed?: string }
    ) => {
      const run: StoredRun = { count, timestamp: Date.now(), success, message };
      addRun(route, run, opts);
      if (success) incrementStats(route, count);
      refresh();
    },
    [refresh]
  );

  const clearStats = useCallback(() => {
    storageClearStats();
    refresh();
  }, [refresh]);

  return (
    <StatsContext.Provider value={{ counts, totalGenerated, recentRuns, recordRun, clearStats }}>
      {children}
    </StatsContext.Provider>
  );
}

// ---------------------------------------------------------------------------
// Hook
// ---------------------------------------------------------------------------

export function useStats(): StatsState {
  const ctx = useContext(StatsContext);
  if (!ctx) throw new Error("useStats must be used within StatsProvider");
  return ctx;
}
