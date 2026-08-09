<?php

// Whitelist of allowed files
$allowed_files = array('index.php', 'about.php', 'contact.php', 'help.php');

// Get the requested page
$file = isset($_GET['page']) ? $_GET['page'] : 'index.php';

// Block protocol wrappers (:// patterns) - check for common wrappers
$wrappers = array('://', 'php://', 'file://', 'data://', 'expect://', 'zip://');
foreach ($wrappers as $wrapper) {
    if (stripos($file, $wrapper) !== false) {
        $file = 'index.php';
        break;
    }
}

// Strip directory traversal attempts using basename
$file = basename($file);

// Validate against whitelist
if (!in_array($file, $allowed_files)) {
    $file = 'index.php';
}

?>
