from pathlib import Path


def test_crypto_high_uses_authenticated_random_encryption():
    source = Path("vulnerabilities/cryptography/source/token_library_high.php").read_text()
    assert 'aes-256-gcm' in source
    assert 'rainbowclimbinghigh' not in source
    assert 'random_bytes(12)' in source
    assert '"tag"' in source
