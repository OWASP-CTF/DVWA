from pathlib import Path


def test_bac_current_user_lookups_are_parameterized():
    for level in ("low", "medium"):
        source = Path(f"vulnerabilities/bac/source/{level}.php").read_text()
        assert "mysqli_stmt_bind_param" in source
        assert "WHERE user = '" not in source
