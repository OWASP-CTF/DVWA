from pathlib import Path


def test_bac_low_does_not_authorize_from_user_cookie():
    source = Path("vulnerabilities/bac/source/low.php").read_text()
    assert "dvwaCurrentUser() !== 'admin'" in source
    assert "$_COOKIE['user_id']" not in source
