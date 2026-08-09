from pathlib import Path


def test_api_user_delete_requires_bearer_access_token():
    source = Path("vulnerabilities/api/src/UserController.php").read_text()
    section = source[source.index("private function deleteUser"):source.index("public function processRequest")]
    assert "HTTP_AUTHORIZATION" in section
    assert "Login::check_access_token" in section
    assert "401 Unauthorized" in section
