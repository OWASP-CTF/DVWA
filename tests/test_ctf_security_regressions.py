from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]


def source(relative_path: str) -> str:
    return (ROOT / relative_path).read_text(encoding="utf-8")


def test_authbypass_sets_denial_status_before_output():
    for level in ("low", "medium", "high", "impossible"):
        body = source(f"vulnerabilities/authbypass/source/{level}.php")
        assert body.index("http_response_code(403)") < body.index('print "Unauthorised"')

    for endpoint in ("change_user_details.php", "get_user_data.php"):
        body = source(f"vulnerabilities/authbypass/{endpoint}")
        denied = body.index('"error" => "Access denied"')
        assert body.rfind("http_response_code(403)", 0, denied) != -1


def test_bac_low_displays_only_server_derived_role():
    body = source("vulnerabilities/bac/source/low.php")
    assert "$display_role = $role" in body
    assert "$role = isset($_COOKIE['user_role'])" not in body
    assert "htmlspecialchars($display_role, ENT_QUOTES, 'UTF-8')" in body


def test_high_weak_id_cookie_is_host_only_and_script_inaccessible():
    body = source("vulnerabilities/weak_id/source/high.php")
    assert "$_SERVER['HTTP_HOST']" not in body
    assert "'httponly' => true" in body
    assert "'samesite' => 'Strict'" in body
    assert "random_bytes(20)" in body


def test_high_sqli_state_setters_reject_non_numeric_ids():
    session_input = source("vulnerabilities/sqli/session-input.php")
    cookie_input = source("vulnerabilities/sqli_blind/cookie-input.php")

    for body in (session_input, cookie_input):
        assert "is_string( $id )" in body
        assert "preg_match( '/^\\d+$/D', $id )" in body
        assert "http_response_code( 422 )" in body

    assert "$_SESSION[ 'id' ] = (int) $id" in session_input
    assert "setcookie( 'id', (string) (int) $id" in cookie_input


def test_blind_sqli_high_has_no_artificial_timing_branch():
    body = source("vulnerabilities/sqli_blind/source/high.php")
    assert "sleep(" not in body
    assert "rand(" not in body


def test_brute_high_enforces_one_atomic_three_failure_window():
    body = source("vulnerabilities/brute/source/high.php")
    assert "$max_fail = $total_failed_login" in body
    assert "flock( $bucket_handle, LOCK_EX )" in body
    assert "flock( $bucket_handle, LOCK_UN )" in body
    assert "@file_put_contents" not in body
