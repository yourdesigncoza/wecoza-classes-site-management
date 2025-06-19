# Security Policy

## Supported Versions

We actively support the following versions of the WeCoza Classes Site Management plugin with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take the security of the WeCoza Classes Site Management plugin seriously. If you discover a security vulnerability, please follow these guidelines:

### How to Report

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please report security vulnerabilities by emailing us directly at:
**laudes.michael@gmail.com**

Include the following information in your report:
- A clear description of the vulnerability
- Steps to reproduce the issue
- Potential impact of the vulnerability
- Any suggested fixes or mitigations
- Your contact information for follow-up

### What to Expect

1. **Acknowledgment**: We will acknowledge receipt of your vulnerability report within 48 hours.

2. **Initial Assessment**: We will provide an initial assessment of the vulnerability within 5 business days.

3. **Investigation**: We will investigate the vulnerability and determine its severity and impact.

4. **Resolution**: We will work to resolve the vulnerability as quickly as possible:
   - Critical vulnerabilities: Within 7 days
   - High severity vulnerabilities: Within 14 days
   - Medium/Low severity vulnerabilities: Within 30 days

5. **Disclosure**: We will coordinate with you on the disclosure timeline and process.

### Security Best Practices

When using the WeCoza Classes Site Management plugin, we recommend:

#### For Site Administrators
- Keep WordPress core, themes, and plugins updated
- Use strong, unique passwords
- Implement two-factor authentication
- Regular security audits and monitoring
- Backup your site regularly
- Use HTTPS/SSL certificates
- Limit user permissions appropriately

#### For Developers
- Follow WordPress security best practices
- Sanitize all input data
- Escape all output data
- Use nonces for form submissions
- Implement proper capability checks
- Validate user permissions
- Use prepared statements for database queries
- Avoid direct file access

### Common Security Considerations

#### Data Protection
- All sensitive data is properly sanitized and validated
- User inputs are escaped before output
- Database queries use prepared statements
- File uploads are restricted and validated

#### Access Control
- Proper capability checks are implemented
- User permissions are validated
- Administrative functions require appropriate privileges
- Nonces are used for form submissions

#### WordPress Security Features
- Follows WordPress coding standards
- Uses WordPress security functions
- Implements proper hooks and filters
- Respects WordPress user roles and capabilities

### Vulnerability Disclosure Policy

We believe in responsible disclosure and will:

1. Work with security researchers to understand and resolve vulnerabilities
2. Provide credit to researchers who report vulnerabilities (unless they prefer to remain anonymous)
3. Coordinate disclosure timelines to ensure users have time to update
4. Publish security advisories for significant vulnerabilities

### Security Updates

Security updates will be:
- Released as soon as possible after a vulnerability is confirmed
- Clearly marked as security releases
- Accompanied by detailed security advisories
- Backward compatible when possible

### Contact Information

For security-related questions or concerns:
- **Email**: laudes.michael@gmail.com
- **Subject Line**: [SECURITY] WeCoza Classes Site Management

For general support or non-security issues, please use the GitHub issues tracker.

### Acknowledgments

We appreciate the security research community and thank all researchers who responsibly disclose vulnerabilities to help keep our users safe.

---

**Note**: This security policy applies specifically to the WeCoza Classes Site Management plugin. For WordPress core security issues, please refer to the [WordPress Security Team](https://make.wordpress.org/core/handbook/testing/reporting-security-vulnerabilities/).
