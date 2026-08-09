from pathlib import Path


def test_bac_medium_profile_queries_are_parameterized():
    source = Path("vulnerabilities/bac/source/medium.php").read_text()
    assert "mysqli_stmt_bind_param" in source
    assert "WHERE user_id = '$id'" not in source
