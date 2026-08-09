from pathlib import Path


def test_crypto_medium_uses_authenticated_encryption():
    source = Path("vulnerabilities/cryptography/source/medium.php").read_text()
    assert 'aes-128-ecb' not in source
    assert 'ik ben een aardbei' not in source
    assert 'aes-256-gcm' in source
    assert 'random_bytes(12)' in source
    assert "getenv('DVWA_CRYPTO_KEY')" in source
    assert "'cryptography-medium:' . $key" in source
    assert 'ctype_xdigit($token)' in source
    assert 'json_decode ($decrypted, true)' in source
    assert '$errors = "Token validation failed"' in source


def test_compose_requires_crypto_key():
    compose = Path("compose.yml").read_text()
    assert 'DVWA_CRYPTO_KEY=${DVWA_CRYPTO_KEY:?' in compose
