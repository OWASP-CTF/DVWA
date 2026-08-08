<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );
dvwaDatabaseConnect();
$login_state = "";

if( isset( $_POST[ 'Login' ] ) ) {
	$user = stripslashes( $_POST[ 'username' ] );
	$pass = stripslashes( $_POST[ 'password' ] );

	// Bound parameter for the lookup, and the password is verified in PHP so
	// this page works with the salted hashes the rest of the application now
	// stores.
	$data = $db->prepare( 'SELECT password FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	$safe_user = htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' );

	if( $row && dvwaPasswordVerify( $pass, $row[ 'password' ] ) ) {
		$login_state = "<h3 class=\"loginSuccess\">Valid password for '{$safe_user}'</h3>";
	} else {
		$login_state = "<h3 class=\"loginFail\">Wrong password for '{$safe_user}'</h3>";
	}

	dvwaSecurityLog( 'csrf.test_credentials', array( 'target' => $user ) );
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
