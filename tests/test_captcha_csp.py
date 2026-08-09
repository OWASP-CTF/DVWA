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


def test_api_and_authbypass_boundaries_are_hardened():
    for level in ("low", "medium"):
        source = read(f"vulnerabilities/api/source/{level}.php")
        assert "user_info.innerHTML" not in source
    assert 'if (dvwaCurrentUser() != "admin")' in read("vulnerabilities/authbypass/get_user_data.php")
    source = read("vulnerabilities/authbypass/change_user_details.php")
    assert "mysqli_prepare" in source
    assert "mysqli_stmt_bind_param" in source


def test_bac_levels_require_server_side_admin_authorization():
    for level in ("low", "medium", "high"):
        source = read(f"vulnerabilities/bac/source/{level}.php")
        assert "dvwaCurrentUser() !== 'admin'" in source
        assert "Access denied." in source


def test_cryptography_levels_do_not_use_repeating_xor_or_ecb():
    low = read("vulnerabilities/cryptography/source/low.php")
    medium = read("vulnerabilities/cryptography/source/medium.php")
    assert "aes-256-gcm" in low
    assert "xor_this" not in low
    assert "aes-128-ecb" not in medium
    assert "aes-256-gcm" in medium


def test_file_inclusion_uses_allowlists_and_session_ids_are_unpredictable():
    for level in ("low", "medium"):
        assert "in_array($file, $allowed, true)" in read(f"vulnerabilities/fi/source/{level}.php")
    for level in ("low", "medium", "high"):
        source = read(f"vulnerabilities/weak_id/source/{level}.php")
        assert "random_bytes(32)" in source
        assert "httponly" in source
    assert "in_array($file, $allowed, true)" in read("vulnerabilities/fi/source/high.php")


def test_javascript_tokens_use_constant_time_comparison():
    source = read("vulnerabilities/javascript/index.php")
    assert source.count("hash_equals(") == 3


def test_upload_low_validates_content_and_uses_random_names():
    source = read("vulnerabilities/upload/source/low.php")
    assert "finfo(FILEINFO_MIME_TYPE)" in source
    assert "getimagesize($uploaded_tmp)" in source
    assert "random_bytes(16)" in source


def test_upload_medium_uses_detected_mime_and_random_names():
    source = read("vulnerabilities/upload/source/medium.php")
    assert "finfo(FILEINFO_MIME_TYPE)" in source
    assert "getimagesize($uploaded_tmp)" in source
    assert "random_bytes(16)" in source


def test_open_redirect_high_requires_exact_local_target():
    source = read("vulnerabilities/open_redirect/source/high.php")
    assert "if ($target === 'info.php')" in source
    assert 'header ("location: info.php")' in source
    assert 'header ("location: " . $_GET[\'redirect\'])' not in source


def test_open_redirect_medium_uses_local_allowlist():
    source = read("vulnerabilities/open_redirect/source/medium.php")
    assert "in_array($target, $allowed, true)" in source
    assert 'header ("location: info.php")' in source


def test_open_redirect_low_does_not_reflect_destination():
    source = read("vulnerabilities/open_redirect/source/low.php")
    assert "if ($target === 'info.php')" in source
    assert 'header ("location: " . $_GET[\'redirect\'])' not in source


def test_upload_high_uses_detected_type_and_random_name():
    source = read("vulnerabilities/upload/source/high.php")
    assert "finfo(FILEINFO_MIME_TYPE)" in source
    assert "random_bytes(16)" in source


def test_api_health_connectivity_validates_and_quotes_target():
    source = read("vulnerabilities/api/src/HealthController.php")
    assert "FILTER_VALIDATE_IP" in source
    assert 'escapeshellarg($target)' in source


def test_api_user_updates_reject_mass_assignment():
    source = read("vulnerabilities/api/src/UserController.php")
    assert "strlen($input['name']) > 100" in source
    assert 'array_key_exists ("level", $input)' not in source[source.index('private function updateUser'):source.index('private function updateUser') + 1800]


def test_api_order_updates_validate_field_types_and_lengths():
    source = read("vulnerabilities/api/src/OrderController.php")
    assert "strlen($input['name']) > 100" in source
    assert "strlen($input['address']) > 500" in source
    assert "!is_array($input['items'])" in source


def test_api_tokens_do_not_use_hardcoded_secrets():
    source = read("vulnerabilities/api/src/Login.php")
    assert '"12345"' not in source
    assert '"98765"' not in source
    assert "getenv('DVWA_ACCESS_TOKEN_SECRET')" in source


def test_api_json_login_uses_configured_password_verification():
    source = read("vulnerabilities/api/src/LoginController.php")
    assert 'getenv(\'DVWA_API_PASSWORD_HASH\')' in source
    assert 'password_verify($password, $expectedPasswordHash)' in source
    assert '$password == "becareful"' not in source[source.index('private function loginJSON'):source.index('private function login()')]


def test_api_basic_login_uses_configured_client_credentials():
    source = read("vulnerabilities/api/src/LoginController.php")
    login = source[source.index('private function login()'):]
    assert "getenv('DVWA_API_CLIENT_ID')" in login
    assert "getenv('DVWA_API_CLIENT_SECRET')" in login
    assert "hash_equals($expectedClient, $client_id)" in login
