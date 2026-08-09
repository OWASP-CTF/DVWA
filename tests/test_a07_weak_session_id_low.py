from pathlib import Path


CHALLENGE_ID = "Challenge-9-Weak-Session-IDs-Low"
OWASP_CATEGORY = "A07:2025 Authentication Failures"
VULNERABLE_FILE = "vulnerabilities/weak_id/source/low.php"

# These snippets identify the documented predictable-counter implementation.
VULNERABLE_SNIPPETS = (
    "$_SESSION['last_session_id']++",
    "$cookie_value = $_SESSION['last_session_id']",
)


def test_a07_low_predictable_session_snippet_is_removed():
    source = Path(VULNERABLE_FILE).read_text()
    assert CHALLENGE_ID in source
    assert OWASP_CATEGORY in source
    for snippet in VULNERABLE_SNIPPETS:
        assert snippet not in source


def test_a07_low_session_cookie_is_random_and_hardened():
    source = Path(VULNERABLE_FILE).read_text()
    assert "bin2hex(random_bytes(32))" in source
    assert '"path" => "/vulnerabilities/weak_id/"' in source
    assert '"secure" => true' in source
    assert '"httponly" => true' in source
    assert '"samesite" => "Strict"' in source
