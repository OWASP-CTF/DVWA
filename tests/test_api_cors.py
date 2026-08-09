from pathlib import Path


def test_api_cors_does_not_allow_any_origin():
    source = Path("vulnerabilities/api/public/index.php").read_text()
    assert 'Access-Control-Allow-Origin: *' not in source
    assert "DVWA_API_ALLOWED_ORIGIN" in source
