from pathlib import Path


def test_authbypass_user_data_requires_admin_and_encodes_names():
    source = Path("vulnerabilities/authbypass/get_user_data.php").read_text()
    assert 'if (dvwaCurrentUser() != "admin")' in source
    assert "htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8')" in source
    assert "htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8')" in source
