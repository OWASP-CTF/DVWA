<?php
/*
 * Nothing is computed in the browser any more.
 *
 * This level chained sha256 calls in high.js, and shipped the readable
 * version next to it as high_unobfuscated.js.
 *
 * The client controls every line of script it runs, so a value the client
 * derives can never prove anything to the server. index.php now issues a
 * one-shot token, holds it in the session and puts it straight into the hidden
 * field, so the browser only has to hand it back.
 */
?>
