from pathlib import Path


def test_bac_medium_does_not_trust_client_token():
    source = Path("vulnerabilities/bac/source/medium.php").read_text()
    assert "dvwaCurrentUser() !== 'admin'" in source
    assert "user_token" not in source
