from pathlib import Path


SOURCE_DIR = Path("vulnerabilities/sqli_blind/source")
VULNERABLE_LEVELS = ("low", "medium", "high")


def test_blind_sqli_levels_use_parameterized_queries():
    for level in VULNERABLE_LEVELS:
        source = (SOURCE_DIR / f"{level}.php").read_text()

        assert "is_numeric( $id )" in source
        assert "intval( $id )" in source
        assert "$db->prepare(" in source
        assert "bindParam( ':id', $id, PDO::PARAM_INT )" in source
        assert "->prepare(" in source
        assert "bindValue( ':id', $id, SQLITE3_INTEGER )" in source
        assert "->execute()" in source


def test_blind_sqli_levels_do_not_interpolate_ids_into_queries():
    vulnerable_fragments = (
        "user_id = '$id'",
        'user_id = "$id"',
        "user_id = $id",
    )

    for level in VULNERABLE_LEVELS:
        source = (SOURCE_DIR / f"{level}.php").read_text()

        for fragment in vulnerable_fragments:
            assert fragment not in source


def test_blind_sqli_levels_do_not_leak_status_or_timing_oracles():
    for level in VULNERABLE_LEVELS:
        source = (SOURCE_DIR / f"{level}.php").read_text()

        assert "404 Not Found" not in source
        assert "sleep(" not in source
        assert "rand(" not in source
