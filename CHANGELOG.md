# Changelog

All notable changes to the **Contentful WordPress Plugins** collection will be documented in this file.

This project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html) at the repository level.
Individual plugin versions are tracked separately in their respective plugin headers.

## [Unreleased]

---

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

[Unreleased]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.2.2...HEAD
[v1.2.2]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.2.1...v1.2.2
[v1.2.1]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.2.0...v1.2.1
[v1.2.0]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.1.2...v1.2.0
[v1.1.2]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.1.1...v1.1.2
[v1.1.1]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.1.0...v1.1.1
[v1.1.0]: https://github.com/SilverAssist/contentful-wordpress-plugins/compare/v1.0.0...v1.1.0
[v1.0.0]: https://github.com/SilverAssist/contentful-wordpress-plugins/releases/tag/v1.0.0
