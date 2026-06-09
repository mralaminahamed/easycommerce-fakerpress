# Admin UI Redesign — Design Spec

**Date:** 2026-06-09
**Branch:** `redesign/admin-ui`
**Status:** Draft — awaiting review

## Goal

Replace the current plain Tailwind gray/blue admin UI with the Linear/Vercel-style
SaaS interface prototyped in the Claude Design handoff
(`project/app/*`). Recreate it pixel-faithfully in the existing stack
(React + TypeScript + Tailwind v4 + Radix), wire net-new interaction features to
real data, ship updated brand assets, and refresh the WordPress.org directory
assets.

Generators and their parameter schemas already match the prototype — **no
generators are missing**. This is a UI/UX + interaction + assets project, not a
data-layer project.

## Locked decisions (from brainstorming)

1. **Styling:** Rebuild in Tailwind v4. Re-express the prototype's design tokens
   (`theme.css`) as `@theme` CSS variables; keep Radix primitives where they add
   value (Select, Switch, Tooltip, Dialog). Build custom FP components against
   the token vars.
2. **Features in scope (all four):** Dashboard + run history · Live preview table
   · Command palette (⌘K) · Batch queue.
3. **Preview data:** Real preview endpoint — REST route returning sample rows
   from the actual generators (server-side faker + real locale/sample-data).
4. **Theming:** Full tweaks panel — light/dark + 5 accent palettes + density,
   persisted to localStorage.

## Constraints

- App mounts in `#easycommerce-fakerpress-root` inside wp-admin. WordPress owns
  the real admin bar + left menu; we do **not** recreate them. Our shell = the
  plugin's own sidebar nav + topbar + content (the prototype's `fp-*`, not its
  `wp-*` mock chrome).
- `#wpcontent` left padding is already stripped so the app owns the content area.
  The app must fill the viewport height (minus WP admin bar) so the sticky run
  bar and full-height sidebar work.
- Dark theme applies **only inside the plugin root** — never restyle WP chrome.
- All user-facing strings via `__()` / `sprintf()` with the
  `easycommerce-fakerpress` text domain.
- Keep `data-testid` hooks compatible with the existing Playwright e2e suite
  (update tests where structure changes; never silently drop coverage).

## Design tokens (Tailwind `@theme`)

Port `theme.css` into `src/styles.css` as `@theme` + `:root`/`[data-theme]`
variables:

- **Type:** Geist + Geist Mono (self-hosted under `assets/fonts/`, not Google
  CDN — wp.org compliance). `--font-sans`, `--font-mono`.
- **Radii:** `--r-xs…--r-full`.
- **Color:** full light + dark token sets (`--bg`, `--surface*`, `--border*`,
  `--text*`), semantic colors (green/amber/red/sky/violet), accent system with
  `color-mix` derivations (`--accent-soft`, `--accent-hover`, `--ring`, …).
- **Accent palettes:** `[data-accent="indigo|blue|violet|emerald|amber"]`.
- **Shadows:** `--shadow-sm…--shadow-pop`.
- **Density:** `[data-density="compact"]` overrides.
- Theme/accent/density set as attributes on the **root container** (not
  `<html>`, to stay scoped inside wp-admin), via a `ThemeProvider`.

Keyframes/animations (`fp-rise`, `fp-row-in`, `fp-pop`, `fp-spin`, etc.) ported,
respecting `prefers-reduced-motion`.

## Architecture

### Component layers

```
src/admin/
  theme/
    ThemeProvider.tsx        // applies data-theme/-accent/-density to root, persists
    useTheme.ts
  components/
    shell/
      AppShell.tsx           // sidebar + topbar + scroll/content frame, full-height
      Sidebar.tsx            // collapsible, brand, ⌘K button, grouped nav + counts, footer
      Topbar.tsx             // breadcrumb, locale pill, theme toggle, tweaks, batch chip
    overlays/
      CommandPalette.tsx     // ⌘K quick-jump (generators + pages)
      TweaksPanel.tsx        // accent / appearance / density
      LocalePicker.tsx
      BatchTray.tsx          // queue + sequential run
      Toasts.tsx + ToastProvider
    Pages/
      DashboardPage.tsx      // was HomePage → overview: stats + recent + grid
      GeneratorPage.tsx      // 2-col config + live preview + sticky run bar
      SettingsPage.tsx       // redesigned cards incl. tweaks-relevant defaults
      PluginsPage.tsx        // redesigned plugin cards
    dashboard/
      StatCard.tsx, Sparkline.tsx, RecentActivity.tsx, GeneratorGrid.tsx
    generator/
      ConfigColumn.tsx, FieldSection.tsx
      PreviewTable.tsx, RunBar.tsx
      fields/  Toggle, Chips, Stepper, NumberField, TextField, RangeField, Select
    ui/                      // Radix-wrapped primitives, restyled to tokens
```

### State

- **App-level providers:** `ThemeProvider`, `ToastProvider`, `BatchProvider`
  (queue), `StatsProvider` (run history/counts from localStorage).
- Run history + per-generator counts persist via the existing
  `lib/storage.ts` (extend, don't replace). Dashboard reads from it.
- Routing stays `createHashRouter`; add `/` = Dashboard (current HomePage role),
  keep `/generator/:type`, `/settings`, `/plugins`.
- ⌘K + Escape handled by a global key listener in `AppShell`.

### Field schema → control mapping

The prototype's flat `fields[]` (`toggle|chips|range|select|number|text`) maps to
our existing JSON-Schema `parameterConfig`. Build a **schema→fields adapter**
(`lib/fieldsFromSchema.ts`) that walks `parameterConfig` (incl. nested
`properties`, `dependsOn`) and emits typed field descriptors + section grouping,
so the rich controls render from the schema we already ship — single source of
truth, no duplicated field tables.

### Live preview endpoint (real data)

Add a preview REST route and a generator preview path:

- **Route:** `POST /easycommerce-fakerpress/v1/{resource}/preview` in the base
  `Controller` (alongside `generate`). Accepts the same params + `count`
  (clamped, e.g. ≤ 25). Returns `{ columns: [...], rows: [...] }` — plain arrays,
  **no DB writes, no model persistence.**
- **Generator:** add `public function preview( int $count ): array` to
  `Abstracts/Generator`. Default implementation builds rows from the faker +
  loaded sample data via a new `protected function build_preview_row(): array`.
  Each concrete generator overrides `build_preview_row()` to return its column
  cells (id/name/price/status/…) **without** calling EC model save methods.
- **Phasing:** implement `build_preview_row()` for the 4 Core generators first
  (Products, Customers, Orders, Coupons); the base provides a generic fallback
  (id + a couple faker fields) so every generator previews something real from
  day one. Remaining generators get bespoke rows in a later phase.
- Frontend `PreviewTable` debounces param changes, calls `/preview`, renders
  with cell-kind styling (mono/money/num/status/badge/stars). "Shuffle" re-rolls
  the seed.

### Batch queue (client-side)

`BatchProvider` holds `[{ route, count }]`. BatchTray runs them by issuing the
existing `/generate` calls sequentially, updating progress + toasts + stats. No
backend change.

## Assets

From `project/assets/`:

- `fakerpress-mark.svg`, `fakerpress-logo.svg` → `assets/brand/` → used in
  Sidebar brand + Dashboard header.
- `wp-admin-menu-icon.svg` → register as the admin menu icon (monochrome 20×20,
  recolors with WP menu states) in `add_menu_page`.
- `icon.svg`, `icon-128x128.png`, `icon-256x256.png` → replace existing
  `assets/icon-*.png` + add SVG (wp.org directory icon).
- `banner-1544x500.png`, `banner-772x250.png` → replace existing
  `assets/banner-*.png`.
- Self-host Geist/Geist Mono woff2 under `assets/fonts/` + `@font-face`.
- Regenerate `assets/screenshot-*.png` after the redesign ships (final phase),
  and update `readme.txt` screenshot captions to match the new UI.

## Build sequence (phases)

1. **Tokens + ThemeProvider** — port design tokens to `@theme`, self-host fonts,
   `ThemeProvider` (theme/accent/density on root + localStorage). Verify a
   throwaway swatch renders.
2. **UI primitives** — restyle/rebuild field controls + ui/ to tokens (Button,
   Toggle, Chips, Stepper, Number/Text/Range, Select, Badge, StatusPill,
   SectionLabel, Card, Icon set).
3. **App shell** — `AppShell` + `Sidebar` (collapsible, grouped, counts) +
   `Topbar`. Wire routing into the shell. Replace `RootLayout`.
4. **Dashboard** — StatCard + Sparkline + RecentActivity + GeneratorGrid; extend
   `lib/storage` for run history; replace HomePage.
5. **Generator page** — schema→fields adapter, ConfigColumn + FieldSection,
   RunBar (count stepper / seed / metadata / add-to-batch / generate). Wire real
   `/generate`.
6. **Live preview** — PHP: base `preview()` + route + Core `build_preview_row()`;
   FE: `PreviewTable` debounced fetch. Generic fallback for non-Core.
7. **Overlays** — ToastProvider + Toasts, CommandPalette (⌘K), LocalePicker,
   TweaksPanel, BatchProvider + BatchTray.
8. **Settings + Plugins** — redesigned cards; settings defaults feed
   generator/run-bar; danger zone clears stats/history.
9. **Assets + wp.org** — brand SVGs, admin menu icon, wp.org icon/banners,
   fonts; bump version; refresh screenshots + `readme.txt`.
10. **Tests + polish** — update Playwright testids/flows, dark-mode + density +
    accent pass, reduced-motion, responsive breakpoints, `yarn build` +
    `composer phpcs`/`phpstan` green.

## Out of scope

- New generators or changes to generator data logic (already complete).
- MCP server changes.
- Changes to the real generation/persistence behavior beyond adding read-only
  preview.

## Testing

- Playwright e2e updated per phase (shell nav, generator flow, preview render,
  command palette, batch run, theme toggle).
- PHP: a `preview` route test per Core generator asserting no rows persist
  (count before == count after) and shape `{columns, rows}`.
- Manual: dark/light × 5 accents × density on Dashboard + Generator; WP chrome
  untouched; reduced-motion.
