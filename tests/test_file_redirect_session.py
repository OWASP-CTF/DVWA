from pathlib import Path


def read(path):
    return Path(path).read_text()


def test_file_inclusion_levels_use_an_explicit_allowlist():
    for level in ("low", "medium", "high"):
        source = read(f"vulnerabilities/fi/source/{level}.php")
        assert "in_array" in source
        assert "file1.php" in source
        assert "../" not in source


def test_open_redirect_levels_only_allow_known_local_destinations():
    for level in ("low", "medium", "high"):
        source = read(f"vulnerabilities/open_redirect/source/{level}.php")
        assert 'info.php?id=1' in source
        assert 'info.php?id=2' in source
        assert 'http://' not in source
        assert 'https://' not in source


def test_weak_session_ids_use_random_bytes():
    for level in ("low", "medium", "high"):
        source = read(f"vulnerabilities/weak_id/source/{level}.php")
        assert "random_bytes(32)" in source
        assert "$cookie_value = time()" not in source
        assert "md5($_SESSION" not in source


def test_high_upload_does_not_serve_user_controlled_filenames():
    source = read("vulnerabilities/upload/source/high.php")
    assert "is_uploaded_file" in source
    assert "getimagesize" in source
    assert "random_bytes(16)" in source
    assert "basename( $_FILES" not in source
