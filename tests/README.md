# Tests

## Runtime security smoke tests

The runtime suite builds on real HTTP behavior: it resets the test database, logs in,
selects the required security levels, verifies benign behavior, and submits exploit
regression payloads. Start DVWA first, then run:

```
DVWA_BASE_URL=http://127.0.0.1:4280 \
  python3 -m unittest discover -s tests/smoke -v
```

The `Runtime security tests` pull-request workflow builds the exact PR image and runs
this suite without third-party Python dependencies or privileged GitHub permissions.

## Source guardrails

The other `test_*.py` modules inspect source code for known-dangerous patterns. They
are fast regression guardrails, but they do not replace runtime exploit tests.

## test_url.py

This test will find all fully qualified URLs mentioned in any PHP script and will check if the URL is still alive. This helps weed out dead links from documentation and references.

Do not run `test_url.py` in pull-request security CI. It makes nondeterministic outbound
requests to URLs found in PR-controlled files. Run it only as a separately isolated,
scheduled documentation-maintenance job with an explicit destination allow-list.
