from pathlib import Path


def test_api_token_secrets_use_constant_time_comparisons():
    source = Path("vulnerabilities/api/src/Login.php").read_text()
    assert source.count("hash_equals(") >= 2
