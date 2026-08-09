from pathlib import Path
import unittest


ROOT = Path(__file__).resolve().parents[1]


def source(path):
    return (ROOT / path).read_text(encoding="utf-8")


class ResidualSecurityTests(unittest.TestCase):
    def test_every_brute_level_requires_and_rotates_a_session_token(self):
        index = source("vulnerabilities/brute/index.php")
        self.assertIn("tokenField()", index)
        self.assertNotIn("if( $vulnerabilityFile == 'high.php' ||", index)

        for level in ("low", "medium", "high"):
            with self.subTest(level=level):
                body = source(f"vulnerabilities/brute/source/{level}.php")
                self.assertIn("checkToken(", body)
                self.assertIn("generateSessionToken();", body)
                self.assertNotIn("rowCount()", body)

    def test_brute_failure_delays_cannot_randomly_be_zero(self):
        for level in ("low", "medium", "high"):
            with self.subTest(level=level):
                body = source(f"vulnerabilities/brute/source/{level}.php")
                self.assertIn("sleep( 2 );", body)
                self.assertNotIn("rand( 0", body)

    def test_csp_low_never_builds_a_script_element_from_submitted_input(self):
        body = source("vulnerabilities/csp/source/low.php")
        self.assertIn("htmlspecialchars ($include, ENT_QUOTES, 'UTF-8')", body)
        self.assertNotIn("src='\" . htmlspecialchars", body)
        self.assertIn('<script src="source/impossible.js"></script>', body)

    def test_open_redirect_rejections_are_normal_non_redirect_responses(self):
        for level in ("low", "medium", "high"):
            with self.subTest(level=level):
                body = source(f"vulnerabilities/open_redirect/source/{level}.php")
                self.assertNotIn("http_response_code (500)", body)
                self.assertIn('header ("Location: " . $target, true, 302);', body)

    def test_captcha_high_validates_token_and_has_fail_secure_no_key_path(self):
        index = source("vulnerabilities/captcha/index.php")
        high = source("vulnerabilities/captcha/source/high.php")

        self.assertIn("'high.php' || $vulnerabilityFile == 'impossible.php'", index)
        self.assertIn("checkToken(", high)
        self.assertIn("recaptcha_private_key' ] != ''", high)
        self.assertIn("$current_password_ok", high)
        self.assertNotIn("hidd3n_valu3", high)

    def test_javascript_levels_do_not_disclose_the_accepted_token(self):
        body = source("vulnerabilities/javascript/index.php")

        self.assertIn("random_bytes( 32 )", body)
        self.assertIn("hash_hmac( 'sha256', $phrase", body)
        self.assertIn("hash_equals( $expected_token, $token )", body)
        self.assertNotIn("$server_token", body)
        self.assertNotIn('name="client_token"', body)
        self.assertIn('name="token" value="" id="token"', body)


if __name__ == "__main__":
    unittest.main()
