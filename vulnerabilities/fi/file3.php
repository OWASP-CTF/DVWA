<?php

$current_user = htmlspecialchars( dvwaCurrentUser(), ENT_QUOTES, 'UTF-8' );
$remote_addr = htmlspecialchars( $_SERVER[ 'REMOTE_ADDR' ] ?? '', ENT_QUOTES, 'UTF-8' );
$user_agent = htmlspecialchars( $_SERVER[ 'HTTP_USER_AGENT' ] ?? '', ENT_QUOTES, 'UTF-8' );
$http_host = htmlspecialchars( $_SERVER[ 'HTTP_HOST' ] ?? '', ENT_QUOTES, 'UTF-8' );

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: File Inclusion</h1>
	<div class=\"vulnerable_code_area\">
		<h3>File 3</h3>
		<hr />
		Welcome back <em>{$current_user}</em><br />
		Your IP address is: <em>{$remote_addr}</em><br />";
if( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER )) {
	$forwarded_for = htmlspecialchars( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ], ENT_QUOTES, 'UTF-8' );
	$page[ 'body' ] .= "Forwarded for: <em>{$forwarded_for}";
	$page[ 'body' ] .= "</em><br />";
}
		$page[ 'body' ] .= "Your user-agent address is: <em>{$user_agent}</em><br />";
if( array_key_exists( 'HTTP_REFERER', $_SERVER )) {
		$referer = htmlspecialchars( $_SERVER[ 'HTTP_REFERER' ], ENT_QUOTES, 'UTF-8' );
		$page[ 'body' ] .= "You came from: <em>{$referer}</em><br />";
}
		$page[ 'body' ] .= "I'm hosted at: <em>{$http_host}</em><br /><br />
		[<em><a href=\"?page=include.php\">back</a></em>]
	</div>

	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://en.wikipedia.org/wiki/Remote_File_Inclusion', 'Wikipedia - File inclusion vulnerability' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-web-security-testing-guide/stable/4-Web_Application_Security_Testing/07-Input_Validation_Testing/11.1-Testing_for_Local_File_Inclusion', 'WSTG - Local File Inclusion' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-web-security-testing-guide/stable/4-Web_Application_Security_Testing/07-Input_Validation_Testing/11.2-Testing_for_Remote_File_Inclusion', 'WSTG - Remote File Inclusion' ) . "</li>
	</ul>
</div>\n";

?>
