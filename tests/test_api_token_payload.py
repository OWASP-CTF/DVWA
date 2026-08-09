from pathlib import Path


def test_api_token_payload_is_structurally_validated():
    source = Path("vulnerabilities/api/src/Token.php").read_text()
    assert "!is_array($token)" in source
    assert "isset($token['secret'], $token['expires'])" in source
    assert "!is_int($token['expires'])" in source
