from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]


def read(relative_path):
    return (ROOT / relative_path).read_text()


def test_file_inclusion_diagnostics_encode_http_metadata():
    source = read("vulnerabilities/fi/file3.php")
    for server_value in (
        "REMOTE_ADDR",
        "HTTP_X_FORWARDED_FOR",
        "HTTP_USER_AGENT",
        "HTTP_REFERER",
        "HTTP_HOST",
    ):
        assert f"htmlspecialchars( $_SERVER[ '{server_value}' ]" in source


def test_api_help_encodes_server_name_in_link():
    source = read("vulnerabilities/api/help/help.php")
    assert "htmlspecialchars($_SERVER['SERVER_NAME'] ?? '', ENT_QUOTES, 'UTF-8')" in source


def test_forms_do_not_reflect_php_self():
    for relative_path in (
        "vulnerabilities/api/source/medium.php",
        "vulnerabilities/cryptography/source/low.php",
        "vulnerabilities/cryptography/source/medium.php",
    ):
        assert "$_SERVER['PHP_SELF']" not in read(relative_path)
