import subprocess
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[1]
TARGET_HELPER = REPO_ROOT / "vulnerabilities/api/src/NetworkTarget.php"


def resolve(target, records=None):
    lookup = "null"
    if records is not None:
        lookup = "function ($hostname) { return " + records + "; }"
    code = (
        f"require '{TARGET_HELPER}'; "
        f"$result = Src\\NetworkTarget::resolvePublicTarget('{target}', {lookup}); "
        "echo $result === null ? 'null' : $result;"
    )
    return subprocess.run(
        ["php", "-r", code], check=True, capture_output=True, text=True
    ).stdout


def test_allows_public_ipv4_and_ipv6_literals():
    assert resolve("8.8.8.8") == "8.8.8.8"
    assert resolve("2001:4860:4860::8888") == "2001:4860:4860::8888"


def test_rejects_private_and_reserved_literals():
    for address in ("127.0.0.1", "10.0.0.1", "169.254.1.1", "::1", "fc00::1"):
        assert resolve(address) == "null"


def test_rejects_hostname_resolving_only_to_private_or_reserved_addresses():
    assert resolve("internal.example", "[['ip' => '10.0.0.1']]") == "null"
    assert resolve("loopback.example", "[['ipv6' => '::1']]") == "null"


def test_uses_the_resolved_public_address_for_hostnames():
    assert resolve("public.example", "[['ip' => '8.8.4.4']]") == "8.8.4.4"
