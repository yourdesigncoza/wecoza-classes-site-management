# Contributing to WeCoza Classes Site Management

Thank you for your interest in contributing to the WeCoza Classes Site Management plugin! This document provides guidelines and information for contributors.

## Table of Contents
- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)
- [Reporting Issues](#reporting-issues)

## Code of Conduct

By participating in this project, you agree to abide by our code of conduct:
- Be respectful and inclusive
- Focus on constructive feedback
- Help create a welcoming environment for all contributors

## Getting Started

### Prerequisites
- WordPress 5.0 or higher
- PHP 7.4 or higher
- Git
- Code editor (VS Code recommended)

### Development Setup
1. Fork the repository
2. Clone your fork locally:
   ```bash
   git clone https://github.com/yourdesigncoza/wecoza-classes-site-management.git
   cd wecoza-classes-site-management
   ```
3. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

### Project Structure
```
wecoza-classes-site-management/
├── app/
│   ├── Controllers/     # MVC Controllers
│   ├── Models/         # Data models
│   ├── Views/          # View templates
│   ├── Helpers/        # Helper functions
│   └── Services/       # Business logic services
├── assets/
│   ├── css/           # Stylesheets
│   └── js/            # JavaScript files
├── config/            # Configuration files
├── includes/          # Core plugin files
└── languages/         # Translation files
```

## Development Workflow

### Branching Strategy
- `main` - Production-ready code
- `develop` - Development branch
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Critical fixes

### Commit Messages
Use conventional commit format:
```
type(scope): description

[optional body]

[optional footer]
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

Example:
```
feat(sites): add bulk site creation functionality

Implement bulk site creation with CSV import support.
Includes validation and error handling.

Closes #123
```

## Coding Standards

### PHP Standards
Follow WordPress Coding Standards:
- Use WordPress naming conventions
- Proper indentation (tabs, not spaces)
- Meaningful variable and function names
- Comprehensive documentation

### Security Guidelines
- Always sanitize input data
- Escape output data
- Use nonces for form submissions
- Implement proper capability checks
- Validate and sanitize all user inputs

### Example Code Structure
```php
<?php
/**
 * Site Controller
 *
 * @package WeCozaSiteManagement
 * @subpackage Controllers
 */

class WeCoza_Site_Controller {
    
    /**
     * Create a new site
     *
     * @param array $site_data Site data to create
     * @return int|WP_Error Site ID on success, WP_Error on failure
     */
    public function create_site( $site_data ) {
        // Validate capabilities
        if ( ! current_user_can( 'manage_sites' ) ) {
            return new WP_Error( 'insufficient_permissions', 'You do not have permission to create sites.' );
        }
        
        // Sanitize input
        $site_data = $this->sanitize_site_data( $site_data );
        
        // Validate data
        $validation = $this->validate_site_data( $site_data );
        if ( is_wp_error( $validation ) ) {
            return $validation;
        }
        
        // Create site
        return $this->site_model->create( $site_data );
    }
}
```

## Testing

### Running Tests
```bash
# PHP syntax check
find . -name "*.php" -not -path "./vendor/*" | xargs -I {} php -l {}

# WordPress Coding Standards
phpcs --standard=WordPress .
```

### Writing Tests
- Write unit tests for new functionality
- Include integration tests where appropriate
- Test edge cases and error conditions
- Ensure tests are isolated and repeatable

## Submitting Changes

### Pull Request Process
1. Ensure your code follows the coding standards
2. Update documentation if needed
3. Add or update tests as appropriate
4. Create a pull request with:
   - Clear title and description
   - Reference to related issues
   - Screenshots (if UI changes)
   - Testing instructions

### Review Process
- All pull requests require review
- Address feedback promptly
- Keep pull requests focused and small
- Ensure CI checks pass

## Reporting Issues

### Bug Reports
Use the bug report template and include:
- Clear description of the issue
- Steps to reproduce
- Expected vs actual behavior
- Environment information
- Error logs (if applicable)

### Feature Requests
Use the feature request template and include:
- Clear description of the feature
- Use cases and benefits
- Proposed implementation (if applicable)

## Development Guidelines

### MVC Architecture
This plugin follows MVC (Model-View-Controller) architecture:
- **Models**: Handle data and database operations
- **Views**: Handle presentation and templates
- **Controllers**: Handle business logic and user interactions

### WordPress Integration
- Use WordPress hooks and filters appropriately
- Follow WordPress plugin development best practices
- Ensure compatibility with WordPress multisite
- Support internationalization (i18n)

### Performance Considerations
- Minimize database queries
- Use WordPress caching mechanisms
- Optimize asset loading
- Consider scalability

## Questions?

If you have questions about contributing, please:
1. Check existing documentation
2. Search existing issues
3. Create a new issue with the "question" label

Thank you for contributing to WeCoza Classes Site Management!
