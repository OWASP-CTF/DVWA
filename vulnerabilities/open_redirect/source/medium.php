<?php

$targets = array( 1 => 'info.php?id=1', 2 => 'info.php?id=2' );
$redirect = filter_input( INPUT_GET, 'redirect', FILTER_VALIDATE_INT );

if ( $redirect !== false && isset( $targets[ $redirect ] ) ) {
	header( 'Location: ' . $targets[ $redirect ] );
	exit;
}

http_response_code (400);
?>
<p>Invalid redirect target.</p>
<?php
exit;
?>
