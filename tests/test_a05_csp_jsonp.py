from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]


def read(relative_path):
    return (ROOT / relative_path).read_text()


def test_csp_high_levels_do_not_render_hidden_posted_markup():
    for level in ("high", "impossible"):
        source = read(f"vulnerabilities/csp/source/{level}.php")
        assert "$_POST['include']" not in source


def test_jsonp_accepts_only_the_expected_callback():
    source = read("vulnerabilities/csp/source/jsonp.php")
    assert "hash_equals('solveSum', $callback)" in source
    assert "http_response_code(400)" in source
    assert 'echo $callback' not in source
    assert "application/javascript" in source


def test_csp_clients_do_not_interpret_response_values_as_html():
    for level in ("high", "impossible"):
        source = read(f"vulnerabilities/csp/source/{level}.js")
        assert ".textContent = obj['answer']" in source
        assert ".innerHTML" not in source


def test_fixed_jsonp_response_uses_javascript_content_type():
    source = read("vulnerabilities/csp/source/jsonp_impossible.php")
    assert "application/javascript" in source
