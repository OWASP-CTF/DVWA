from pathlib import Path


def test_api_token_parser_uses_strict_base64_and_lengths():
    source = Path("vulnerabilities/api/src/Token.php").read_text()
    assert "base64_decode ($ciphertext, true)" in source
    assert "strlen($tag) !== 16" in source
    assert "return false;" in source
