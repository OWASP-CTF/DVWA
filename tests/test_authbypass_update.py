from pathlib import Path


def test_authbypass_update_requires_admin_and_uses_parameters():
    source = Path("vulnerabilities/authbypass/change_user_details.php").read_text()
    assert 'if (dvwaCurrentUser() != "admin")' in source
    assert "mysqli_prepare" in source
    assert "mysqli_stmt_bind_param" in source
    assert "UPDATE users SET first_name = '" not in source
