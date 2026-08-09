<?php
if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
    define('DVWA_WEB_PAGE_TO_ROOT', '../../');
}

require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup(array('authenticated'));
dvwaDatabaseConnect();

$page = dvwaPageNewGrab();
$page['title'] = 'Broken Access Control Logs' . $page['title_separator'] . $page['title'];
$page['page_id'] = 'bac';

// This page is the actual protected resource: the access log for the BAC
// module. It is linked from the site-wide Security page for every logged
// in user, so the link itself is not an access control - authorisation has
// to be enforced here, server-side, regardless of how the request reaches
// this URL (direct navigation, forced browsing, bookmarked link, etc), and
// regardless of the current DVWA security level.
//
// The caller's role is resolved strictly from the database, keyed off the
// server-side authenticated username (dvwaCurrentUser()). It is never taken
// from a request parameter or cookie, so it cannot be spoofed by the client.
$currentUser = dvwaCurrentUser();
$role = 'user';
$roleStmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT role FROM users WHERE user = ? LIMIT 1");
if ($roleStmt) {
    mysqli_stmt_bind_param($roleStmt, "s", $currentUser);
    mysqli_stmt_execute($roleStmt);
    $roleResult = mysqli_stmt_get_result($roleStmt);
    if ($roleResult && mysqli_num_rows($roleResult) > 0) {
        $roleRow = mysqli_fetch_assoc($roleResult);
        if (!empty($roleRow['role'])) {
            $role = $roleRow['role'];
        }
    }
    mysqli_stmt_close($roleStmt);
}

$html = "<div class=\"body_padded\">";
$html .= "<h1>Broken Access Control Logs</h1>";

if ($role !== 'admin') {
    http_response_code(403);
    $html .= "<p>Access denied. You do not have permission to view this page.</p>";
} else {
    $html .= "<div class='log-container'>";
    $html .= "<h3>Access Log</h3>";

    $log_query = "SELECT l.id, l.user_id, l.target_id, l.ip_address, l.timestamp,
                     u1.user as accessor_user, u2.user as target_user
                     FROM bac_log l
                     LEFT JOIN users u1 ON l.user_id = u1.user_id
                     LEFT JOIN users u2 ON l.target_id = u2.user_id
                     ORDER BY l.timestamp DESC LIMIT 50";
    $log_result = mysqli_query($GLOBALS["___mysqli_ston"], $log_query);

    if ($log_result && mysqli_num_rows($log_result) > 0) {
        $html .= "<table class='log-table'>";
        $html .= "<tr><th>ID</th><th>Accessor</th><th>Target</th><th>IP Address</th><th>Timestamp</th></tr>";

        while ($log = mysqli_fetch_assoc($log_result)) {
            // Some of this is stored data, and some (the IP address) comes
            // from a caller-controlled header, so everything is encoded for
            // the HTML context it is written into.
            $target_user = $log['target_user'] ? htmlspecialchars($log['target_user'], ENT_QUOTES, 'UTF-8') : 'Non-existent User (ID: ' . intval($log['target_id']) . ')';

            $html .= "<tr>";
            $html .= "<td>" . intval($log['id']) . "</td>";
            $html .= "<td>" . htmlspecialchars($log['accessor_user'], ENT_QUOTES, 'UTF-8') . " (ID: " . intval($log['user_id']) . ")</td>";
            $html .= "<td>{$target_user}</td>";
            $html .= "<td>" . htmlspecialchars($log['ip_address'], ENT_QUOTES, 'UTF-8') . "</td>";
            $html .= "<td>" . htmlspecialchars($log['timestamp'], ENT_QUOTES, 'UTF-8') . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
    } else {
        $html .= "<p>No access logs found.</p>";
    }

    $html .= "</div>";
}

$html .= "<p><a href='index.php'>Back</a></p>";
$html .= "</div>";

$page['body'] .= "
<style>
    .log-container {
        max-height: 400px;
        overflow-y: auto;
        margin-bottom: 20px;
    }
    .log-table {
        width: 100%;
        border-collapse: collapse;
    }
    .log-table th, .log-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .log-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    .log-table th {
        padding-top: 12px;
        padding-bottom: 12px;
        background-color: #4a4a4a;
        color: white;
    }
</style>";

$page['body'] .= $html;

dvwaHtmlEcho($page);
?>
