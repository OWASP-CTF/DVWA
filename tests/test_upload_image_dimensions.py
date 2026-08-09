from pathlib import Path
import subprocess


SOURCE = "vulnerabilities/upload/source/impossible.php"


def dimensions_allowed(width, height):
    source = Path(__file__).resolve().parents[1] / SOURCE
    script = (
        "function generateSessionToken() {} "
        "$_POST = array(); "
        f"require '{source}'; "
        f"echo uploadImageDimensionsAllowed(array({width}, {height})) ? 'allowed' : 'rejected';"
    )
    return subprocess.run(["php", "-r", script], check=True, capture_output=True, text=True).stdout


def test_safe_dimensions_are_allowed_before_gd_decode():
    assert dimensions_allowed(1920, 1080) == "allowed"


def test_oversized_dimensions_are_rejected_before_gd_decode():
    assert dimensions_allowed(100000, 100000) == "rejected"
