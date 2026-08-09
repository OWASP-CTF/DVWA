from pathlib import Path


def test_api_user_serialization_does_not_expose_password_hashes():
    source = Path("vulnerabilities/api/src/User.php").read_text()
    version_one = source[source.index("case 1:"):source.index("default:")]
    assert '"password" => $this->password' not in version_one
