from pathlib import Path


def test_bac_logs_use_validated_ip_and_prepared_statements():
    for level in ("low", "medium"):
        source = Path(f"vulnerabilities/bac/source/{level}.php").read_text()
        assert "FILTER_VALIDATE_IP" in source
        assert "mysqli_stmt_bind_param" in source
        assert "{$ip}" not in source
