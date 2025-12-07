# Contributing to WordPress Address Autocomplete

Thank you for considering contributing to WordPress Address Autocomplete! This document provides guidelines and instructions for contributing.

## Code of Conduct

-   Be respectful and inclusive
-   Provide constructive feedback
-   Focus on what is best for the community
-   Show empathy towards other community members

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates. When creating a bug report, include:

-   Clear and descriptive title
-   Exact steps to reproduce the problem
-   Expected behavior vs actual behavior
-   WordPress version, PHP version, and plugin versions
-   Screenshots if applicable
-   Error messages or logs

### Suggesting Enhancements

Enhancement suggestions are welcome! Please include:

-   Clear and descriptive title
-   Detailed description of the suggested enhancement
-   Explain why this enhancement would be useful
-   List any alternative solutions you've considered

### Pull Requests

1. Fork the repository
2. Create a new branch for your feature (`git checkout -b feature/amazing-feature`)
3. Make your changes following our coding standards
4. Write or update tests as needed
5. Update documentation as needed
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

## Coding Standards

### PHP

-   Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
-   Use meaningful variable and function names
-   Add DocBlocks to all functions and classes
-   Keep functions focused and single-purpose
-   Sanitize and validate all inputs
-   Escape all outputs

### JavaScript

-   Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
-   Use meaningful variable and function names
-   Add JSDoc comments for functions
-   Use modern ES6+ syntax where appropriate
-   Keep functions focused and single-purpose

### CSS

-   Follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
-   Use meaningful class names
-   Keep specificity low
-   Add comments for complex sections
-   Ensure responsive design

## Translation

We welcome translations! To contribute translations:

1. Copy `languages/wp-address-autocomplete.pot`
2. Translate using Poedit or similar tool
3. Save as `wp-address-autocomplete-{locale}.po`
4. Generate `.mo` file
5. Submit a pull request with both files

## Testing

Before submitting a pull request:

1. Run PHPUnit tests: `composer test`
2. Check coding standards: `composer phpcs`
3. Test with multiple WordPress versions
4. Test with supported form plugins
5. Test with both providers (OpenStreetMap and Google Maps)

## Documentation

-   Update README.md if you change functionality
-   Update inline documentation (DocBlocks)
-   Add comments for complex logic
-   Update CHANGELOG.md

## Questions?

Feel free to open an issue for questions or reach out to [NDNCI](https://www.ndnci.com).

## License

By contributing, you agree that your contributions will be licensed under GPL v2 or later.
