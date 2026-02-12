# Contributing to Contentful WordPress Plugins

Thank you for your interest in contributing to our WordPress plugins! We welcome contributions from the community.

## 🤝 Code of Conduct

By participating in this project, you are expected to uphold our Code of Conduct:

- Use welcoming and inclusive language
- Be respectful of differing viewpoints and experiences
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

## 🐛 Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- **Clear description** of the issue
- **Steps to reproduce** the behavior
- **Expected behavior**
- **Actual behavior**
- **Environment details:**
  - WordPress version
  - PHP version
  - Plugin version
  - Active theme and other plugins

## 💡 Suggesting Enhancements

Enhancement suggestions are welcome! Please include:

- **Clear description** of the enhancement
- **Use case** - why would this be useful?
- **Implementation ideas** (if any)
- **Examples** from other plugins or platforms

## 🛠 Development Setup

1. **Fork the repository**
2. **Clone your fork:**
   ```bash
   git clone https://github.com/YOUR-USERNAME/contentful-wordpress-plugins.git
   cd contentful-wordpress-plugins
   ```

3. **Install development dependencies:**
   ```bash
   make dev-setup
   make install-dev
   ```

4. **Create a feature branch:**
   ```bash
   git checkout -b feature/your-feature-name
   ```

## 📝 Coding Standards

### PHP Standards
- Follow **WordPress Coding Standards** (WPCS)
- Use **PSR-4** autoloading
- Maintain **PHPStan Level 8** compliance
- Add **type declarations** for all methods
- Write **DocBlocks** for all classes and methods

### Code Quality Tools
Run these commands before submitting:

```bash
# Check coding standards
make phpcs

# Fix auto-fixable issues
make phpcs-fix

# Run static analysis
make phpstan

# Run all tests
make test
```

### WordPress Specific Guidelines
- Use WordPress functions when available (e.g., `wp_remote_get()` instead of `file_get_contents()`)
- Sanitize and validate all input
- Escape all output
- Use WordPress nonces for forms
- Follow WordPress naming conventions
- Prefix all global functions and variables

## 🏗 Architecture Guidelines

### Plugin Structure
Each plugin follows the SilverAssist standards:

```
plugin-name/
├── plugin-name.php           # Main plugin file
├── composer.json            # Dependencies and autoload
├── includes/
│   ├── Core/
│   │   ├── Plugin.php       # Main plugin class
│   │   ├── Activator.php    # Activation/deactivation
│   │   └── Interfaces/      # Contracts
│   ├── Service/             # Business logic
│   ├── Admin/              # Admin interfaces
│   ├── Utils/              # Helper classes
│   └── View/               # Rendering classes
└── README.md               # Plugin documentation
```

### Loading Priority
Components load with specific priorities:
- **10**: Core services (data loaders, registrars)
- **20**: Processing services (GraphQL, shortcodes)
- **30**: UI components (admin pages)

### Interface Implementation
All loadable components must implement `LoadableInterface`:

```php
interface LoadableInterface {
    public function priority(): int;
    public function register(): void;
}
```

## 🧪 Testing

### Manual Testing
1. **Set up a WordPress development environment**
2. **Install the plugins** in development mode
3. **Test your changes** with various WordPress themes
4. **Verify compatibility** with WPGraphQL
5. **Test with sample Contentful data**

### Automated Testing
- **PHPCS**: Code standards compliance
- **PHPStan**: Static analysis
- **Composer validation**: Dependency management

Run all tests:
```bash
make test
```

## 📤 Pull Request Process

1. **Update documentation** if needed
2. **Run quality assurance** tools
3. **Commit with descriptive messages**
4. **Push to your feature branch**
5. **Create a Pull Request** with:
   - Clear title and description
   - Reference to related issues
   - Screenshots (if UI changes)
   - Testing instructions

### Pull Request Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] PHPCS passed
- [ ] PHPStan passed
- [ ] Manual testing completed
- [ ] Works with WordPress 6.5+
- [ ] Compatible with WPGraphQL

## Screenshots (if applicable)
```

## 🔄 Review Process

1. **Automated checks** must pass
2. **Code review** by maintainers
3. **Testing** in development environment
4. **Approval and merge**

## 📄 License

By contributing, you agree that your contributions will be licensed under the PolyForm Noncommercial License 1.0.0.

## 💬 Questions?

- **Issues**: [GitHub Issues](https://github.com/SilverAssist/contentful-wordpress-plugins/issues)
- **Discussions**: [GitHub Discussions](https://github.com/SilverAssist/contentful-wordpress-plugins/discussions)
- **Email**: [support@silverassist.com](mailto:support@silverassist.com)

Thank you for contributing! 🎉
