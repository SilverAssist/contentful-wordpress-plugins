# Changelog

All notable changes to the **Contentful WordPress Plugins** collection will be documented in this file.

This project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html) at the repository level.
Individual plugin versions are tracked separately in their respective plugin headers.

## [Unreleased]

### Changed
- Bumped `actions/checkout` (v4→v7), `actions/cache` (v4→v6), and `actions/upload-artifact` (v4→v7) in both workflows to their Node 24 releases, clearing the "Node.js 20 is deprecated" warnings GitHub Actions now emits on every job step
- Adopted `silverassist/wp-plugin-kernel` in all three sub-plugins, replacing each one's own duplicated `LoadableInterface`/`Plugin` bootstrap with the shared `AbstractPlugin` — components now implement `get_priority()`/`should_load()`/`init()` (the kernel's contract) instead of the old local `priority()`/`register()`. In `contentful-tables`, `TableDataLoader` became a proper singleton (`ShortcodeRegistrar`/`SettingsPage` now fetch it via `TableDataLoader::instance()` instead of constructor injection), since kernel-managed components are instantiated with no arguments.

### Added
- **community-listings:** Admin meta box ("Provider Listings") on the `community` post type editor, city-level posts only — lets editors view/update the `provider_listings` JSON without CLI access. JSON-safe save via `$wpdb->update()`/`insert()` (avoids `update_post_meta()`'s `wp_unslash()` corrupting `\"` escapes), with byte/provider counts, client-side validation, and post-meta cache invalidation after the direct write (#2)

### Changed
- Adopted the `SilverAssistWP` PHPCS ruleset and composed shared `silverassist/coding-standards` + `silverassist/wp-coding-standards` PHPStan base configs across all three sub-plugins, replacing hand-rolled rulesets and duplicated static-analysis baselines (#3)

### Fixed
- **community-listings:** Resolved all 3 pre-existing PHPStan errors — dead-code truthy check in `SettingsPage::render_settings_page()`, a `register_graphql_field()` call missing its own `function_exists()` guard, and an unreachable array-offset fallback in the GraphQL type-mapping refactored to a `match` expression
- **Makefile:** `phpcs`, `phpstan`, `install`, `install-dev`, and `build` targets no longer silently ignore failures in any sub-plugin but the last one in `$(PLUGINS)` — each `for` loop now propagates a non-zero exit status, so CI actually fails when any sub-plugin has a real violation (`phpcs`/`phpstan` previously didn't: the 3 PHPStan errors above went undetected by CI since this repo's first PHPStan adoption)
- **Makefile:** `phpcs` target now calls `vendor/bin/phpcs --warning-severity=0` directly instead of `composer run phpcs`, matching exactly what CI's own PHPCS step runs — previously `make phpcs` treated pre-existing warnings (discouraged `json_encode()`/`file_get_contents()` calls, a missing nonce-verification annotation) as failures that CI itself was configured to ignore, so the two disagreed on what counted as passing

---

## [v1.2.5] — 2026-03-02

### Plugin Versions
- **Community Listings CPT** v2.2.4
- **Contentful Tables** v4.2.0
- **GraphQL Shortcode Support** v1.2.1

### Added
- **community-listings:** Add `provider_listings` meta field to CommunityMeta GraphQL type (exposed as `providerListings`)

## [v1.2.4] — 2026-02-25

### Plugin Versions
- **Community Listings CPT** v2.2.3
- **Contentful Tables** v4.2.0
- **GraphQL Shortcode Support** v1.2.1

### Fixed
- **community-listings:** Fix meta field resolver to use WPGraphQL `databaseId` property first (WPGraphQL `Model\Post` does not expose `ID` directly)
- **community-listings:** Add `show_in_graphql => true` to `register_post_meta()` calls for native WPGraphQL meta support

## [v1.2.3] — 2026-02-25

### Plugin Versions
- **Community Listings CPT** v2.2.2
- **Contentful Tables** v4.2.0
- **GraphQL Shortcode Support** v1.2.1

### Added
- **community-listings:** Register `communityMeta` field on the `Community` WPGraphQL type, exposing all custom meta fields (`listingType`, `stateShort`, `stateLong`, `contentfulId`, `linkText`, `originalSlug`, `originalUrl`, `contentBucket`, `sitemapGroup`, `heroTextContrast`, `noindex`, `nofollow`) via a dedicated `CommunityMeta` GraphQL object type

## [v1.2.2] — 2026-02-25

### Plugin Versions
- **Community Listings CPT** v2.2.1
- **Contentful Tables** v4.2.0
- **GraphQL Shortcode Support** v1.2.1

### Fixed
- **graphql-shortcode-support:** Prevent duplicate `renderedContent` field registration when multiple post type slugs resolve to the same WPGraphQL type name (DUPLICATE_FIELD error)

## [v1.2.1] — 2026-02-24

### Plugin Versions
- **Community Listings CPT** v2.2.1
- **Contentful Tables** v4.2.0
- **GraphQL Shortcode Support** v1.2.0

### Fixed
- **community-listings:** Prevent duplicate `renderedContent` field registration in WPGraphQL when GraphQL Shortcode Support plugin is also active (DUPLICATE_FIELD error)

## [v1.2.0] — 2026-02-24

### Plugin Versions
- **Community Listings CPT** v2.2.0
- **Contentful Tables** v4.2.0
- **GraphQL Shortcode Support** v1.2.0

### Added
- `CHANGELOG.md` for tracking all releases
- Dynamic release notes in GitHub Actions workflow

### Changed
- Updated `SETUP_COMPLETE.md` to reflect current plugin versions
- Release workflow now reads changelog instead of hardcoding release notes

---

## [v1.1.2] — 2026-02-24

### Plugin Versions
- **Community Listings CPT** v2.1.1
- **Contentful Tables** v4.1.1
- **GraphQL Shortcode Support** v1.1.0

### Fixed
- **community-listings:** Register Community CPT in WPGraphQL schema (`show_in_graphql`, `graphql_single_name`, `graphql_plural_name`)

## [v1.1.1] — 2026-02-13

### Plugin Versions
- **Community Listings CPT** v2.1.1
- **Contentful Tables** v4.1.1
- **GraphQL Shortcode Support** v1.1.0

### Fixed
- **contentful-tables:** Bump to v4.1.1
- **contentful-tables:** Register DataLoader and always hide key column

## [v1.1.0] — 2026-02-12

### Plugin Versions
- **Community Listings CPT** v2.1.0
- **Contentful Tables** v4.1.0
- **GraphQL Shortcode Support** v1.1.0

### Added
- Silver Assist Settings Hub integration for all plugins
- Unified admin menu under **Silver Assist** top-level menu

### Changed
- Version bumps across all plugins for Settings Hub support

### Fixed
- PHPCS/PHPStan compliance across all plugins

## [v1.0.0] — 2026-02-12

### Plugin Versions
- **Community Listings CPT** v2.0.0
- **Contentful Tables** v4.0.0
- **GraphQL Shortcode Support** v1.0.0

### Added
- Initial plugin collection release
- Community Listings CPT with hierarchical state → city structure
- Contentful Tables with table, chart, card, form, and TOC shortcodes
- GraphQL Shortcode Support for WPGraphQL content fields
- CI/CD pipeline with GitHub Actions
- PHPCS + PHPStan quality assurance (Level 8)
- Automated release packaging
- Comprehensive documentation and contribution guidelines

---

[Unreleased]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.2.5...HEAD
[v1.2.5]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.2.4...v1.2.5
[v1.2.4]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.2.3...v1.2.4
[v1.2.3]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.2.2...v1.2.3
[v1.2.2]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.2.1...v1.2.2
[v1.2.1]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.2.0...v1.2.1
[v1.2.0]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.1.2...v1.2.0
[v1.1.2]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.1.1...v1.1.2
[v1.1.1]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.1.0...v1.1.1
[v1.1.0]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.0.0...v1.1.0
[v1.0.0]: https://github.com/SilverAssist/contentful-wordpress-plugins/releases/tag/v1.0.0
