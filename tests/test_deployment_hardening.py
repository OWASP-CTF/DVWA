from pathlib import Path


def test_image_installs_global_security_configuration():
    dockerfile = Path("Dockerfile").read_text()
    assert "a2enmod headers rewrite" in dockerfile
    assert "config/apache-security.conf" in dockerfile
    assert "config/php-security.ini" in dockerfile
    assert "composer install --no-dev" in dockerfile
    assert "apt-get purge -y --auto-remove git zip unzip 7zip" in dockerfile


def test_apache_security_headers_and_identity_are_hardened():
    config = Path("config/apache-security.conf").read_text()
    assert "ServerName localhost" in config
    assert "ServerTokens Prod" in config
    assert "ServerSignature Off" in config
    assert "TraceEnable Off" in config
    assert 'X-Content-Type-Options "nosniff"' in config
    assert 'X-Frame-Options "SAMEORIGIN"' in config
    assert 'Referrer-Policy "strict-origin-when-cross-origin"' in config
    assert "Permissions-Policy" in config


def test_php_errors_are_logged_but_not_exposed():
    config = Path("config/php-security.ini").read_text()
    assert "display_errors = Off" in config
    assert "display_startup_errors = Off" in config
    assert "expose_php = Off" in config
    assert "log_errors = On" in config


def test_compose_prevents_privilege_escalation():
    compose = Path("compose.yml").read_text()
    assert compose.count("no-new-privileges:true") == 2
