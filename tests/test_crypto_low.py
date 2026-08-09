from pathlib import Path


def test_crypto_low_uses_authenticated_encryption_and_password_hash():
    source = Path("vulnerabilities/cryptography/source/low.php").read_text()
    assert 'xor_this' not in source
    assert 'wachtwoord' not in source
    assert 'aes-256-gcm' in source
    assert 'random_bytes(12)' in source
    assert 'base64_decode($encoded, true)' in source
    assert "'cryptography-low:' . $key" in source
    assert "getenv('DVWA_CRYPTO_LOW_PASSWORD_HASH')" in source
    assert 'password_verify($password, $password_hash)' in source
    assert '$password == "Olifant"' not in source


def test_compose_requires_low_crypto_configuration():
    compose = Path("compose.yml").read_text()
    assert 'DVWA_CRYPTO_KEY=${DVWA_CRYPTO_KEY:?' in compose
    assert 'DVWA_CRYPTO_LOW_PASSWORD_HASH=${DVWA_CRYPTO_LOW_PASSWORD_HASH:?' in compose
