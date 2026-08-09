<?php
if( isset( $_GET[ 'Login' ] ) ) {
	checkToken( $_REQUEST['user_token'], $_SESSION['session_token'], 'index.php' );
	$user = mysqli_real_escape_string( $GLOBALS['___mysqli_ston'], stripslashes( $_GET['username'] ) );
	$pass = md5( mysqli_real_escape_string( $GLOBALS['___mysqli_ston'], stripslashes( $_GET['password'] ) ) );
	$query = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';";
	$result = mysqli_query( $GLOBALS['___mysqli_ston'], $query );
	if( $result && mysqli_num_rows( $result ) == 1 ) {
		$html .= "<p>Welcome to the password protected area {$user}</p>";
	} else {
		sleep( rand( 0, 3 ) );
		$html .= '<pre><br />Username and/or password incorrect.</pre>';
	}
}
generateSessionToken();
?>
