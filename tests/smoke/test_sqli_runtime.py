import unittest
import urllib.parse

from dvwa_session import DvwaSession


class SqlInjectionRuntimeTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.session = DvwaSession()
        cls.session.reset_database()
        cls.session.login()

    def assert_no_user_record(self, body):
        self.assertNotIn("First name:", body)
        self.assertNotIn("Surname:", body)
        self.assertNotIn("SQL syntax", body)

    def test_low_preserves_lookup_and_rejects_boolean_injection(self):
        self.session.set_security_level("low")
        benign = self.session.read("/vulnerabilities/sqli/?id=1&Submit=Submit")
        self.assertIn("First name: admin", benign)
        self.assertNotIn("First name: Gordon", benign)

        payload = urllib.parse.quote("1' OR '1'='1", safe="")
        attacked = self.session.read(
            f"/vulnerabilities/sqli/?id={payload}&Submit=Submit"
        )
        self.assert_no_user_record(attacked)

    def test_medium_preserves_lookup_and_rejects_numeric_injection(self):
        self.session.set_security_level("medium")
        benign = self.session.read(
            "/vulnerabilities/sqli/", {"id": "2", "Submit": "Submit"}
        )
        self.assertIn("First name: Gordon", benign)
        self.assertNotIn("First name: admin", benign)

        attacked = self.session.read(
            "/vulnerabilities/sqli/", {"id": "1 OR 1=1", "Submit": "Submit"}
        )
        self.assert_no_user_record(attacked)

    def test_high_preserves_lookup_and_rejects_union_injection(self):
        self.session.set_security_level("high")
        self.session.read(
            "/vulnerabilities/sqli/session-input.php",
            {"id": "3", "Submit": "Submit"},
        )
        benign = self.session.read("/vulnerabilities/sqli/")
        self.assertIn("First name: Hack", benign)
        self.assertNotIn("First name: admin", benign)

        self.session.read(
            "/vulnerabilities/sqli/session-input.php",
            {
                "id": "1' UNION SELECT user,password FROM users#",
                "Submit": "Submit",
            },
        )
        attacked = self.session.read("/vulnerabilities/sqli/")
        self.assert_no_user_record(attacked)


if __name__ == "__main__":
    unittest.main()
