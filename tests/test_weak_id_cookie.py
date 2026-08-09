from pathlib import Path
import subprocess


SOURCE = "vulnerabilities/weak_id/source/impossible.php"


def secure_flag_for(https_value):
    source = Path(__file__).resolve().parents[1] / SOURCE
    script = (
        "$_SERVER['REQUEST_METHOD'] = 'GET'; "
        + (f"$_SERVER['HTTPS'] = '{https_value}'; " if https_value is not None else "")
        + f"require '{source}'; "
        + "echo weakIdCookieSecure() ? 'secure' : 'insecure';"
    )
    return subprocess.run(["php", "-r", script], check=True, capture_output=True, text=True).stdout


def test_weak_id_cookie_uses_secure_flag_only_for_https():
    assert secure_flag_for(None) == "insecure"
    assert secure_flag_for("off") == "insecure"
    assert secure_flag_for("on") == "secure"


def test_weak_id_cookie_keeps_httponly_and_uses_transport_flag():
    source = (Path(__file__).resolve().parents[1] / SOURCE).read_text()
    assert "weakIdCookieSecure(), true" in source
