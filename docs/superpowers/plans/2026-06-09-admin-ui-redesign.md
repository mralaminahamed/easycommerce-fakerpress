# Admin UI Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the plain Tailwind admin UI with the Linear/Vercel-style SaaS interface from the Claude Design handoff, wiring net-new features (dashboard+history, live preview, ⌘K palette, batch queue) to real data, plus refreshed brand/wp.org assets.

**Architecture:** Re-express the prototype's design tokens as Tailwind v4 `@theme` + scoped CSS vars on the app root; build custom `fp-*` React components against those tokens, keeping Radix for Select/Switch/Tooltip/Dialog. Rich generator controls render from the existing JSON-Schema `parameterConfig` via a schema→fields adapter. A read-only REST `preview` route returns real faker rows without persistence.

**Tech Stack:** React 18 (`@wordpress/element`), TypeScript, Tailwind v4, Radix UI, react-router-dom (hash router), `@wordpress/api-fetch`, PHP 7.4+ (PSR-4), PHPUnit, Playwright.

**Reference:** Prototype source lives at
`/home/alamin/Downloads/claude-design/ec-fakerpress/desgin/FakerPress for EasyCommerce-handoff/fakerpress-for-easycommerce/project/`.
Files referenced below as `proto:app/<file>` = `project/app/<file>`; `proto:assets/<file>` = `project/assets/<file>`. Port CSS values **exactly** from `proto:app/theme.css` + `proto:app/components.css`.

**Conventions:**
- Every user string via `__()`/`sprintf()`, text domain `easycommerce-fakerpress`.
- After each phase: `yarn build` must pass; PHP phases also `composer phpcs` + `composer phpstan`.
- Preserve/extend `data-testid` hooks for Playwright.
- Commit after every task (messages use Conventional Commits).
- Theme/accent/density attributes go on the **app root container**, never `<html>`.

---

## File Structure

**Created:**
```
src/admin/theme/ThemeProvider.tsx        // applies data-theme/-accent/-density + persists
src/admin/theme/useTheme.ts              // context hook
src/admin/lib/icons.tsx                  // FP icon set (lucide paths from proto:primitives)
src/admin/lib/fieldsFromSchema.ts        // parameterConfig → field descriptors + sections
src/admin/lib/preview.ts                 // /preview fetch + types
src/admin/providers/ToastProvider.tsx
src/admin/providers/BatchProvider.tsx
src/admin/providers/StatsProvider.tsx
src/admin/components/shell/AppShell.tsx
src/admin/components/shell/Sidebar.tsx
src/admin/components/shell/Topbar.tsx
src/admin/components/overlays/CommandPalette.tsx
src/admin/components/overlays/TweaksPanel.tsx
src/admin/components/overlays/LocalePicker.tsx
src/admin/components/overlays/BatchTray.tsx
src/admin/components/overlays/Toasts.tsx
src/admin/components/dashboard/StatCard.tsx
src/admin/components/dashboard/Sparkline.tsx
src/admin/components/dashboard/RecentActivity.tsx
src/admin/components/generator/ConfigColumn.tsx
src/admin/components/generator/FieldSection.tsx
src/admin/components/generator/PreviewTable.tsx
src/admin/components/generator/RunBar.tsx
src/admin/components/generator/fields/{Toggle,Chips,Stepper,NumberField,TextField,RangeField,FieldSelect}.tsx
src/admin/components/ui/{badge,status-pill,section-label}.tsx   // new token primitives
assets/fonts/                            // Geist + Geist Mono woff2 + @font-face
assets/brand/{fakerpress-mark.svg,fakerpress-logo.svg,wp-admin-menu-icon.svg}
includes/Generators/...                  // add build_preview_row() per Core generator
tests/php/src/Controllers/PreviewRouteTest.php
```

**Modified:**
```
src/styles.css                           // design tokens, fonts, base
src/admin/components/App.tsx             // wrap providers + ThemeProvider
src/admin/components/Pages/RootLayout.tsx     // → AppShell
src/admin/components/Pages/HomePage.tsx       // → Dashboard
src/admin/components/Pages/GeneratorPage.tsx  // 2-col + preview + runbar
src/admin/components/Pages/SettingsPage.tsx   // redesigned cards
src/admin/components/Pages/PluginsPage.tsx    // redesigned cards
src/admin/components/home/{StatsBar,GeneratorGrid}.tsx  // restyle to tokens
src/admin/lib/storage.ts                 // run history + counts
src/admin/lib/generators.ts              // add section grouping hints if needed
src/admin/components/ui/*                 // restyle Button/Switch/Select/etc to tokens
includes/Abstracts/Generator.php         // preview() + build_preview_row()
includes/Abstracts/Controller.php        // register preview route + callback
class-easycommerce-fakerpress.php        // admin menu icon, version bump, asset enqueue
assets/icon-128x128.png, icon-256x256.png, icon.svg, banner-*.png
readme.txt                               // version, screenshots captions
```

---

## Phase 1 — Design tokens + ThemeProvider

### Task 1: Self-host Geist fonts

**Files:**
- Create: `assets/fonts/` (woff2 files + `fonts.css`)
- Modify: `src/styles.css`

- [ ] **Step 1: Fetch Geist + Geist Mono woff2**

Download Geist (400,500,600,700) and Geist Mono (400,500,600) woff2 from the Geist repo release (vercel/geist-font, OFL licensed) into `assets/fonts/`. If offline, copy from system or note the source URL in `assets/fonts/SOURCE.txt`.

Run: `ls assets/fonts/*.woff2`
Expected: 7 woff2 files present.

- [ ] **Step 2: Write `assets/fonts/fonts.css`**

```css
@font-face{font-family:"Geist";font-weight:400;font-display:swap;src:url("./Geist-Regular.woff2") format("woff2")}
@font-face{font-family:"Geist";font-weight:500;font-display:swap;src:url("./Geist-Medium.woff2") format("woff2")}
@font-face{font-family:"Geist";font-weight:600;font-display:swap;src:url("./Geist-SemiBold.woff2") format("woff2")}
@font-face{font-family:"Geist";font-weight:700;font-display:swap;src:url("./Geist-Bold.woff2") format("woff2")}
@font-face{font-family:"Geist Mono";font-weight:400;font-display:swap;src:url("./GeistMono-Regular.woff2") format("woff2")}
@font-face{font-family:"Geist Mono";font-weight:500;font-display:swap;src:url("./GeistMono-Medium.woff2") format("woff2")}
@font-face{font-family:"Geist Mono";font-weight:600;font-display:swap;src:url("./GeistMono-SemiBold.woff2") format("woff2")}
```

Note: fonts ship in the plugin and must resolve at runtime. They are imported into the bundle in Task 2; webpack copies them via `file-loader`/asset modules (wp-scripts handles `url()` in CSS). Verify after Task 2 build that woff2 land in `build/`.

- [ ] **Step 3: Commit**

```bash
git add assets/fonts
git commit -m "chore(assets): self-host Geist + Geist Mono fonts"
```

### Task 2: Port design tokens into `src/styles.css`

**Files:**
- Modify: `src/styles.css`

- [ ] **Step 1: Replace token block**

Keep the existing Tailwind imports (lines 1–3) and the Radix collapsible/progress keyframes/utilities. Replace the `@theme` block + `@layer base` body font with the ported tokens. Insert after the Tailwind imports:

```css
@import "../assets/fonts/fonts.css";

@theme {
  --font-sans: "Geist", ui-sans-serif, system-ui, -apple-system, sans-serif;
  --font-mono: "Geist Mono", ui-monospace, "SF Mono", Menlo, monospace;
  /* keep existing --color-wp-admin-* vars for WP integration */
  --color-wp-admin-primary: var(--wp-admin-primary, #2271b1);
  --color-wp-admin-secondary: var(--wp-admin-secondary, #135e96);
  --color-wp-admin-highlight: var(--wp-admin-highlight, #043f54);
  --color-wp-admin-accent: var(--wp-admin-accent, #0a4b78);
}
```

Then add a scoped token block. **All app tokens live under `.fp-root`** (the app container, see Task 4) so dark theme never escapes into WP chrome. Copy every variable from `proto:app/theme.css` lines 7–97 verbatim, but wrap the selectors:

- `:root { … }` (proto lines 7–59) → `.fp-root { … }`
- `[data-theme="dark"] { … }` (60–89) → `.fp-root[data-theme="dark"] { … }`
- `[data-accent="…"] { … }` (91–97) → `.fp-root[data-accent="…"] { … }`

Append the radii (`--r-xs…--r-full`) and `--density` to the `.fp-root` block. Port the keyframes (proto theme.css 132–147) to the global scope (append after existing keyframes). Port scrollbar + `.mono`/`.tnum` + focus-ring rules (proto 113–130) scoped under `.fp-root`.

- [ ] **Step 2: Base app styles**

Append:

```css
.fp-root {
  font-family: var(--font-sans);
  background: var(--bg-2);
  color: var(--text);
  height: 100vh;
  -webkit-font-smoothing: antialiased;
}
.fp-root *, .fp-root *::before, .fp-root *::after { box-sizing: border-box; }
```

- [ ] **Step 3: Build + visual check**

Run: `yarn build`
Expected: build succeeds, woff2 emitted to `build/`. Grep: `ls build/ | grep -i geist` → woff2 present (or referenced via hashed names).

- [ ] **Step 4: Commit**

```bash
git add src/styles.css
git commit -m "feat(ui): port design tokens to Tailwind @theme, scoped to .fp-root"
```

### Task 3: ThemeProvider + useTheme

**Files:**
- Create: `src/admin/theme/useTheme.ts`, `src/admin/theme/ThemeProvider.tsx`

- [ ] **Step 1: Write `useTheme.ts`**

```tsx
import { createContext, useContext } from "@wordpress/element";

export type Theme = "light" | "dark";
export type Accent = "indigo" | "blue" | "violet" | "emerald" | "amber";
export type Density = "comfortable" | "compact";

export interface ThemeState {
  theme: Theme; accent: Accent; density: Density;
  setTheme: (t: Theme) => void;
  setAccent: (a: Accent) => void;
  setDensity: (d: Density) => void;
}

export const ThemeContext = createContext<ThemeState | null>(null);

export function useTheme(): ThemeState {
  const ctx = useContext(ThemeContext);
  if (!ctx) throw new Error("useTheme must be used within ThemeProvider");
  return ctx;
}
```

- [ ] **Step 2: Write `ThemeProvider.tsx`**

```tsx
import { useState, useEffect, useRef } from "@wordpress/element";
import { ThemeContext, type Theme, type Accent, type Density } from "./useTheme";

const LS = (k: string, d: string) => {
  try { return localStorage.getItem(k) ?? d; } catch { return d; }
};
const save = (k: string, v: string) => { try { localStorage.setItem(k, v); } catch {} };

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  const rootRef = useRef<HTMLDivElement>(null);
  const [theme, setTheme] = useState<Theme>(() => LS("fp_theme", "light") as Theme);
  const [accent, setAccent] = useState<Accent>(() => LS("fp_accent", "indigo") as Accent);
  const [density, setDensity] = useState<Density>(() => LS("fp_density", "comfortable") as Density);

  useEffect(() => { save("fp_theme", theme); save("fp_accent", accent); save("fp_density", density); }, [theme, accent, density]);

  return (
    <ThemeContext.Provider value={{ theme, accent, density, setTheme, setAccent, setDensity }}>
      <div ref={rootRef} className="fp-root" data-theme={theme} data-accent={accent} data-density={density}>
        {children}
      </div>
    </ThemeContext.Provider>
  );
}
```

- [ ] **Step 3: Wrap App**

In `src/admin/components/App.tsx`, wrap `<RouterProvider>` with `<ThemeProvider>`:

```tsx
import { ThemeProvider } from "@/admin/theme/ThemeProvider";
// ...
export default function App() {
  return (
    <ThemeProvider>
      <RouterProvider router={router} />
    </ThemeProvider>
  );
}
```

- [ ] **Step 4: Build + verify**

Run: `yarn build` → succeeds. Manually load admin page; confirm `<div class="fp-root" data-theme="light" …>` wraps the app and Geist font renders.

- [ ] **Step 5: Commit**

```bash
git add src/admin/theme src/admin/components/App.tsx
git commit -m "feat(ui): add ThemeProvider (theme/accent/density on app root)"
```

---

## Phase 2 — UI primitives (token-styled)

> Port each control from `proto:app/primitives.jsx` + its CSS from `proto:app/components.css`. Convert `React.createElement` to JSX/TSX, type props, keep class names identical so the ported CSS applies. CSS already lives in `src/styles.css` after Task 2 only for tokens — **component CSS must also be ported**: append the relevant `proto:app/components.css` blocks (scoped by their `.fp-*` classes) under `.fp-root` in `src/styles.css` as each task lands, OR add a dedicated `src/admin/components.css` imported by `index.tsx`. Use the latter to keep `styles.css` focused.

### Task 4: Component CSS file + icon set

**Files:**
- Create: `src/admin/components.css`, `src/admin/lib/icons.tsx`
- Modify: `src/index.tsx`

- [ ] **Step 1: Create `src/admin/components.css`**

Paste `proto:app/components.css` in full, but **prefix every selector with `.fp-root`** (e.g. `.fp-btn` → `.fp-root .fp-btn`) so styles stay scoped. Exclude the `.wp-*` chrome block (proto components.css 5–26) — WordPress provides real chrome. Keep everything from `.fp-app` (line 28) onward.

- [ ] **Step 2: Import it**

In `src/index.tsx` add after `import './styles.css';`:
```tsx
import '@/admin/components.css';
```

- [ ] **Step 3: Write `src/admin/lib/icons.tsx`**

Port the `ICONS` map + `Icon` component from `proto:app/primitives.jsx` lines 7–71 to TSX:

```tsx
const ICONS: Record<string, string> = {
  box: '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
  // … copy every entry verbatim from proto:primitives.jsx ICONS …
};

export function Icon({ name, size = 18, stroke = 1.75, fill = "none", className = "", style = {} }:
  { name: string; size?: number; stroke?: number; fill?: string; className?: string; style?: React.CSSProperties }) {
  const p = ICONS[name] ?? ICONS.box;
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill={fill} stroke="currentColor"
      strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round" className={className}
      style={{ flexShrink: 0, display: "block", ...style }}
      dangerouslySetInnerHTML={{ __html: p }} />
  );
}
export type IconName = keyof typeof ICONS;
```

- [ ] **Step 4: Build + commit**

Run: `yarn build` → succeeds.
```bash
git add src/admin/components.css src/admin/lib/icons.tsx src/index.tsx
git commit -m "feat(ui): scoped component CSS + FP icon set"
```

### Task 5: Field control primitives

**Files:**
- Create: `src/admin/components/generator/fields/Toggle.tsx`, `Chips.tsx`, `Stepper.tsx`, `NumberField.tsx`, `TextField.tsx`, `RangeField.tsx`, `FieldSelect.tsx`

- [ ] **Step 1: Port each control to TSX**

Translate `proto:app/primitives.jsx`:
- `Toggle` (95–107) → `Toggle.tsx`, props `{ checked: boolean; onChange: (v:boolean)=>void; label?: string; hint?: string }`.
- `Stepper` (110–120) → `Stepper.tsx`, props `{ value:number; onChange:(v:number)=>void; min?:number; max?:number; step?:number }`.
- `Chips` (123–134) → `Chips.tsx`, props `{ options:string[]; value:string[]; onChange:(v:string[])=>void }`.
- `NumField` (137–147) → `NumberField.tsx`, props `{ value:number|string; onChange:(v:string)=>void; prefix?:string; suffix?:string; ph?:string; width?:number }`.
- `TextField` (148–153) → `TextField.tsx`, props `{ value:string; onChange:(v:string)=>void; ph?:string }`.
- `RangeField` (156–186) → `RangeField.tsx`, props `{ value:{lo:number;hi:number}; min:number; max:number; prefix?:string; suffix?:string; onChange:(v:{lo:number;hi:number})=>void }`. Use `useRef`/pointer events as in proto.
- `Select` (189–211) → `FieldSelect.tsx`, props `{ value:string; options:string[]; onChange:(v:string)=>void; width?:number }`. Keep the custom popover (not Radix) to match the prototype exactly.

Each imports `Icon` from `@/admin/lib/icons`, uses `useState`/`useRef`/`useEffect` from `@wordpress/element`, and keeps identical class names.

- [ ] **Step 2: Build + visual smoke**

Run: `yarn build` → succeeds. (No unit tests for pure presentational controls; covered by Playwright in Phase 10.)

- [ ] **Step 3: Commit**

```bash
git add src/admin/components/generator/fields
git commit -m "feat(ui): port field control primitives to TSX"
```

### Task 6: Display primitives + Button/Switch/Select restyle

**Files:**
- Create: `src/admin/components/ui/badge.tsx`, `status-pill.tsx`, `section-label.tsx`
- Modify: `src/admin/components/ui/button.tsx`, `switch.tsx`, `select.tsx`

- [ ] **Step 1: Port Badge / StatusPill / SectionLabel + tone()**

From `proto:app/primitives.jsx` 73–81, 213–225. Create `src/admin/lib/tone.ts` exporting `tone(text:string)`. Then:
- `badge.tsx` → `Badge({ children, kind, soft })`, class `fp-badge tone-${kind}`.
- `status-pill.tsx` → `StatusPill({ children })` using `tone()`, class `fp-status tone-${k}` + dot.
- `section-label.tsx` → `SectionLabel({ children, right? })`, class `fp-seclabel`.

- [ ] **Step 2: Restyle Button/Switch to FP classes**

Update `ui/button.tsx` to render `fp-btn fp-btn-{variant} fp-btn-{size}` (variants: primary/outline/ghost/soft/danger; sizes sm/md/lg) matching `proto:app/primitives.jsx` `Btn` (84–92). Keep existing prop API additive so `ActionPanel` etc. still compile, or update callers in their phases. Switch can keep Radix but restyle classes to `fp-switch`/`fp-knob`. Select: keep Radix wrapper but apply token styling, OR standardize on `FieldSelect` — prefer `FieldSelect` for generator fields; keep Radix `Select` only where already used until its caller is rebuilt.

- [ ] **Step 3: Build + commit**

Run: `yarn build` → succeeds.
```bash
git add src/admin/components/ui src/admin/lib/tone.ts
git commit -m "feat(ui): token-styled badge/status/button primitives"
```

---

## Phase 3 — App shell

### Task 7: Sidebar

**Files:**
- Create: `src/admin/components/shell/Sidebar.tsx`

- [ ] **Step 1: Build Sidebar from `proto:app/chrome.jsx` FPNav (52–94)**

Props: `{ collapsed:boolean; setCollapsed:(f:(v:boolean)=>boolean)=>void; counts:Record<string,number>; openCmd:()=>void }`. Use `react-router-dom` `useLocation`/`useNavigate` for active state + navigation instead of the proto `route/go`. Brand mark uses `Icon name="sparkles"`. Group generators via `generators` from `@/admin/lib/generators` (group by `category`, order by `order`). Render `fp-nav`, `fp-brand`, `fp-cmd-btn` (calls `openCmd`), grouped `fp-nav-item`s with `counts[route]`, footer items Settings + Our Plugins. Persist collapsed to `localStorage` key `fp_nav_collapsed`.

Map generator → nav: label `g.name`, icon — add an `iconName` to each generator (Task 7a) OR map lucide icon to FP icon name. **Decision:** add `iconName: IconName` field to `Generator` type + each entry in `generators.ts` (matches proto `icon` strings: box/users/cart/tag/sliders/truck/dollar/card/bag/layers/receipt/scroll/pin/star). Do this in Step 2.

- [ ] **Step 2: Add `iconName` to generators**

Modify `src/admin/types/index.ts` `Generator` interface: add `iconName: string;`. Modify `src/admin/lib/generators.ts`: add `iconName` to each entry — products:`"box"`, customers:`"users"`, orders:`"cart"`, coupons:`"tag"`, product-variations:`"sliders"`, shipping-plans:`"truck"`, tax-classes:`"dollar"`, transaction:`"card"`, cart-sessions:`"bag"`, attributes:`"layers"`, refunds:`"receipt"`, logs:`"scroll"`, locations:`"pin"`, product-reviews:`"star"`. Keep existing `icon` (lucide) for back-compat until callers drop it.

- [ ] **Step 3: Build + commit**

Run: `yarn build` → succeeds.
```bash
git add src/admin/components/shell/Sidebar.tsx src/admin/types/index.ts src/admin/lib/generators.ts
git commit -m "feat(shell): collapsible grouped sidebar nav"
```

### Task 8: Topbar + AppShell, replace RootLayout

**Files:**
- Create: `src/admin/components/shell/Topbar.tsx`, `src/admin/components/shell/AppShell.tsx`
- Modify: `src/admin/components/Pages/RootLayout.tsx`

- [ ] **Step 1: Topbar from `proto:app/chrome.jsx` FPTopbar (97–119)**

Props: `{ crumb:string; locale:string; onOpenLocale:()=>void; onOpenTweaks:()=>void; batchCount:number; onOpenBatch:()=>void }`. Theme toggle uses `useTheme()`. Breadcrumb root links to `/`. Render `fp-topbar` with crumbs, batch chip (if `batchCount>0`), locale pill, theme toggle (`sun`/`moon`), tweaks button.

- [ ] **Step 2: AppShell**

Compose: `fp-app > fp-shell > [Sidebar, fp-main > [Topbar, content]]`. Owns: `navCollapsed` state, `cmdOpen/tweaksOpen/localeOpen/batchOpen` overlay state, global ⌘K + Escape listener (port from `proto:app/app.jsx` 37–43). Derive `crumb` + `counts` from route + StatsProvider. Renders `<Outlet />` inside `fp-scroll` for non-generator routes; generator route manages its own scroll (sticky run bar) — detect via `useLocation().pathname.startsWith("/generator/")` and render Outlet without `fp-scroll` wrapper. Mount overlays (added Phase 7) conditionally; for now leave overlay slots as no-ops / TODO wired in Phase 7 (use placeholder `cmdOpen && null`). To avoid placeholders: implement AppShell overlay mounting in Phase 7 and keep AppShell here with the state + handlers wired to buttons that toggle state (no overlay components yet — acceptable: buttons toggle state that nothing renders until Phase 7).

- [ ] **Step 3: Replace RootLayout with AppShell**

Rewrite `RootLayout.tsx` to render `<AppShell />`. Remove the old gray header nav.

- [ ] **Step 4: Build + verify**

Run: `yarn build` → succeeds. Load admin: sidebar + topbar render, nav works, theme toggle flips `data-theme`, collapse works. Overlay buttons toggle state (no visible overlay yet).

- [ ] **Step 5: Commit**

```bash
git add src/admin/components/shell src/admin/components/Pages/RootLayout.tsx
git commit -m "feat(shell): AppShell with topbar, replace RootLayout"
```

---

## Phase 4 — Dashboard + run history

### Task 9: Extend storage for run history + counts

**Files:**
- Modify: `src/admin/lib/storage.ts`, `src/admin/types/index.ts`
- Test: `src/admin/lib/__tests__/storage.test.ts` (if jest configured; else manual)

- [ ] **Step 1: Inspect existing storage API**

Read `src/admin/lib/storage.ts`. It already has `addRun`, `getRuns`, `incrementStats`. Add: `getCounts(): Record<string,number>` (per-route totals), `getTotalGenerated(): number`, `getRecentRuns(limit:number): GlobalRun[]` (across all generators, newest first), `clearStats(): void`. Add type `GlobalRun { route:string; count:number; timestamp:number; success:boolean; locale?:string; seed?:string }` to types.

- [ ] **Step 2: Write test (if jest present)**

Check `package.json` for a `test`/jest script. If present:
```ts
import { addRun, getCounts, getTotalGenerated, clearStats } from "@/admin/lib/storage";
beforeEach(() => { localStorage.clear(); clearStats(); });
test("counts accumulate per route", () => {
  addRun("products", { route:"products", count:10, timestamp:Date.now(), success:true });
  addRun("products", { route:"products", count:5, timestamp:Date.now(), success:true });
  expect(getCounts().products).toBe(15);
  expect(getTotalGenerated()).toBe(15);
});
```
Run: `yarn test storage` → FAIL (functions missing). If no jest, skip to Step 3 and verify manually.

- [ ] **Step 3: Implement the additions**, keeping existing exports intact.

- [ ] **Step 4: Verify**

Run: `yarn test storage` → PASS (or `yarn build` + manual localStorage check).

- [ ] **Step 5: Commit**

```bash
git add src/admin/lib/storage.ts src/admin/types/index.ts src/admin/lib/__tests__
git commit -m "feat(storage): run history, per-route counts, totals"
```

### Task 10: StatsProvider

**Files:**
- Create: `src/admin/providers/StatsProvider.tsx`

- [ ] **Step 1: Implement**

Context exposing `{ counts, totalGenerated, recentRuns, recordRun(route,count,success,locale?,seed?), clearStats() }`. Backed by storage (Task 9); holds React state mirror so dashboard re-renders on record. `recordRun` calls `addRun`+`incrementStats` then updates state. Expose `useStats()` hook.

- [ ] **Step 2: Wrap in App**

Add `<StatsProvider>` inside `<ThemeProvider>` (outside RouterProvider) in `App.tsx`.

- [ ] **Step 3: Build + commit**

```bash
git add src/admin/providers/StatsProvider.tsx src/admin/components/App.tsx
git commit -m "feat(stats): StatsProvider for dashboard + counts"
```

### Task 11: Dashboard components + page

**Files:**
- Create: `src/admin/components/dashboard/Sparkline.tsx`, `StatCard.tsx`, `RecentActivity.tsx`
- Modify: `src/admin/components/home/GeneratorGrid.tsx`, `src/admin/components/Pages/HomePage.tsx`

- [ ] **Step 1: Sparkline + StatCard**

Port `Spark` (`proto:app/dashboard.jsx` 6–23) → `Sparkline.tsx` props `{ data:number[]; w?:number; h?:number; color?:string }`. Port `StatCard` (25–39) → props `{ iconName:string; label:string; value:number; empty:boolean; delta:number; spark:number[]; accentVar?:string }`.

- [ ] **Step 2: RecentActivity**

Port the recent-activity block (`proto:app/dashboard.jsx` 77–93) → `RecentActivity.tsx` props `{ runs:GlobalRun[] }`, using `timeAgo` (reuse from `lib/utils` or add). Rows link to `/generator/:route` via `useNavigate`.

- [ ] **Step 3: GeneratorGrid restyle**

Rewrite `home/GeneratorGrid.tsx` to render the `fp-gen-card` grid grouped by category (port `proto:app/dashboard.jsx` 95–113), using `iconName`, `counts[route]`, navigate on click.

- [ ] **Step 4: Dashboard page**

Rewrite `HomePage.tsx` → render `fp-page wide`: page head ("FakerPress" + sub + "New generation" button → `/generator/products`), stat row (Products/Customers/Orders + Total Generated from `useStats()`), Recent activity, GeneratorGrid. Port layout from `proto:app/dashboard.jsx` 57–113.

- [ ] **Step 5: Build + verify**

Run: `yarn build` → succeeds. Load `/`: dashboard renders with stat cards, sparklines, empty recent state, generator grid.

- [ ] **Step 6: Commit**

```bash
git add src/admin/components/dashboard src/admin/components/home/GeneratorGrid.tsx src/admin/components/Pages/HomePage.tsx
git commit -m "feat(dashboard): overview with stats, sparklines, recent activity, grid"
```

---

## Phase 5 — Generator page (config + run bar)

### Task 12: Schema→fields adapter

**Files:**
- Create: `src/admin/lib/fieldsFromSchema.ts`
- Test: `src/admin/lib/__tests__/fieldsFromSchema.test.ts`

- [ ] **Step 1: Define output types**

```ts
export type FieldType = "toggle" | "chips" | "range" | "select" | "number" | "text";
export interface FieldDescriptor {
  key: string;            // dot-path into params, e.g. "price_range" or "inventory.manage_stock"
  type: FieldType;
  label: string;
  section: string;
  options?: string[];     // select/chips
  min?: number; max?: number; // range/number
  prefix?: string; suffix?: string;
  default?: any;
  dependsOn?: Record<string, any>;
}
export interface FieldSection { name: string; fields: FieldDescriptor[]; }
export function fieldsFromSchema(config: Record<string, ParameterConfig>): FieldSection[];
```

- [ ] **Step 2: Write failing test**

```ts
import { fieldsFromSchema } from "@/admin/lib/fieldsFromSchema";
test("maps enum string → select", () => {
  const s = fieldsFromSchema({ product_type: { type:"string", enum:["a","b"], default:"a", description:"Type" } });
  const f = s.flatMap(x=>x.fields).find(f=>f.key==="product_type")!;
  expect(f.type).toBe("select"); expect(f.options).toEqual(["a","b"]);
});
test("maps array enum → chips", () => {
  const s = fieldsFromSchema({ types:{ type:"array", items:{ enum:["x","y"] }, default:["x"], description:"Types" } });
  expect(s.flatMap(x=>x.fields)[0].type).toBe("chips");
});
test("maps {min,max} object → range", () => {
  const s = fieldsFromSchema({ price_range:{ type:"object", properties:{ min:{type:"number",default:10}, max:{type:"number",default:500} }, description:"Price" } });
  const f = s.flatMap(x=>x.fields).find(f=>f.key==="price_range")!;
  expect(f.type).toBe("range"); expect(f.min).toBe(10); expect(f.max).toBe(500);
});
test("boolean → toggle", () => {
  const s = fieldsFromSchema({ flag:{ type:"boolean", default:true, description:"Flag" } });
  expect(s.flatMap(x=>x.fields)[0].type).toBe("toggle");
});
```

Run: `yarn test fieldsFromSchema` → FAIL.

- [ ] **Step 3: Implement adapter**

Rules: `type:string`+`enum` → select. `type:array` → chips (options from `items.enum`). `type:object` with exactly `{min,max}` numeric props → range (min/max from defaults). `type:object` (other) → recurse, prefixing child keys with `parent.` and grouping under a section = humanized parent key. `type:boolean` → toggle. `type:integer|number` (no enum) → number. else → text. Section name = humanized top-level key (or `title`/`description`-derived). Respect `dependsOn` (carry through; hidden when unmet — handled in render). Humanize: snake_case → Title Case.

- [ ] **Step 4: Run tests → PASS. Commit.**

```bash
git add src/admin/lib/fieldsFromSchema.ts src/admin/lib/__tests__/fieldsFromSchema.test.ts
git commit -m "feat(generator): schema→fields adapter"
```

### Task 13: ConfigColumn + FieldSection + Field renderer

**Files:**
- Create: `src/admin/components/generator/FieldSection.tsx`, `ConfigColumn.tsx`

- [ ] **Step 1: Field renderer**

In `ConfigColumn.tsx` (or a `Field.tsx`), port the `Field` switch from `proto:app/generator.jsx` 16–27, dispatching `FieldDescriptor.type` → the Phase-2 controls. Read/write values via dot-path get/set helpers against the `params` object (handle nested keys like `inventory.manage_stock`).

- [ ] **Step 2: ConfigColumn**

Port `proto:app/generator.jsx` 89–115: config head (icon, title, Popular tag), description, dependency note (`DEP` map 7–13 — port as a `route→note` map), sections from `fieldsFromSchema(generator.parameterConfig)`, two-number-section special case (`allNum`) + dup-label case. Props: `{ generator; params; setField(key,value); }`.

- [ ] **Step 3: Build + commit**

```bash
git add src/admin/components/generator/ConfigColumn.tsx src/admin/components/generator/FieldSection.tsx
git commit -m "feat(generator): config column rendering rich controls from schema"
```

### Task 14: RunBar + GeneratorPage rebuild (real /generate)

**Files:**
- Create: `src/admin/components/generator/RunBar.tsx`
- Modify: `src/admin/components/Pages/GeneratorPage.tsx`

- [ ] **Step 1: RunBar**

Port `proto:app/generator.jsx` sticky bar (139–154): Count `Stepper`, Seed `TextField`, Metadata `Toggle`, spacer, "Add to batch" (Phase 7 — wire to BatchProvider; until then a no-op button is acceptable but prefer wiring after Phase 7; for this task render the button calling an `onAddBatch` prop the page passes), "Generate N items" primary. Props: `{ count; seed; meta; onCount; onSeed; onMeta; onGenerate; onAddBatch; generating }`.

- [ ] **Step 2: GeneratorPage**

Rewrite to the 2-col layout (`proto:app/generator.jsx` 88–137, 62–87 state): `fp-gen-main > fp-gen-body > fp-gen-wrap[ConfigColumn | PreviewColumn]` + `RunBar`. State: `params` (init from `fieldsFromSchema` defaults), `count/seed/meta` (init from `getSettings()`), `generating/progress`. `onGenerate` → POST `/{route}/generate` (reuse logic from current `ActionPanel` 60–109), then `useStats().recordRun(...)` + toast (toast wired Phase 7; until then console/no-op). Preview column = placeholder until Task 16 (render empty `fp-preview-col` with "Live preview" header now; PreviewTable added next task). Remove old `ParamsPanel`/`ActionPanel`/`GeneratorSidebar` usage from the page.

- [ ] **Step 3: Build + verify**

Run: `yarn build` → succeeds. Generator page: rich controls render from schema, count/seed/meta work, Generate POSTs and records a run (visible on Dashboard).

- [ ] **Step 4: Commit**

```bash
git add src/admin/components/generator/RunBar.tsx src/admin/components/Pages/GeneratorPage.tsx
git commit -m "feat(generator): 2-col page with run bar, real generate wiring"
```

---

## Phase 6 — Live preview (real endpoint)

### Task 15: PHP preview route + Core build_preview_row

**Files:**
- Modify: `includes/Abstracts/Generator.php`, `includes/Abstracts/Controller.php`
- Modify: `includes/Generators/Product.php`, `Customer.php`, `Order.php`, `Coupon.php`
- Test: `tests/php/src/Controllers/PreviewRouteTest.php`

- [ ] **Step 1: Write failing PHP test**

```php
// tests/php/src/Controllers/PreviewRouteTest.php
public function test_preview_returns_columns_and_rows_without_persisting() {
    $before = wp_count_posts('product')->publish ?? 0;
    $req = new \WP_REST_Request('POST', '/easycommerce-fakerpress/v1/products/preview');
    $req->set_param('count', 5);
    $res = rest_get_server()->dispatch($req);
    $this->assertSame(200, $res->get_status());
    $data = $res->get_data();
    $this->assertArrayHasKey('columns', $data);
    $this->assertArrayHasKey('rows', $data);
    $this->assertCount(5, $data['rows']);
    $after = wp_count_posts('product')->publish ?? 0;
    $this->assertSame($before, $after); // no persistence
}
```

Run: `phpunit tests/php/src/Controllers/PreviewRouteTest.php` → FAIL (route 404).

- [ ] **Step 2: Generator::preview + build_preview_row**

In `includes/Abstracts/Generator.php` add:
```php
public function preview( int $count ): array {
    $count   = max( 1, min( 25, $count ) );
    $rows    = array();
    $columns = $this->get_preview_columns();
    for ( $i = 0; $i < $count; $i++ ) {
        $rows[] = $this->build_preview_row();
    }
    return array( 'columns' => $columns, 'rows' => $rows );
}

/** Default generic preview columns/row — overridden per generator. */
protected function get_preview_columns(): array {
    return array(
        array( 'key' => 'id',    'label' => __( 'ID', 'easycommerce-fakerpress' ) ),
        array( 'key' => 'value', 'label' => __( 'Value', 'easycommerce-fakerpress' ) ),
    );
}
protected function build_preview_row(): array {
    return array(
        'id'    => array( 'v' => $this->faker->randomNumber( 5 ), 'kind' => 'mono' ),
        'value' => array( 'v' => $this->faker->words( 2, true ), 'kind' => 'text' ),
    );
}
```
`build_preview_row()` must use only `$this->faker` + `load_sample_data()` and **must not** call EC model save/persist methods.

- [ ] **Step 3: Controller preview route**

In `includes/Abstracts/Controller.php` `register_routes()`, register a second route mirroring `generate` at `'/' . $rest_base . '/preview'` → callback `preview_items` (same permission check). Implement:
```php
public function preview_items( WP_REST_Request $request ) {
    $generator = $this->get_generator_instance();
    $params    = $request->get_params();
    if ( isset( $params['locale'] ) ) { $generator->set_locale( (string) $params['locale'] ); }
    $generator->set_generation_params( $params );
    $count = isset( $params['count'] ) ? (int) $params['count'] : 10;
    return rest_ensure_response( $generator->preview( $count ) );
}
```
Reuse `get_generation_params()` for arg schema (count/locale/seed). Ensure `set_generation_params` here does not trigger any writes.

- [ ] **Step 4: Core build_preview_row overrides**

In each of Product/Customer/Order/Coupon generator, add `get_preview_columns()` + `build_preview_row()` returning cells matching the prototype columns (`proto:app/data.js` `cols`+`row` for each), using real faker + sample data, **no persistence**:
- Product: id(mono), name, type(badge), price(money), stock(num), category.
- Customer: name, email(mono), type(badge), age(num), location, orders(num).
- Order: id(mono), customer, status(status), items(num), total(money), country(mono).
- Coupon: code(mono), type(badge), value(money), min(money), uses(num).

- [ ] **Step 5: Run tests → PASS**

Run: `phpunit tests/php/src/Controllers/PreviewRouteTest.php` → PASS. Also `composer phpcs includes/` + `composer phpstan` → clean.

- [ ] **Step 6: Commit**

```bash
git add includes tests/php/src/Controllers/PreviewRouteTest.php
git commit -m "feat(api): read-only preview route + Core preview rows"
```

### Task 16: PreviewTable (frontend)

**Files:**
- Create: `src/admin/lib/preview.ts`, `src/admin/components/generator/PreviewTable.tsx`
- Modify: `src/admin/components/Pages/GeneratorPage.tsx`

- [ ] **Step 1: preview.ts**

```ts
import apiFetch from "@wordpress/api-fetch";
export interface PreviewCell { v: string | number; kind?: string; }
export interface PreviewColumn { key: string; label: string; }
export interface PreviewData { columns: PreviewColumn[]; rows: Record<string, PreviewCell>[]; }
export async function fetchPreview(route: string, params: Record<string, any>): Promise<PreviewData> {
  return apiFetch({ path: `/easycommerce-fakerpress/v1/${route}/preview`, method: "POST", data: params }) as Promise<PreviewData>;
}
```

- [ ] **Step 2: PreviewTable**

Port `PreviewTable` + `Cell` rendering from `proto:app/generator.jsx` 29–60, but fetch from `/preview` (debounced ~400ms on `params/count/seed/meta` change). Render `fp-table-card`/`fp-table` with cell-kind classes (mono/money/num/stars/badge/status). Header live-dot + footer (showing N of count, seed). Loading + error states. "Shuffle" prop re-rolls seed.

- [ ] **Step 3: Wire into GeneratorPage preview column**

Replace the placeholder preview column with `<PreviewTable route={generator.route} params={params} count={count} seed={seed} meta={meta} />` plus the generating overlay (`proto:app/generator.jsx` 126–134).

- [ ] **Step 4: Build + verify**

Run: `yarn build` → succeeds. Generator page shows real preview rows; changing a param refetches; shuffle re-rolls.

- [ ] **Step 5: Commit**

```bash
git add src/admin/lib/preview.ts src/admin/components/generator/PreviewTable.tsx src/admin/components/Pages/GeneratorPage.tsx
git commit -m "feat(generator): live preview table from real endpoint"
```

---

## Phase 7 — Overlays

### Task 17: ToastProvider + Toasts

**Files:**
- Create: `src/admin/providers/ToastProvider.tsx`, `src/admin/components/overlays/Toasts.tsx`
- Modify: `src/admin/components/App.tsx`, `GeneratorPage.tsx` (emit toast on generate)

- [ ] **Step 1: ToastProvider**

Context `{ toast(title, sub?) }`. Holds `toasts[]`, auto-dismiss 4200ms (port `proto:app/app.jsx` 64–69). `useToast()` hook.

- [ ] **Step 2: Toasts component**

Port `proto:app/overlays.jsx` Toasts (158–166). Mount once in AppShell.

- [ ] **Step 3: Wrap + wire**

Add `<ToastProvider>` in App tree. In GeneratorPage `onGenerate` success → `toast('Generated N ...','Added to your EasyCommerce store')`.

- [ ] **Step 4: Build + commit**

```bash
git add src/admin/providers/ToastProvider.tsx src/admin/components/overlays/Toasts.tsx src/admin/components/App.tsx src/admin/components/Pages/GeneratorPage.tsx
git commit -m "feat(overlays): toasts"
```

### Task 18: CommandPalette + LocalePicker

**Files:**
- Create: `src/admin/components/overlays/CommandPalette.tsx`, `LocalePicker.tsx`
- Modify: `src/admin/components/shell/AppShell.tsx`

- [ ] **Step 1: CommandPalette**

Port `proto:app/overlays.jsx` 8–62. Items = pages (Overview/Settings/Our Plugins) + generators. Selecting navigates via `useNavigate` then closes. Keyboard up/down/enter/escape.

- [ ] **Step 2: LocalePicker**

Port 101–110. Options from `window.easycommerceFakerpressApi.locale.allLocales`. On select set app locale (lift locale to AppShell state + persist `fp_locale`); Topbar pill shows it.

- [ ] **Step 3: Mount in AppShell**

Render `cmdOpen && <CommandPalette …/>`, `localeOpen && <LocalePicker …/>` using the state from Task 8.

- [ ] **Step 4: Build + verify** ⌘K opens palette, navigates; locale picker changes topbar pill.

- [ ] **Step 5: Commit**

```bash
git add src/admin/components/overlays/CommandPalette.tsx src/admin/components/overlays/LocalePicker.tsx src/admin/components/shell/AppShell.tsx
git commit -m "feat(overlays): command palette + locale picker"
```

### Task 19: TweaksPanel

**Files:**
- Create: `src/admin/components/overlays/TweaksPanel.tsx`
- Modify: `AppShell.tsx`

- [ ] **Step 1: Port TweaksPanel** (`proto:app/overlays.jsx` 64–98). Accent swatches, appearance seg (light/dark), density seg — all driven by `useTheme()`. Mount `tweaksOpen && <TweaksPanel/>` in AppShell.

- [ ] **Step 2: Build + verify** accent/theme/density changes apply live + persist across reload.

- [ ] **Step 3: Commit**

```bash
git add src/admin/components/overlays/TweaksPanel.tsx src/admin/components/shell/AppShell.tsx
git commit -m "feat(overlays): tweaks panel (accent/appearance/density)"
```

### Task 20: BatchProvider + BatchTray

**Files:**
- Create: `src/admin/providers/BatchProvider.tsx`, `src/admin/components/overlays/BatchTray.tsx`
- Modify: `App.tsx`, `AppShell.tsx`, `RunBar.tsx`, `GeneratorPage.tsx`

- [ ] **Step 1: BatchProvider**

Context `{ batch:{route,count}[]; add(route,count); remove(i); setCount(i,n); clear(); runAll(onProgress) }`. `runAll` issues `/{route}/generate` sequentially (reuse generate params building), records each via StatsProvider, toasts on completion. `useBatch()` hook.

- [ ] **Step 2: BatchTray**

Port `proto:app/overlays.jsx` 112–155. Lists queue, stepper per item, remove, total, "Run batch" → `runAll`. Progress bar.

- [ ] **Step 3: Wire**

`RunBar` "Add to batch" → `useBatch().add(route, count)` + toast. Topbar batch chip shows `batch.length`, opens tray. Mount `batchOpen && <BatchTray/>` in AppShell. Add `<BatchProvider>` to App tree.

- [ ] **Step 4: Build + verify** add 2 generators to batch, run, both record runs + toast.

- [ ] **Step 5: Commit**

```bash
git add src/admin/providers/BatchProvider.tsx src/admin/components/overlays/BatchTray.tsx src/admin/components/App.tsx src/admin/components/shell/AppShell.tsx src/admin/components/generator/RunBar.tsx src/admin/components/Pages/GeneratorPage.tsx
git commit -m "feat(overlays): batch queue + tray"
```

---

## Phase 8 — Settings + Plugins redesign

### Task 21: SettingsPage redesign

**Files:**
- Modify: `src/admin/components/Pages/SettingsPage.tsx`

- [ ] **Step 1: Rebuild** from `proto:app/pages.jsx` SettingsPage (7–80): cards — Generation defaults (count/locale/seed/meta → persist via `lib/settings`), Run history (max runs), Sample data (sync status — wire to existing sync REST if present, else show status read-only), About (version from `window`/constant), Danger zone (Clear run history → `useStats().clearStats()`; Reset settings). Use token card classes `fp-set-card`. Keep existing settings persistence (`lib/settings.ts`).

- [ ] **Step 2: Build + verify** settings save; clear-history empties dashboard.

- [ ] **Step 3: Commit**

```bash
git add src/admin/components/Pages/SettingsPage.tsx
git commit -m "feat(settings): redesigned settings cards"
```

### Task 22: PluginsPage redesign

**Files:**
- Modify: `src/admin/components/Pages/PluginsPage.tsx`

- [ ] **Step 1: Rebuild** from `proto:app/pages.jsx` PluginsPage (104–124) — `fp-plugins-grid` of `fp-plugin-card`. Keep the existing plugin data source the current PluginsPage uses (read it first); only restyle. If current page fetches from wp.org API, preserve that; map fields to the card.

- [ ] **Step 2: Build + commit**

```bash
git add src/admin/components/Pages/PluginsPage.tsx
git commit -m "feat(plugins): redesigned plugin cards"
```

---

## Phase 9 — Assets + wp.org

### Task 23: Brand assets + admin menu icon

**Files:**
- Create: `assets/brand/fakerpress-mark.svg`, `fakerpress-logo.svg`, `wp-admin-menu-icon.svg`
- Modify: `class-easycommerce-fakerpress.php`

- [ ] **Step 1: Copy SVGs** from `proto:assets/` into `assets/brand/`. Use `fakerpress-mark.svg` in Sidebar brand + Dashboard header (replace the `sparkles` Icon if desired — optional; keep `sparkles` if simpler). 

- [ ] **Step 2: Admin menu icon**

In `class-easycommerce-fakerpress.php` `add_menu_page` (line ~162), set the icon arg to the menu SVG. Preferred: base64 data URI of `assets/brand/wp-admin-menu-icon.svg` so it recolors with WP states, e.g.:
```php
$icon = 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( EASYCOMMERCE_FAKERPRESS_PATH . 'assets/brand/wp-admin-menu-icon.svg' ) );
```
Pass `$icon` as the 6th `add_menu_page` arg. Verify the SVG uses `fill="currentColor"`/no hardcoded color so WP recolors it; if it has a fixed fill, edit to `currentColor`.

- [ ] **Step 3: Verify** admin menu shows new icon; matches WP menu hover/active states.

- [ ] **Step 4: Commit**

```bash
git add assets/brand class-easycommerce-fakerpress.php
git commit -m "feat(brand): logo + WP admin menu icon"
```

### Task 24: wp.org directory icon + banners

**Files:**
- Modify: `assets/icon-128x128.png`, `assets/icon-256x256.png`, `assets/banner-1544x500.png`, `assets/banner-772x250.png`
- Create: `assets/icon.svg`

- [ ] **Step 1: Replace** the four PNGs with the prototype's (`proto:assets/icon-128x128.png`, `icon-256x256.png`, `banner-1544x500.png`, `banner-772x250.png`) and add `assets/icon.svg`. These map to the wp.org `assets/` SVN dir (the repo already uses `assets/` for directory art per `.distignore`/release flow — confirm by reading `.distignore`).

- [ ] **Step 2: Commit**

```bash
git add assets/icon-128x128.png assets/icon-256x256.png assets/icon.svg assets/banner-1544x500.png assets/banner-772x250.png
git commit -m "chore(assets): update wp.org directory icon + banners"
```

### Task 25: Version bump + readme

**Files:**
- Modify: `readme.txt`, `class-easycommerce-fakerpress.php`, `easycommerce-fakerpress.php`, `package.json`, `composer.json`

- [ ] **Step 1: Bump version** (e.g. 2.1.0 → 2.2.0) in the plugin header (`easycommerce-fakerpress.php`), the version constant, `readme.txt` `Stable tag`, `package.json`, `composer.json`. Add a `== Changelog ==` entry summarizing the redesign.

- [ ] **Step 2: Update screenshot captions** in `readme.txt` `== Screenshots ==` to describe the new dashboard/generator/preview/command-palette UI (final PNGs regenerated in Task 27).

- [ ] **Step 3: Commit**

```bash
git add readme.txt class-easycommerce-fakerpress.php easycommerce-fakerpress.php package.json composer.json
git commit -m "chore(release): bump version + changelog for redesign"
```

---

## Phase 10 — Tests + polish

### Task 26: Update Playwright e2e

**Files:**
- Modify: existing Playwright specs under `tests/` (locate first)

- [ ] **Step 1: Locate + run current suite**

Run: `yarn playwright test` (or the project's e2e script — check `package.json`). Note failures from the structural changes.

- [ ] **Step 2: Update selectors/flows**

Add/restore `data-testid`s on new components: `app-shell`, `sidebar`, `topbar`, `generator-runbar`, `generate-btn`, `preview-table`, `command-palette`, `batch-tray`, `tweaks-panel`, `stat-card`. Update specs to: nav via sidebar, open a generator, assert preview rows render, run generate, assert toast + dashboard count, open ⌘K and navigate, add-to-batch + run, toggle theme asserts `data-theme` on `.fp-root`.

- [ ] **Step 3: Run → all pass.**

Run: `yarn playwright test` → green.

- [ ] **Step 4: Commit**

```bash
git add tests
git commit -m "test(e2e): update Playwright for redesigned UI"
```

### Task 27: Screenshots + final polish

**Files:**
- Modify: `assets/screenshot-*.png`

- [ ] **Step 1: Polish pass**

Manually verify across: light/dark × accents indigo/blue/violet/emerald/amber × density comfortable/compact, on Dashboard + Generator + Settings + Plugins. Confirm WP chrome untouched, reduced-motion respected, responsive ≤1080px (config/preview stack), no console errors. Fix visual regressions inline.

- [ ] **Step 2: Regenerate screenshots**

With a running EasyCommerce store, capture the 11 `assets/screenshot-N.png` against the new UI (Dashboard, Generator+preview, command palette, batch tray, settings, dark mode, etc.). **Requires a live store — if unavailable, defer this step and note it.**

- [ ] **Step 3: Final gates**

Run: `yarn build` → ok. `composer phpcs` → clean. `composer phpstan` → clean. `yarn playwright test` → green.

- [ ] **Step 4: Commit**

```bash
git add assets/screenshot-*.png
git commit -m "docs(assets): refresh screenshots for redesigned UI"
```

---

## Self-Review notes

- **Spec coverage:** tokens (T1–2), ThemeProvider (T3), primitives (T4–6), shell (T7–8), dashboard+history (T9–11), generator+runbar (T12–14), real preview (T15–16), overlays toasts/palette/locale/tweaks/batch (T17–20), settings/plugins (T21–22), assets/wp.org (T23–25), tests/polish (T26–27). All spec phases mapped.
- **Sequencing caveat:** AppShell (T8) wires overlay-toggle state before overlay components exist (T17–20); buttons toggle harmless state until then — explicitly noted, not a placeholder.
- **Back-compat:** `Generator.icon` (lucide) kept alongside new `iconName` until old callers (`ActionPanel`/`ParamsPanel`/`GeneratorSidebar`) are removed in T14; delete those three legacy files in T14 Step 2 if fully unreferenced.
- **Preview no-persist** is asserted by PHP test (T15) — the core correctness guarantee.
- **Screenshots (T27)** gated on a live store; deferrable.
