# Security Headers Configuration Guide

**Status:** ✅ Implemented  
**Date:** January 25, 2026

---

## Overview

Security headers have been implemented to protect your application from various web-based attacks. These headers are configured in **two ways** for maximum compatibility:

1. **Apache `.htaccess`** (primary) - `/.htaccess`
2. **PHP Configuration** (fallback) - `src/config/config.php`

---

## Implemented Headers

### 1. **X-Frame-Options: SAMEORIGIN**
**Purpose:** Prevents clickjacking attacks  
**What it does:** Restricts embedding the site in iframes to same-origin only

```
X-Frame-Options: SAMEORIGIN
```

**Attack Prevented:**
```html
<!-- Attacker's site -->
<iframe src="https://yoursite.com/admin"></iframe>
```

---

### 2. **X-Content-Type-Options: nosniff**
**Purpose:** Prevents MIME type sniffing attacks  
**What it does:** Forces browser to use Content-Type header instead of guessing

```
X-Content-Type-Options: nosniff
```

**Attack Prevented:** Malicious files disguised with wrong extensions

---

### 3. **X-XSS-Protection: 1; mode=block**
**Purpose:** XSS protection for legacy browsers  
**What it does:** Enables browser's built-in XSS filter and blocks page if attack detected

```
X-XSS-Protection: 1; mode=block
```

**Modern Note:** Modern browsers use Content-Security-Policy instead

---

### 4. **Referrer-Policy: strict-origin-when-cross-origin**
**Purpose:** Controls referrer information sharing  
**What it does:** Only shares referrer for same-origin requests

```
Referrer-Policy: strict-origin-when-cross-origin
```

**Benefit:** Prevents leaking sensitive URLs to external sites

---

### 5. **Content-Security-Policy (CSP)**
**Purpose:** Comprehensive protection against XSS and injection attacks
**Policy:**
```
default-src 'self'
script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com
img-src 'self' https: data:
font-src 'self' https://fonts.gstatic.com data:
connect-src 'self' https://www.google.com
frame-src 'self' https://www.google.com
frame-ancestors 'self'
```

**Restrictions:**
- Scripts only from own domain + Google reCAPTCHA (allows inline for compatibility)
- Stylesheets from own domain + Google Fonts
- Images from own domain, HTTPS, or data URIs
- Fonts from own domain, Google Fonts, or data URIs
- API calls to own domain + Google reCAPTCHA
- Iframe embedding only from same domain + Google reCAPTCHA

---

### 6. **Permissions-Policy**
**Purpose:** Disable browser features that aren't used  
**Policy:**
```
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

**Disabled Features:**
- Geolocation API
- Microphone access
- Camera access

---

## Implementation Details

### Apache (.htaccess)

**Location:** `/.htaccess`

The root `.htaccess` file includes:
```apache
# Prevent clickjacking
Header set X-Frame-Options "SAMEORIGIN"

# Prevent MIME type sniffing
Header set X-Content-Type-Options "nosniff"

# XSS Protection
Header set X-XSS-Protection "1; mode=block"

# Referrer Policy
Header set Referrer-Policy "strict-origin-when-cross-origin"

# Content Security Policy
Header set Content-Security-Policy "..."
```

**Requirements:**
- Apache 2.2+ with `mod_headers` enabled
- `.htaccess` must be processed by Apache

---

### PHP Fallback

**Location:** `src/config/config.php`

If `.htaccess` is not available, PHP sends headers via:

```php
$config['security_headers'] = TRUE;

if ($config['security_headers']) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    // ... other headers
}
```

**To Disable:** Set `$config['security_headers'] = FALSE;`

---

## Verification

### Check Headers in Chrome DevTools

1. Open Developer Tools (F12)
2. Go to Network tab
3. Refresh page
4. Click on any request (usually the main document)
5. Look for "Response Headers" section
6. Verify security headers are present

### Command Line Verification

```bash
# Check security headers
curl -i https://your-domain.com | grep -i "X-\|Content-Security\|Referrer"

# Example output:
# X-Frame-Options: SAMEORIGIN
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block
# Referrer-Policy: strict-origin-when-cross-origin
# Content-Security-Policy: ...
```

### Online Tools

Use security header checkers:
- https://securityheaders.com
- https://observatory.mozilla.org
- https://csp-evaluator.withgoogle.com

---

## Adjusting CSP for Your Needs

If you need to load resources from external sources, update the CSP policy.

**Common Adjustments:**

### Allow External CDN Scripts
```php
script-src 'self' 'unsafe-inline' https://cdn.example.com;
```

### Allow Google Fonts
```php
font-src 'self' data: https://fonts.googleapis.com;
```

### Allow Bootstrap CDN
```php
style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;
```

**Example Updated Policy in config.php:**
```php
$csp = "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
    "img-src 'self' https: data:; " .
    "font-src 'self' data: https://fonts.googleapis.com; " .
    "connect-src 'self'; " .
    "frame-ancestors 'self'";
header("Content-Security-Policy: " . $csp);
```

---

## Production Checklist

- [ ] Apache has `mod_headers` enabled: `a2enmod headers`
- [ ] `.htaccess` file is processed: Check Apache config
- [ ] Or PHP header configuration is enabled: `$config['security_headers'] = TRUE`
- [ ] Verify headers via curl or browser DevTools
- [ ] Test with https://securityheaders.com
- [ ] Adjust CSP if external resources needed
- [ ] Monitor browser console for CSP violations

---

## Troubleshooting

### Headers Not Appearing

**Check if mod_headers is enabled:**
```bash
# On Linux/macOS
a2enmod headers

# Restart Apache
sudo systemctl restart apache2
```

**Check Apache configuration:**
```bash
# Verify .htaccess is processed
grep "AllowOverride" /etc/apache2/apache2.conf
# Should show: AllowOverride All or AllowOverride Headers
```

### CSP Blocking Resources

**Check browser console:** Look for CSP violation errors like:
```
Refused to load the script 'https://external.com/script.js' 
because it violates the following Content Security Policy directive: "script-src 'self' 'unsafe-inline'"
```

**Solution:** Add the domain to the appropriate CSP directive

### iframe Not Working

**Error:** `Refused to frame 'https://external.com' because an ancestor violates the following Content Security Policy directive: "frame-ancestors 'self'"`

**Solution:** Update `frame-ancestors` directive:
```
frame-ancestors 'self' https://trusted-domain.com
```

---

## Security Headers Score

| Header | Importance | Status |
|--------|-----------|--------|
| X-Frame-Options | High | ✅ Configured |
| X-Content-Type-Options | High | ✅ Configured |
| Content-Security-Policy | Critical | ✅ Configured |
| X-XSS-Protection | Medium | ✅ Configured |
| Referrer-Policy | Medium | ✅ Configured |
| Permissions-Policy | Low | ✅ Configured |

---

## Impact on Security

### Attack Vectors Mitigated

✅ **Clickjacking** - Blocked by X-Frame-Options  
✅ **MIME Type Sniffing** - Prevented by X-Content-Type-Options  
✅ **XSS Injections** - Controlled by Content-Security-Policy  
✅ **Cross-Site Scripting** - Limited by CSP script-src  
✅ **Unintended Feature Access** - Disabled by Permissions-Policy  

### Browser Compatibility

- ✅ Chrome 25+
- ✅ Firefox 23+
- ✅ Safari 7+
- ✅ Edge 12+
- ✅ IE 11+ (limited support for CSP)

---

## Additional Resources

- [OWASP Secure Headers](https://owasp.org/www-project-secure-headers/)
- [MDN: HTTP Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers)
- [Content Security Policy Guide](https://content-security-policy.com/)
- [Security Headers Reference](https://securityheaders.com/)

---

**Configuration Date:** January 25, 2026  
**Status:** Production Ready ✅  
**Last Updated:** January 25, 2026
