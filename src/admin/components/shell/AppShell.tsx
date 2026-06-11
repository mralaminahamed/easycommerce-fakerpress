import React from "react";
import { useState, useEffect } from "@wordpress/element";
import { useLocation, Outlet } from "react-router-dom";

import { Sidebar } from "@/admin/components/shell/Sidebar";
import { Topbar } from "@/admin/components/shell/Topbar";
import { Toasts } from "@/admin/components/overlays/Toasts";
import { CommandPalette } from "@/admin/components/overlays/CommandPalette";
import { LocalePicker } from "@/admin/components/overlays/LocalePicker";
import { TweaksPanel } from "@/admin/components/overlays/TweaksPanel";
import { BatchTray } from "@/admin/components/overlays/BatchTray";
import { generators } from "@/admin/lib/generators";
import { useStats } from "@/admin/providers/StatsProvider";
import { useBatch } from "@/admin/providers/BatchProvider";

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function getNavCollapsedInit(): boolean {
  try {
    return localStorage.getItem("fp_nav_collapsed") === "1";
  } catch {
    return false;
  }
}

function deriveCrumb(pathname: string): string {
  if (pathname === "/") return "Overview";
  if (pathname === "/settings") return "Settings";
  if (pathname === "/plugins") return "Our Plugins";
  if (pathname.startsWith("/generator/")) {
    const route = pathname.replace("/generator/", "");
    const gen = generators.find((g) => g.route === route);
    return gen ? gen.name : "Generator";
  }
  return "Overview";
}

// ---------------------------------------------------------------------------
// AppShell
// ---------------------------------------------------------------------------

export function AppShell() {
  const { pathname } = useLocation();

  // ---- nav collapsed (persisted) ----
  const [navCollapsed, setNavCollapsedRaw] = useState<boolean>(getNavCollapsedInit);

  const setNavCollapsed = (updater: (v: boolean) => boolean) => {
    setNavCollapsedRaw((prev) => {
      const next = updater(prev);
      try {
        localStorage.setItem("fp_nav_collapsed", next ? "1" : "0");
      } catch {}
      return next;
    });
  };

  // ---- locale (persisted) ----
  const [locale, setLocaleRaw] = useState<string>(() => {
    try {
      const saved = localStorage.getItem("fp_locale");
      if (saved) return saved;
    } catch {}
    return window.easycommerceFakerpressApi?.locale?.label ?? "English (United States)";
  });

  const setLocale = (l: string) => {
    setLocaleRaw(l);
    try {
      localStorage.setItem("fp_locale", l);
    } catch {}
  };

  // ---- overlay open flags ----
  // Actual overlay components are added in a LATER task (Phase 7).
  // For now these flags are toggled by buttons but nothing renders them yet — that is expected.
  const [cmdOpen, setCmdOpen] = useState(false);
  const [tweaksOpen, setTweaksOpen] = useState(false);
  const [localeOpen, setLocaleOpen] = useState(false);
  const [batchOpen, setBatchOpen] = useState(false);

  // ---- global keyboard handler ----
  useEffect(() => {
    const handler = (e: KeyboardEvent) => {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === "k") {
        e.preventDefault();
        setCmdOpen((o) => !o);
      }
      if (e.key === "Escape") {
        setCmdOpen(false);
        setTweaksOpen(false);
        setLocaleOpen(false);
        setBatchOpen(false);
      }
    };
    window.addEventListener("keydown", handler);
    return () => window.removeEventListener("keydown", handler);
  }, []);

  // ---- derived state ----
  const crumb = deriveCrumb(pathname);
  const isGenerator = pathname.startsWith("/generator/");

  const { counts } = useStats();
  const { batch } = useBatch();

  return (
    <div className="fp-app">
      <div className="fp-shell">
        <Sidebar
          collapsed={navCollapsed}
          setCollapsed={setNavCollapsed}
          counts={counts}
          openCmd={() => setCmdOpen(true)}
        />
        <div className="fp-main">
          <Topbar
            crumb={crumb}
            locale={locale}
            onOpenLocale={() => setLocaleOpen(true)}
            onOpenTweaks={() => setTweaksOpen(true)}
            batchCount={batch.length}
            onOpenBatch={() => setBatchOpen(true)}
          />
          {isGenerator ? (
            <div className="fp-main" style={{ overflow: "hidden" }}>
              <Outlet />
            </div>
          ) : (
            <div className="fp-scroll">
              <Outlet />
            </div>
          )}
        </div>
      </div>
      {cmdOpen && <CommandPalette onClose={() => setCmdOpen(false)} />}
      {tweaksOpen && <TweaksPanel onClose={() => setTweaksOpen(false)} />}
      {batchOpen && <BatchTray onClose={() => setBatchOpen(false)} />}
      {localeOpen && (
        <LocalePicker
          onClose={() => setLocaleOpen(false)}
          locale={locale}
          setLocale={setLocale}
        />
      )}
      <Toasts />
    </div>
  );
}

export default AppShell;
