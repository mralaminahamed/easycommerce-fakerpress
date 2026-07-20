# Changelog

All notable changes to EasyCommerce FakerPress are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/2.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Release dates are taken from the release tags in this repository. The [`readme.txt`](readme.txt) changelog carries only the most recent releases, as WordPress.org recommends; this file is the complete history.

## [Unreleased]

## [2.2.0] - 2026-06-11

### Added

- Dashboard with stat cards and sparklines, a recent-activity feed, and a generator grid grouped by category, all driven by real run history.
- Live preview. A read-only REST preview route returns real faker rows without persisting anything; the preview table refreshes as settings change and re-rolls on Shuffle.
- Command palette, opened with Cmd/Ctrl+K, for jumping to any generator or page.
- Batch queue. Multiple generators can be queued and run sequentially from the batch tray, with progress and toasts.
- Tweaks panel with live theme, accent, and density controls, persisted to the browser.

### Changed

- Complete admin UI redesign on a new design-token system: self-hosted Geist fonts, light and dark themes, five accent palettes, and comfortable/compact density. All styles are scoped to the plugin so WordPress chrome is never restyled.
- Generator page rebuilt as a two-column config and preview layout with a sticky run bar carrying the count stepper, seed, metadata toggle, add-to-batch, and generate controls.
- Settings and Our Plugins pages rebuilt on the new card system.
- Refreshed brand assets: new logo, recolorable admin menu icon, and updated WordPress.org icon and banners.

## [2.1.0] - 2026-04-26

### Added

- Three generators: Attributes, Refunds, and Logs, bringing the total to 14.
- Run history. A per-generator run log in localStorage with a configurable FIFO limit, recent runs in the sidebar, and all-time stats on the dashboard.
- Settings page covering default count, locale, seed, and metadata toggle, plus the run-history limit, sample data sync, an About card, and Reset Settings.
- Sample data sync. Locale-specific reference data can be downloaded or force re-synced from the companion GitHub repository via REST endpoints.
- Our Plugins page, listing the author's other WordPress.org plugins with live data.
- Global sticky navigation for Generators, Settings, and Our Plugins.
- Playwright end-to-end suite: 131 tests covering the home page, generator page layout, ActionPanel interactions, all six field types, and all 14 generators.

### Changed

- Admin UI redesigned in a modern SaaS style with clean white, blue, and indigo accents.
- Generator page reworked with a sticky top bar, a collapsible sidebar carrying category navigation and per-generator run history, and a two-panel params and action layout.
- Component architecture reworked: the 757-line `GeneratorBase` monolith was replaced with focused `ParamsPanel`, `ActionPanel`, and `GeneratorSidebar` components, and parameter configuration was centralised in `generators.ts`.

### Fixed

- Category matching now works in all locales.
- Only a single `ActionPanel` instance is rendered in the DOM.
- Focus styles are scoped to the plugin.
- `RangeField` error colour corrected.
- Nested route active state resolves correctly.

## [2.0.4] - 2026-02-26

### Changed

- Shared TypeScript type definitions extracted into their own module; generators and components now consume a shared `GeneratorResult` type.
- Webpack configuration updated, with a `TerserPlugin` configuration added for WordPress compatibility.
- Build output renamed from `index` to `app`, and the asset file path updated to match the entry point.
- Dependencies updated, including PHPStan 2.1.40.

### Fixed

- Corrected the import path for the shared types module.

### Removed

- Leftover `console.log` calls in components.
- `package-lock.json`, since Yarn provides the lockfile.

## [2.0.3] - 2026-01-14

### Added

- Product Review generator with weighted rating distribution and verified-purchase support.
- WordPress comments integration for review storage.

### Changed

- Controller pattern and API schema made consistent across generators.

### Fixed

- Order generator data structure now matches the EasyCommerce `Order` model.
- Order notes are created through the `Order_Notes` model.

## [2.0.2] - 2026-01-14

### Added

- "Get Started" plugin action link on the Plugins screen.

### Changed

- Upgraded to Tailwind CSS v4.
- Build system and dependency compatibility updates.

### Fixed

- Visual inconsistencies in success messages and navigation.

## [2.0.1] - 2025-11-13

### Fixed

- Minor bug fixes and code quality improvements.

## [2.0.0] - 2025-11-11

### Changed

- **Breaking:** Parameter schemas were realigned across all 10 generators. Custom REST API integrations and hooks that pass generator parameters must be reviewed before upgrading.
- Full TypeScript migration with proper interfaces and validation.

### Fixed

- Array versus string mismatches and naming inconsistencies across all REST controllers.

## [1.0.4] - 2025-11-10

### Added

- Sample dataset for locales.

### Fixed

- Deployment issues in the release workflow.

## [1.0.3] - 2025-10-29

### Added

- Hook system exposing 15+ filters and actions for data customisation.
- REST response filtering for API extensibility.

### Changed

- Plugin descriptions expanded to document the hook system.

### Fixed

- Release name format and plugin name in the GitHub release workflow.
- Indentation issue in the GitHub workflow.

## [1.0.2] - 2025-10-26

### Changed

- Performance improvements for memory usage and processing speed.
- Compatibility updates for the latest EasyCommerce features.

### Fixed

- Validation and error handling across all generators.

## [1.0.1] - 2025-10-26

### Changed

- `readme.txt` simplified, with verbose sections removed and formatting converted to standard markdown.

## [1.0.0] - 2025-10-26

### Added

- Real-time dependency checks via a new REST controller.

### Changed

- PHPStan level 8 compliance.
- Build system and documentation improvements.

## 0.9.0 - 2025-09-15

No release tag exists for this version, so it has no comparison link.

### Added

- Initial release with 10 core generators.
- WordPress admin colour integration.
- React 18 interface with real-time feedback.
- PSR-4 architecture with native EasyCommerce model integration.

[Unreleased]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v2.2.0...HEAD
[2.2.0]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v2.0.4...v2.1.0
[2.0.4]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v2.0.3...v2.0.4
[2.0.3]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v2.0.2...v2.0.3
[2.0.2]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v2.0.1...v2.0.2
[2.0.1]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v1.0.4...v2.0.0
[1.0.4]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/mralaminahamed/easycommerce-fakerpress/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/mralaminahamed/easycommerce-fakerpress/releases/tag/v1.0.0
