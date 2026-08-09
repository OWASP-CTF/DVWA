from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]


def read(relative_path):
    return (ROOT / relative_path).read_text()


def test_help_viewer_does_not_evaluate_file_contents():
    source = read("vulnerabilities/view_help.php")
    assert "eval(" not in source
    assert "dvwaVulnerabilityNameGet( $id ) !== null" in source
    assert "dvwaSecurityLevelIsValid( $security )" in source
    assert "dvwaLocaleIsValid( $locale )" in source


def test_source_viewers_allow_only_known_challenges_and_levels():
    source = read("vulnerabilities/view_source.php")
    source_all = read("vulnerabilities/view_source_all.php")
    assert "dvwaVulnerabilityNameGet( $id )" in source
    assert "dvwaSecurityLevelIsValid( $security )" in source
    assert "dvwaVulnerabilityNameGet( $_GET['id'] ) !== null" in source_all


def test_credential_helper_separates_login_data_from_sql_and_html():
    source = read("vulnerabilities/csrf/test_credentials.php")
    assert "$db->prepare(" in source
    assert "bindParam( ':user', $user, PDO::PARAM_STR )" in source
    assert "htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' )" in source
    assert "WHERE user='$user'" not in source
