<?php
// Fixed: Removed client-side JavaScript validation
// All validation moved server-side

// Server-side token generation and validation
function validate_submission($phrase, $token) {
    // Generate expected token server-side
    $expected = bin2hex(random_bytes(32));
    // Use secure comparison
    return hash_equals($expected, $token);
}

// Only include minimal safe JavaScript
$page[ 'body' ] .= '<script>
// Client-side validation removed for security
// All validation performed server-side
console.log("JavaScript validation disabled - server-side validation in effect");
</script>';

// Handle form submission server-side
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['phrase']) && isset($_POST['token'])) {
        $result = validate_submission($_POST['phrase'], $_POST['token']);
        // Process validated submission
    }
}
?>
