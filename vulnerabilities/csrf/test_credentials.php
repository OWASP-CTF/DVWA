<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );
dvwaDatabaseConnect();
$login_state = "";

if( isset( $_POST[ 'Login' ] ) ) {

	$user = $_POST[ 'username' ];

	$pass = $_POST[ 'password' ];
	$pass = md5( $pass );

	$data = $db->prepare( 'SELECT user FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$user_html = htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' );

	if( $data->rowCount() == 1 ) {    // Login Successful...
		$login_state = "<h3 class=\"loginSuccess\">Valid password for '{$user_html}'</h3>";
	}else{
		// Login failed
		$login_state = "<h3 class=\"loginFail\">Wrong password for '{$user_html}'</h3>";
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
