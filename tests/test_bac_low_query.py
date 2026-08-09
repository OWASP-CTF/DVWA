from pathlib import Path


def test_bac_low_profile_queries_are_parameterized():
    source = Path("vulnerabilities/bac/source/low.php").read_text()
    assert source.count("mysqli_stmt_bind_param") >= 2
    assert "WHERE user_id = $id" not in source
