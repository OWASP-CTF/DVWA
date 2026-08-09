import http.cookiejar
import os
import re
import unittest
import urllib.error
import urllib.parse
import urllib.request


BASE_URL = os.environ.get("DVWA_BASE_URL", "http://127.0.0.1:4280").rstrip("/")
TOKEN_PATTERN = re.compile(
    rb"name=['\"]user_token['\"]\s+value=['\"]([^'\"]+)['\"]"
)


class DvwaSession:
    def __init__(self):
        cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(
            urllib.request.HTTPCookieProcessor(cookies)
        )

    def request(self, path, data=None):
        encoded = urllib.parse.urlencode(data).encode() if data is not None else None
        request = urllib.request.Request(BASE_URL + path, data=encoded)
        return self.opener.open(request, timeout=10)

    def get_token(self, path):
        with self.request(path) as response:
            body = response.read()
        match = TOKEN_PATTERN.search(body)
        if match is None:
            raise AssertionError(f"No CSRF token found at {path}")
        return match.group(1).decode()

    def reset_database(self):
        token = self.get_token("/setup.php")
        with self.request(
            "/setup.php",
            {"create_db": "Create / Reset Database", "user_token": token},
        ) as response:
            if response.status != 200:
                raise AssertionError(f"Database setup returned {response.status}")

    def login(self):
        token = self.get_token("/login.php")
        with self.request(
            "/login.php",
            {
                "username": "admin",
                "password": "password",
                "Login": "Login",
                "user_token": token,
            },
        ) as response:
            body = response.read()
            if response.geturl().endswith("login.php") or b"Login failed" in body:
                raise AssertionError("DVWA login failed")

    def set_security_level(self, level):
        token = self.get_token("/security.php")
        with self.request(
            "/security.php",
            {
                "security": level,
                "seclev_submit": "Submit",
                "user_token": token,
            },
        ) as response:
            body = response.read()
        if f"currently: <em>{level}</em>".encode() not in body:
            raise AssertionError(f"Could not select DVWA security level {level}")


class RuntimeSecuritySmokeTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.session = DvwaSession()
        cls.session.reset_database()
        cls.session.login()

    def test_core_pages_are_reachable(self):
        for path in ("/index.php", "/security.php", "/vulnerabilities/csp/"):
            with self.session.request(path) as response:
                self.assertEqual(response.status, 200, path)

    def test_expected_jsonp_callback_still_works(self):
        with self.session.request(
            "/vulnerabilities/csp/source/jsonp.php?callback=solveSum"
        ) as response:
            body = response.read()
            self.assertEqual(response.status, 200)
            self.assertEqual(
                response.headers.get_content_type(), "application/javascript"
            )
            self.assertEqual(body, b'solveSum({"answer":"15"});')

    def test_injected_jsonp_callbacks_are_rejected(self):
        callbacks = (
            "alert(1)//",
            "solveSum;alert(1)//",
            "solveSum%0Aalert(1)//",
            "",
        )
        for callback in callbacks:
            encoded = urllib.parse.quote(callback, safe="%")
            path = f"/vulnerabilities/csp/source/jsonp.php?callback={encoded}"
            with self.subTest(callback=callback):
                with self.assertRaises(urllib.error.HTTPError) as error:
                    self.session.request(path)
                with error.exception:
                    self.assertEqual(error.exception.code, 400)
                    self.assertEqual(error.exception.read(), b"")

    def test_high_levels_do_not_reflect_hidden_posted_markup(self):
        payload = '<img src=x onerror="alert(1)">'
        for level in ("high", "impossible"):
            with self.subTest(level=level):
                self.session.set_security_level(level)
                with self.session.request(
                    "/vulnerabilities/csp/", {"include": payload}
                ) as response:
                    body = response.read().decode(errors="replace")
                self.assertNotIn(payload, body)
                self.assertIn("Solve the sum", body)


if __name__ == "__main__":
    unittest.main()
