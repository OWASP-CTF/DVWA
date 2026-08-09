import http.cookiejar
import os
import re
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

    def read(self, path, data=None):
        with self.request(path, data) as response:
            if response.status != 200:
                raise AssertionError(f"{path} returned HTTP {response.status}")
            return response.read().decode(errors="replace")

    def get_token(self, path):
        body = self.read(path).encode()
        match = TOKEN_PATTERN.search(body)
        if match is None:
            raise AssertionError(f"No CSRF token found at {path}")
        return match.group(1).decode()

    def reset_database(self):
        token = self.get_token("/setup.php")
        self.read(
            "/setup.php",
            {"create_db": "Create / Reset Database", "user_token": token},
        )

    def login(self):
        token = self.get_token("/login.php")
        body = self.read(
            "/login.php",
            {
                "username": "admin",
                "password": "password",
                "Login": "Login",
                "user_token": token,
            },
        )
        if "Login failed" in body or "name=\"username\"" in body:
            raise AssertionError("DVWA login failed")

    def set_security_level(self, level):
        token = self.get_token("/security.php")
        body = self.read(
            "/security.php",
            {
                "security": level,
                "seclev_submit": "Submit",
                "user_token": token,
            },
        )
        if f"currently: <em>{level}</em>" not in body:
            raise AssertionError(f"Could not select DVWA security level {level}")
