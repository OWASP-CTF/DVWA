<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // SECURE FIX: Generate a cryptographically secure random session token
    $cookie_value = bin2hex(random_bytes(32));

    // Set secure cookie with HttpOnly and SameSite flags
    setcookie(
        "dvwaSession",
        $cookie_value,
        [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );
}

?>