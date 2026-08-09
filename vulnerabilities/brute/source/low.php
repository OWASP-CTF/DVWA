<?php
if( isset( $_GET[ 'Login' ] ) ) {
	$user = $_GET[ 'username' ];
	$pass = md5( $_GET[ 'password' ] );
	$query = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';";
	$result = mysqli_query( $GLOBALS['___mysqli_ston'], $query );
	if( $result && mysqli_num_rows( $result ) == 1 ) {
		$html .= "<p>Welcome to the password protected area {$user}</p>";
	} else {
		$html .= '<pre><br />Username and/or password incorrect.</pre>';
	}
}
?>
