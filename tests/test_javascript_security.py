from pathlib import Path
import unittest


ROOT = Path(__file__).resolve().parents[1]
ROUTE = (ROOT / "vulnerabilities" / "javascript" / "index.php").read_text(
    encoding="utf-8"
)


class JavaScriptRouteSecurityTests(unittest.TestCase):
    def test_all_levels_share_server_verified_acceptance(self):
        for level in ("low", "medium", "high"):
            with self.subTest(level=level):
                self.assertIn(f"case '{level}':", ROUTE)
                self.assertIn("hash_equals( $session_token, $user_token )", ROUTE)
                self.assertIn(
                    "SELECT password FROM users WHERE user = (:user) LIMIT 1", ROUTE
                )
                self.assertIn(
                    "hash_equals( $stored_password, md5( $password ) )", ROUTE
                )

    def test_browser_generated_token_is_not_an_acceptance_secret(self):
        self.assertNotIn('name="token"', ROUTE)
        self.assertNotIn("javascript_token", ROUTE)
        self.assertNotIn("$_POST[ 'client_token' ]", ROUTE)
        self.assertIn('name="client_token"', ROUTE)

    def test_server_verification_inputs_are_required_and_not_prepopulated(self):
        self.assertIn('array_key_exists ("password", $_POST)', ROUTE)
        self.assertIn('array_key_exists ("user_token", $_POST)', ROUTE)
        self.assertIn('name="password"', ROUTE)
        self.assertNotIn('name="password" value=', ROUTE)
        self.assertIn("generateSessionToken();", ROUTE)
        self.assertIn("tokenField();", ROUTE)


if __name__ == "__main__":
    unittest.main()
