from pathlib import Path
import subprocess


SOURCE = "external/recaptcha/recaptchalib.php"


def verification_result(payload):
    source = Path(__file__).resolve().parents[1] / SOURCE
    script = f"require '{source}'; echo captchaVerificationSucceeded({payload}) ? 'accepted' : 'rejected';"
    return subprocess.run(["php", "-r", script], check=True, capture_output=True, text=True).stdout


def test_recaptcha_result_parsing_fails_closed():
    assert verification_result("'{\"success\":true}'") == "accepted"
    assert verification_result("'{\"success\":false}'") == "rejected"
    assert verification_result("'not-json'") == "rejected"


def test_recaptcha_verification_has_a_short_http_timeout():
    source = (Path(__file__).resolve().parents[1] / SOURCE).read_text()
    assert "'timeout' => 5" in source
