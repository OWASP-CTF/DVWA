<?php

// Whitelist of allowed redirect targets
$allowed_redirects = array(
    'index.php',
    'home.php',
    'about.php',
    'info.php',
    'help.php'
);

if (array_key_exists("redirect", $_GET) && $_GET['redirect'] != "") {
    $redirect = $_GET['redirect'];
    
    // Block all protocol wrappers (not just http/https)
    if (preg_match('/^[a-z]+:\/\//i', $redirect)) {
        http_response_code(400);
        ?>
        <p>Protocol wrappers not allowed.</p>
        <?php
        exit;
    }
    
    // Use basename to strip any path traversal
    $redirect = basename($redirect);
    
    // Validate against whitelist
    if (in_array($redirect, $allowed_redirects)) {
        header("location: " . $redirect);
        exit;
    } else {
        http_response_code(400);
        ?>
        <p>Invalid redirect target.</p>
        <?php
        exit;
    }
}

http_response_code(400);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
