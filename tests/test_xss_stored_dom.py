from pathlib import Path


STORED_DIR = Path("vulnerabilities/xss_s/source")
STORED_LEVELS = ("low", "medium", "high")
DOM_DIR = Path("vulnerabilities/xss_d")


def test_stored_xss_levels_encode_and_parameterize_guestbook_values():
    for level in STORED_LEVELS:
        source = (STORED_DIR / f"{level}.php").read_text()

        assert source.count("htmlspecialchars") >= 2
        assert "ENT_QUOTES" in source
        assert "$db->prepare" in source
        assert "bindParam" in source
        assert "VALUES ( '$message', '$name' )" not in source


def test_dom_xss_does_not_write_query_string_as_markup():
    page = (DOM_DIR / "index.php").read_text()
    low = (DOM_DIR / "source/low.php").read_text()
    medium = (DOM_DIR / "source/medium.php").read_text()

    assert "document.write" not in page
    assert "textContent" in page
    assert "stripos" not in medium
    assert "in_array" in medium
    assert "No protections" not in low
