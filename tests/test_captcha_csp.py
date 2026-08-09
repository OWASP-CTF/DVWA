from pathlib import Path


def read(path):
    return Path(path).read_text()


def test_captcha_high_has_no_hardcoded_bypass():
    source = read("vulnerabilities/captcha/source/high.php")
    assert "hidd3n_valu3" not in source
    assert "HTTP_USER_AGENT" not in source
    assert "$resp" in source


def test_captcha_low_and_medium_use_server_side_transition_state():
    for level in ("low", "medium"):
        source = read(f"vulnerabilities/captcha/source/{level}.php")
        assert "$_SESSION['captcha_passed'] = true" in source
        assert "captcha_passed" in source


def test_csp_levels_do_not_render_submitted_markup():
    for level in ("low", "medium"):
        source = read(f"vulnerabilities/csp/source/{level}.php")
        assert ". $_POST['include']" not in source
        assert "<script src='" not in source


def test_command_execution_levels_validate_ip_and_quote_command_argument():
    for level in ("low", "medium", "high"):
        source = read(f"vulnerabilities/exec/source/{level}.php")
        assert "FILTER_VALIDATE_IP" in source
        assert "escapeshellarg($target)" in source
        assert "shell_exec( 'ping  ' . $target )" not in source
