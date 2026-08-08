from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]


def source(relative_path: str) -> str:
    return (ROOT / relative_path).read_text(encoding="utf-8")


def test_high_weak_id_cookie_is_host_only_and_script_inaccessible():
    body = source("vulnerabilities/weak_id/source/high.php")
    assert "$_SERVER['HTTP_HOST']" not in body
    assert "'secure'   => true" in body
    assert "'httponly' => true" in body
    assert "'samesite' => 'Strict'" in body
    assert "random_bytes(20)" in body


def test_high_sqli_state_setters_reject_non_numeric_ids():
    session_input = source("vulnerabilities/sqli/session-input.php")
    cookie_input = source("vulnerabilities/sqli_blind/cookie-input.php")

    for body in (session_input, cookie_input):
        assert "checkToken( $_REQUEST[ 'user_token' ]" in body
        assert "generateSessionToken();" in body
        assert '" . tokenField() . "' in body
        assert "is_string( $id )" in body
        assert "preg_match( '/^\\d+$/D', $id )" in body
        assert "http_response_code( 422 )" in body

    assert "$_SESSION[ 'id' ] = (int) $id" in session_input
    assert "setcookie( 'id', (string) (int) $id" in cookie_input


def test_blind_sqli_high_has_no_artificial_timing_branch():
    body = source("vulnerabilities/sqli_blind/source/high.php")
    assert "sleep(" not in body
    assert "rand(" not in body


def test_brute_high_enforces_database_backed_three_failure_window():
    body = source("vulnerabilities/brute/source/high.php")
    assert "$total_failed_login = 3" in body
    assert "sleep( 2 );" in body
    assert "SELECT failed_login, last_login FROM users" in body
    assert "failed_login = failed_login + 1, last_login = NOW()" in body
    assert "UPDATE users SET failed_login = 0" in body


def test_crypto_low_has_no_installation_wide_known_password():
    body = source("vulnerabilities/cryptography/source/low.php")
    assert 'password == "Olifant"' not in body
    assert "$_SESSION['cryptography_low_password'] = bin2hex(random_bytes(16))" in body
    assert "hash_equals($login_password, $password)" in body
    assert 'low_encode ("Your new password is: " . $login_password)' in body


def test_csp_low_resolves_only_repository_owned_script_ids():
    body = source("vulnerabilities/csp/source/low.php")
    assert "'sum' => 'source/high.js'" in body
    assert "array_key_exists($scriptId, $allowedScripts)" in body
    assert "htmlspecialchars($allowedScripts[$scriptId]" in body
    assert "htmlspecialchars( $_POST['include']" not in body


def test_csrf_high_requires_current_password_reauthentication():
    body = source("vulnerabilities/csrf/source/high.php")
    index = source("vulnerabilities/csrf/index.php")
    assert 'array_key_exists("password_current", $data)' in body
    assert 'array_key_exists("password_current", $_REQUEST)' in body
    assert "SELECT password FROM users WHERE user = (:user) AND password = (:password)" in body
    assert "$current->rowCount() == 1" in body
    assert "$vulnerabilityFile == 'high.php' || $vulnerabilityFile == 'impossible.php'" in index


def test_injection_and_reflected_xss_forms_require_session_tokens():
    modules = {
        "exec": ("low", "medium", "high"),
        "sqli": ("low", "medium"),
        "sqli_blind": ("low", "medium"),
        "xss_r": ("low", "medium", "high"),
    }
    for module, levels in modules.items():
        index = source(f"vulnerabilities/{module}/index.php")
        assert "if( true )" in index
        for level in levels:
            body = source(f"vulnerabilities/{module}/source/{level}.php")
            assert "checkToken( $_REQUEST[ 'user_token' ]" in body
            assert "generateSessionToken();" in body
