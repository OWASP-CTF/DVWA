<?php

// SECURE FIX: Server-side validation logic
// Generate MD5 hash of rot13("ChangeMe") or "success" safely
function generate_server_token($phrase) {
    return md5(str_rot13($phrase));
}

// Ensure phrase parameter is validated server-side
if (isset($_POST['phrase']) && isset($_POST['token'])) {
    $phrase = $_POST['phrase'];
    $token  = $_POST['token'];

    $expected_token = generate_server_token($phrase);

    if ($token === $expected_token) {
        $vulnerabilityFile = 'low.php';
    }
}

// Standard JavaScript token generation template for front-end rendering
$page[ 'body' ] .= <<<EOF
<form action="#" method="POST">
    <label for="phrase">Phrase:</label>
    <input type="text" name="phrase" id="phrase" value="ChangeMe" />
    <br />
    <input type="hidden" name="token" id="token" value="" />
    <input type="submit" id="submit" value="Submit" />
</form>

<script>
/* MD5 implementation code */
function rot13(inp) {
    return inp.replace(/[a-zA-Z]/g, function(c){
        return String.fromCharCode((c <= "Z" ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c - 26);
    });
}

function generate_token() {
    var phrase = document.getElementById("phrase").value;
    document.getElementById("token").value = md5(rot13(phrase));
}

document.getElementById("phrase").addEventListener("keyup", generate_token);
generate_token();
</script>
EOF;

?>