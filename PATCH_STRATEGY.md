# DVWA Security Patch Strategy - Grouped PRs

## Goal: Cover 44 remaining challenges with ~8-10 strategic PRs

### PR Grouping Strategy (Greedy Algorithm for Maximum Coverage)

---

## PR #1: OWASP Top 3 - Injection (SQLi + SQLi Blind)
**Branch:** `fix/owasp-injection-sqli`  
**Challenges:** 6 (SQLi: low/med/high + SQLi Blind: low/med/high)  
**CWE:** CWE-89  
**OWASP:** A03:2021 - Injection  
**Pattern:** Prepared Statements

Files to fix:
- vulnerabilities/sqli/source/{low,medium,high}.php
- vulnerabilities/sqli_blind/source/{low,medium,high}.php

Changes:
- mysqli_prepare() + mysqli_stmt_bind_param()
- htmlspecialchars() on output
- Generic error messages

---

## PR #2: XSS Prevention (All Types)
**Branch:** `fix/owasp-xss`  
**Challenges:** 9 (XSS-R: 3 + XSS-S: 3 + XSS-D: 3)  
**CWE:** CWE-79  
**OWASP:** A03:2021 - Injection  
**Pattern:** Output Encoding

Files to fix:
- vulnerabilities/xss_r/source/{low,medium,high}.php
- vulnerabilities/xss_s/source/{low,medium,high}.php
- vulnerabilities/xss_d/source/{low,medium,high}.php (JS files)

Changes:
- htmlspecialchars() ENT_QUOTES on ALL output
- Context-aware encoding
- DOM manipulation with safe APIs

---

## PR #3: File Operations (FI + Upload + Redirect)
**Branch:** `fix/owasp-file-ops`  
**Challenges:** 9 (FI: 3 + Upload: 3 + Redirect: 3)  
**CWE:** CWE-22, CWE-434, CWE-601  
**OWASP:** A01:2021 - Broken Access Control  
**Pattern:** Whitelist + Validation

Files to fix:
- vulnerabilities/fi/source/{low,medium,high}.php
- vulnerabilities/upload/source/{low,medium,high}.php
- vulnerabilities/open_redirect/source/{low,medium,high}.php

Changes:
- Whitelist allowed files/URLs
- basename() for path stripping
- MIME type + extension validation
- Block protocol wrappers

---

## PR #4: Access Control (BAC + Auth Bypass + API)
**Branch:** `fix/owasp-access-control`  
**Challenges:** 9 (BAC: 3 + Auth Bypass: 3 + API: 3)  
**CWE:** CWE-284  
**OWASP:** A01:2021 - Broken Access Control  
**Pattern:** Server-Side Identity

Files to fix:
- vulnerabilities/bac/source/{low,medium,high}.php
- vulnerabilities/authbypass/{*.php, source/*.php}
- vulnerabilities/api/src/*.php

Changes:
- Server-side identity checks
- Admin validation on ALL levels
- Prevent mass assignment
- Remove password exposure

---

## PR #5: Authentication (CSRF + CAPTCHA + Session)
**Branch:** `fix/owasp-auth`  
**Challenges:** 7 (CSRF: 3 + CAPTCHA: 3 + Weak Session: 1 remaining)  
**CWE:** CWE-352, CWE-307, CWE-330  
**OWASP:** A07:2021 - Auth Failures  
**Pattern:** Secure Tokens + Server Validation

Files to fix:
- vulnerabilities/csrf/source/{low,medium,high}.php
- vulnerabilities/captcha/source/{low,medium,high}.php
- vulnerabilities/weak_id/source/{low,medium,high}.php (if not done)

Changes:
- hash_equals() for token comparison
- Server-side CAPTCHA verification
- random_bytes() for sessions
- SameSite cookie flags

---

## PR #6: Cryptography + CSP + JavaScript
**Branch:** `fix/owasp-crypto-csp`  
**Challenges:** 9 (Crypto: 3 + CSP: 3 + JS: 3)  
**CWE:** CWE-327, CWE-79, CWE-602  
**OWASP:** A02/A03:2021  
**Pattern:** CSPRNG + Strict CSP

Files to fix:
- vulnerabilities/cryptography/source/{low,medium,high}.php
- vulnerabilities/csp/source/{low,medium,high}.php
- vulnerabilities/javascript/source/{low,medium,high}.php

Changes:
- random_bytes() for all random gen
- Strict CSP headers with nonce
- Server-side validation (not client)
- Remove unsafe-inline/eval

---

## Implementation Priority

1. **PR #1 (SQLi)** - 6 challenges, high impact ✓ IN PROGRESS
2. **PR #2 (XSS)** - 9 challenges, highest count
3. **PR #3 (File Ops)** - 9 challenges, diverse vulnerabilities
4. **PR #4 (Access Control)** - 9 challenges, critical security
5. **PR #5 (Auth)** - 7 challenges, auth is critical
6. **PR #6 (Crypto/CSP)** - 9 challenges, completes coverage

**Total:** 49 challenges across 6 PRs  
**Efficiency:** ~8.2 challenges per PR  
**Token Usage:** Optimized via grouping by CWE/pattern

---

## Execution Plan

```bash
# For each PR group:
git checkout dc34-ctf
git checkout -b fix/owasp-{category}

# Apply fixes using patch templates
# Commit with detailed message
# Push to origin
# Create PR with --no-maintainer-edit

gh pr create \
  --title "fix({category}): {description}" \
  --body "@{patch-summary.md}" \
  --base dc34-ctf \
  --head markuszaki:fix/owasp-{category} \
  --no-maintainer-edit
```

---

## Patch Templates

Each PR uses a standard template:
1. Security pattern description
2. Vulnerability explanation
3. Fix implementation details
4. Files changed
5. Testing checklist
6. OWASP/CWE references

---

## Expected Outcome

- **Current:** 21/108 points (19%)
- **After 6 PRs:** ~70/108 points (65%+)
- **Remaining:** Command injection (already PR #182), Brute force (already PR #190)

**Final Target:** 80+/108 points (75%+)
