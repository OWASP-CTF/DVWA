from pathlib import Path


def test_refresh_flow_validates_refresh_token_field():
    source = Path("vulnerabilities/api/src/LoginController.php").read_text()
    section = source[source.index("private function refresh"):]
    assert "$token = $input['refresh_token'];" in section
    assert "Login::check_refresh_token($token)" in section
