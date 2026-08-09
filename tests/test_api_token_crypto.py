from pathlib import Path


def test_api_tokens_use_configured_aes256_key():
    source = Path("vulnerabilities/api/src/Token.php").read_text()
    assert 'aes-256-gcm' in source
    assert 'Paintbrush' not in source
    assert "getenv('DVWA_TOKEN_ENCRYPTION_KEY')" in source
