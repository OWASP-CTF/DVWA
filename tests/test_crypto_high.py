from pathlib import Path


def test_crypto_high_uses_authenticated_random_encryption():
    source = Path("vulnerabilities/cryptography/source/token_library_high.php").read_text()
    assert 'aes-256-gcm' in source
    assert 'rainbowclimbinghigh' not in source
    assert 'dvwa-development-key' not in source
    assert "getenv('DVWA_CRYPTO_KEY')" in source
    assert 'strlen($key) < 32' in source
    assert 'random_bytes(12)' in source
    assert 'function encrypt ($plaintext, $iv, &$tag)' in source
    assert 'encrypt ($token, $iv, $tag)' in source
    assert 'if (!is_array ($data_array))' in source
    assert "base64_decode ($data_array['token'], true)" in source
    decrypt_failure_handler = source.rsplit('} catch (Exception $exp) {', 1)[1]
    assert '"extra" => $exp->getMessage()' not in decrypt_failure_handler
    assert '"tag"' in source


def test_compose_requires_crypto_key():
    compose = Path("compose.yml").read_text()
    assert 'DVWA_CRYPTO_KEY=${DVWA_CRYPTO_KEY:?' in compose
