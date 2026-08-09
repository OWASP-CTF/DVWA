from pathlib import Path


CHECKERS = (
    "vulnerabilities/cryptography/source/check_token_high.php",
    "vulnerabilities/cryptography/source/check_token_impossible.php",
)


def test_crypto_token_checkers_require_an_authenticated_session():
    repository_root = Path(__file__).resolve().parents[1]

    for checker in CHECKERS:
        source = (repository_root / checker).read_text()
        assert "dvwaPageStartup( array( 'authenticated' ) );" in source
        assert source.index("dvwaPageStartup") < source.index("token_library_impossible.php")
