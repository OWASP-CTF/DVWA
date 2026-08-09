from pathlib import Path


def test_refresh_token_checker_handles_invalid_ciphertext():
    source = Path("vulnerabilities/api/src/Login.php").read_text()
    section = source[source.index("public static function check_refresh_token"):]
    assert "if ($decrypted === false)" in section
