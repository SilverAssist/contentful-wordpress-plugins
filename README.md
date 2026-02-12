# Contentful WordPress Plugins

A collection of WordPress plugins designed for Contentful integration and enhanced GraphQL functionality, with unified admin settings via [Silver Assist Settings Hub](https://github.com/SilverAssist/wp-settings-hub).

[![CI](https://github.com/SilverAssist/contentful-wordpress-plugins/actions/workflows/ci.yml/badge.svg)](https://github.com/SilverAssist/contentful-wordpress-plugins/actions/workflows/ci.yml)
[![License](https://img.shields.io/badge/License-PolyForm%20Noncommercial-blue.svg)](https://polyformproject.org/licenses/noncommercial/1.0.0/)
[![WordPress](https://img.shields.io/badge/WordPress-6.5%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://php.net)

## 🚀 Plugins Included

### 1. Community Listings CPT v2.1.0
**Directory:** `community-listings/`

Registers a hierarchical "Community" custom post type for state and city memory care listings with WPGraphQL support.

**Features:**
- Hierarchical CPT with state → city structure
- REST API filtering by listing type and state
- WPGraphQL integration with shortcode rendering
- Meta fields for Contentful integration
- SEO-friendly URL structure
- Silver Assist Settings Hub integration

### 2. Contentful Tables v4.1.0
**Directory:** `contentful-tables/`

Displays Contentful content components (tables, charts, cards, forms) using shortcodes with WPGraphQL support.

**Features:**
- Data tables with filtering capabilities
- Charts and data visualizations
- Responsive card grids
- Contact forms
- Table of contents generation
- WPGraphQL shortcode processing
- Admin settings panel
- Silver Assist Settings Hub integration

### 3. GraphQL Shortcode Support v1.1.0
**Directory:** `graphql-shortcode-support/`

Applies `do_shortcode()` to WPGraphQL content fields, rendering shortcodes as HTML in GraphQL responses.

**Features:**
- Automatic shortcode processing in GraphQL content fields
- Dedicated `renderedContent` field
- Configurable post types and fields
- Toggle for raw vs. processed content
- Admin settings interface
- Silver Assist Settings Hub integration

## 📋 Requirements

- **WordPress:** 6.5+
- **PHP:** 8.2+
- **Dependencies:** [WPGraphQL](https://www.wpgraphql.com/) plugin
- **Optional:** [Silver Assist Settings Hub](https://github.com/SilverAssist/wp-settings-hub) for unified admin menu

## 🛠 Installation

### Option 1: Download Latest Release

1. Go to [Releases](https://github.com/SilverAssist/contentful-wordpress-plugins/releases)
2. Download the latest ZIP files
3. Upload via WordPress Admin → Plugins → Add New → Upload

### Option 2: Clone Repository

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/SilverAssist/contentful-wordpress-plugins.git
cd contentful-wordpress-plugins

# Install production dependencies for all plugins
make install
```

### Option 3: Individual Plugin Installation

Clone or copy individual plugin directories to your WordPress plugins folder and install dependencies.

## ⚙️ Configuration

### Silver Assist Settings Hub (Recommended)

When the [wp-settings-hub](https://github.com/SilverAssist/wp-settings-hub) package is installed, all three plugins register under a unified **Silver Assist** top-level menu in WordPress admin:

```
WordPress Admin
├── Silver Assist 🛡️
│   ├── Dashboard              ← Overview of all plugins
│   ├── Community Listings     ← Plugin status & info
│   ├── Contentful Tables      ← Table settings & shortcodes
│   └── GraphQL Shortcodes     ← Processing settings
```

If the Settings Hub is not installed, each plugin falls back to its own standalone settings page.

### Community Listings CPT
- No additional configuration required
- Custom post type is registered automatically
- Access via WordPress Admin → Communities
- Settings via **Silver Assist → Community Listings** (or **Settings → Community Listings**)

### Contentful Tables
- Settings via **Silver Assist → Contentful Tables** (or **Settings → Contentful Tables**)
- Set up data sources and styling options
- Test shortcodes in posts/pages

### GraphQL Shortcode Support
- Settings via **Silver Assist → GraphQL Shortcodes** (or **Tools → GraphQL Shortcodes**)
- Choose which post types and fields to process
- Toggle automatic processing on/off

## 📝 Usage Examples

### Community Listings
```php
// REST API queries
GET /wp-json/wp/v2/community?listing_type=state
GET /wp-json/wp/v2/community?state_short=TX

// GraphQL Query
{
  communities {
    nodes {
      title
      renderedContent
      communityMeta {
        listingType
        stateShort
      }
    }
  }
}
```

### Contentful Tables Shortcodes
```php
[contentful_table id="pricing-data"]
[contentful_chart id="statistics" type="bar"]
[contentful_cards id="services" filters="medicare"]
[contentful_toc id="guide-sections"]
[contentful_form id="contact-form"]
```

### GraphQL Shortcode Processing
```graphql
{
  posts {
    nodes {
      title
      content           # Raw shortcodes or processed HTML
      renderedContent   # Always processed HTML
    }
  }
}
```

## 🏗 Architecture

All plugins follow **SilverAssist WordPress Plugin Development Standards v2.0.0**:

- ✅ PSR-4 autoloading
- ✅ Interface-driven design
- ✅ Priority-based component loading
- ✅ WordPress Coding Standards (WPCS)
- ✅ PHPStan Level 8 analysis
- ✅ Security best practices

## 🧪 Development

### Requirements
- PHP 8.2+
- Composer
- Node.js (for any build processes)

### Setup
```bash
git clone https://github.com/SilverAssist/contentful-wordpress-plugins.git
cd contentful-wordpress-plugins

# Install dev dependencies for all plugins
make install-dev
```

### Quality Assurance
```bash
# Run all quality checks (PHPCS + PHPStan)
make test

# Individual checks
make phpcs        # WordPress Coding Standards
make phpcs-fix    # Auto-fix code style issues
make phpstan      # Static analysis (Level 8)
make lint         # Run PHPCS + PHPStan together
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

Please ensure your code follows our coding standards and includes appropriate tests.

## 📄 License

This project is licensed under the [PolyForm Noncommercial License 1.0.0](https://polyformproject.org/licenses/noncommercial/1.0.0/).

- ✅ **Permitted:** Personal use, educational use, evaluation, non-commercial research
- ❌ **Prohibited:** Commercial use without separate commercial license

For commercial licensing, please contact [Silver Assist](https://silverassist.com).

## 🐛 Support & Issues

- **Issues:** [GitHub Issues](https://github.com/SilverAssist/contentful-wordpress-plugins/issues)
- **Documentation:** See individual plugin README files
- **Support:** [Silver Assist Support](https://silverassist.com/support)

## 🎯 Roadmap

- [x] Silver Assist Settings Hub integration
- [x] CI/CD pipeline with GitHub Actions
- [x] PHPCS + PHPStan compliance (Level 8)
- [x] Automated release packaging
- [ ] Plugin update mechanism via GitHub
- [ ] Automated testing with WordPress core versions
- [ ] Additional Contentful content types
- [ ] Performance optimizations
- [ ] Multilingual support

## 👥 Authors

**Silver Assist**
- Website: [silverassist.com](https://silverassist.com)
- GitHub: [@SilverAssist](https://github.com/SilverAssist)

---

**Made with ❤️ for the WordPress and Contentful communities**
