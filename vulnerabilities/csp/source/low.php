<?php

// SECURE FIX: Enforce a strict Content-Security-Policy allowing only local scripts
$headerCSP = "Content-Security-Policy: script-src 'self';";
header($headerCSP);

if (isset ($_POST['include'])) {
    // Validate that the included script is local and safe
    $page = $_POST['include'];
    if ($page === "source/jsonp.php" || $page === "source/high.js") {
        echo "<script src='" . htmlspecialchars($page, ENT_QUOTES, 'UTF-8') . "'></script>";
    } else {
        echo "<script src='source/high.js'></script>";
    }
}

?>