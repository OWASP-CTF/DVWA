<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );
dvwaDatabaseConnect();

/*
 * Access log viewer for the Broken Access Control module.
 *
 * This file used to be zero bytes: the module wrote an audit trail that nobody
 * could ever read, which is most of the way to having no audit trail at all
 * (A09:2025 Security Logging & Alerting Failures).
 *
 * The log names who looked at whom, so it is admin only.
 */
if( dvwaCurrentUser() != 'admin' ) {
	dvwaSecurityLog( 'bac.log_viewer.denied' );
	http_response_code( 403 );
	print 'Unauthorised';
	exit;
}

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Broken Access Control :: Access Log' . $page[ 'title_separator' ] . $page[ 'title' ];
$page[ 'page_id' ] = 'bac';

$rows = '';

$query = "SELECT l.id, l.user_id, l.target_id, l.ip_address, l.action, l.timestamp,
                 u1.user AS accessor_user, u2.user AS target_user
          FROM bac_log l
          LEFT JOIN users u1 ON l.user_id = u1.user_id
          LEFT JOIN users u2 ON l.target_id = u2.user_id
          ORDER BY l.timestamp DESC
          LIMIT 200";

$result = mysqli_query( $GLOBALS["___mysqli_ston"], $query );

if( !$result ) {
	error_log( 'bac/log_viewer: ' . mysqli_error( $GLOBALS["___mysqli_ston"] ) );
	$rows = "<tr><td colspan='6'>The access log is not available.</td></tr>";
}
elseif( mysqli_num_rows( $result ) == 0 ) {
	$rows = "<tr><td colspan='6'>No access log entries.</td></tr>";
}
else {
	while( $log = mysqli_fetch_assoc( $result ) ) {
		// Everything here came from a request at some point, so every field is
		// encoded on the way out.
		$target = $log[ 'target_user' ] !== null
			? $log[ 'target_user' ]
			: 'unknown user (id ' . intval( $log[ 'target_id' ] ) . ')';

		$rows .= "<tr>"
			. "<td>" . intval( $log[ 'id' ] ) . "</td>"
			. "<td>" . htmlspecialchars( (string) $log[ 'accessor_user' ], ENT_QUOTES, 'UTF-8' ) . " (id " . intval( $log[ 'user_id' ] ) . ")</td>"
			. "<td>" . htmlspecialchars( (string) $target, ENT_QUOTES, 'UTF-8' ) . "</td>"
			. "<td>" . htmlspecialchars( (string) $log[ 'ip_address' ], ENT_QUOTES, 'UTF-8' ) . "</td>"
			. "<td>" . htmlspecialchars( (string) $log[ 'action' ], ENT_QUOTES, 'UTF-8' ) . "</td>"
			. "<td>" . htmlspecialchars( (string) $log[ 'timestamp' ], ENT_QUOTES, 'UTF-8' ) . "</td>"
			. "</tr>\n";
	}
}

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Broken Access Control :: Access Log</h1>

	<p>The 200 most recent profile access attempts.</p>

	<table class=\"log-table\">
		<tr>
			<th>ID</th><th>Accessor</th><th>Target</th><th>IP address</th><th>Action</th><th>Timestamp</th>
		</tr>
		{$rows}
	</table>

	<p><a href='./'>Back to the module</a></p>
</div>\n";

dvwaHtmlEcho( $page );

?>
