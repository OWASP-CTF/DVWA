<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );
dvwaDatabaseConnect();
$login_state = "";

if( isset( $_POST[ 'Login' ] ) ) {

	$user = $_POST[ 'username' ];
	$user = stripslashes( $user );
	$user = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $user);

	$pass = $_POST[ 'password' ];
	$pass = stripslashes( $pass );
	$pass = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $pass);

	$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT * FROM `users` WHERE user = ?" ) or die( '<pre>' . mysqli_error($GLOBALS["___mysqli_ston"]) . '.<br />Try <a href="setup.php">installing again</a>.</pre>' );
	mysqli_stmt_bind_param( $stmt, 's', $user );
	mysqli_stmt_execute( $stmt );
	$result = mysqli_stmt_get_result( $stmt );
	$row    = $result ? mysqli_fetch_assoc( $result ) : null;

	if( $row && dvwaVerifyPassword( $pass, $row[ 'password' ], $needsUpgrade ) ) {    // Login Successful...
		if( $needsUpgrade ) {
			$newHash = dvwaHashPassword( $pass );
			$upd = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE `users` SET password = ? WHERE user = ?" );
			mysqli_stmt_bind_param( $upd, 'ss', $newHash, $user );
			mysqli_stmt_execute( $upd );
		}
		$login_state = "<h3 class=\"loginSuccess\">Valid password for '{$user}'</h3>";
	}else{
		// Login failed
		$login_state = "<h3 class=\"loginFail\">Wrong password for '{$user}'</h3>";
	}

}
$messagesHtml = messagesPopAllToHtml();
$page = dvwaPageNewGrab();

$page[ 'title' ] .= "Test Credentials";
$page[ 'body' ] .= "
		<div class=\"body_padded\">
			<h1>Test Credentials</h1>
			<h2>Vulnerabilities/CSRF</h2>
			<div id=\"code\">
				<form action=\"" . DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/csrf/test_credentials.php\" method=\"post\">
					<fieldset>
						" . $login_state . "
						<label for=\"user\">Username</label><br /> <input type=\"text\" class=\"loginInput\" size=\"20\" name=\"username\"><br />
						<label for=\"pass\">Password</label><br /> <input type=\"password\" class=\"loginInput\" AUTOCOMPLETE=\"off\" size=\"20\" name=\"password\"><br />
						<p class=\"submit\"><input type=\"submit\" value=\"Login\" name=\"Login\"></p>
					</fieldset>
				</form>
				{$messagesHtml}
			</div>
		</div>\n";

dvwaSourceHtmlEcho( $page );

?>
