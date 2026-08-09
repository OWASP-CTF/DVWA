from pathlib import Path


def read(path):
    return Path(path).read_text()


def test_health_connectivity_is_quoted_and_validated():
    source = read("vulnerabilities/api/src/HealthController.php")
    assert "FILTER_VALIDATE_IP" in source
    assert "escapeshellarg($target)" in source


def test_user_updates_reject_privilege_mass_assignment():
    source = read("vulnerabilities/api/src/UserController.php")
    assert 'array_key_exists ("level", $input)' not in source[source.index('private function updateUser'):source.index('public function processRequest')]
    assert "intval($input['level']) !== 1" in source


def test_user_creation_uses_random_hash_and_regular_level():
    source = read("vulnerabilities/api/src/UserController.php")
    assert "password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)" in source
    assert 'hash ("sha256", "password")' not in source


def test_order_updates_validate_types():
    source = read("vulnerabilities/api/src/OrderController.php")
    assert "strlen($input['name']) > 100" in source
    assert "strlen($input['address']) > 500" in source
    assert "!is_array($input['items'])" in source
