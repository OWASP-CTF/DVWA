from pathlib import Path


SOURCE_DIR = Path("vulnerabilities/xss_r/source")
VULNERABLE_LEVELS = ("low", "medium", "high")


def test_reflected_xss_levels_encode_output_at_the_sink():
    for level in VULNERABLE_LEVELS:
        source = (SOURCE_DIR / f"{level}.php").read_text()

        assert "htmlspecialchars" in source
        assert "ENT_QUOTES" in source
        assert "'UTF-8'" in source


def test_reflected_xss_levels_do_not_render_raw_input():
    vulnerable_fragments = (
        "$_GET[ 'name' ] . '</pre>'",
        '"<pre>Hello {$name}</pre>"',
    )

    for level in VULNERABLE_LEVELS:
        source = (SOURCE_DIR / f"{level}.php").read_text()

        for fragment in vulnerable_fragments:
            assert fragment not in source
