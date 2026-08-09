<?php
if( isset( $_POST['Submit'] ) ) {
	$target = str_replace( array( '&&', ';' ), '', $_REQUEST['ip'] );
	$html .= '<pre>' . shell_exec( 'ping -c 4 ' . $target ) . '</pre>';
}
?>
