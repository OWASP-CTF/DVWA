<?php
if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
    define('DVWA_WEB_PAGE_TO_ROOT', '../../../');
}

// Get current user's ID and role with a prepared statement - this is the
// only trustworthy source of "who is asking".
$query = "SELECT user_id, role FROM users WHERE user = ? LIMIT 1";
$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
$current_user_id = 0;
$role = '';
if ($stmt) {
    $currentUser = dvwaCurrentUser();
    mysqli_stmt_bind_param($stmt, "s", $currentUser);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : array('user_id' => 0, 'role' => '');
    $current_user_id = intval($row['user_id']);
    $role = $row['role'];
    mysqli_stmt_close($stmt);
}

// Fixed access control: authorisation used to be decided by comparing the
// requested user_id against the client-supplied "user_id" COOKIE, which an
// attacker can set to any value. The decision is now based only on the
// server-resolved identity of the logged-in user (current_user_id above),
// which the client cannot influence.
$html = "";
if (isset($_REQUEST['action']) && isset($_REQUEST['user_id'])) {
    if (!is_string($_REQUEST['user_id']) || !preg_match('/^\d+$/', $_REQUEST['user_id'])) {
        $html .= "<p>Invalid user ID format. Please enter a number.</p>";
    } else {
        $id = intval($_REQUEST['user_id']);

        // Check if user exists first (prepared statement)
        $check_query = "SELECT user_id FROM users WHERE user_id = ? LIMIT 1";
        $check_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $check_query);
        $user_exists = false;
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "i", $id);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            $user_exists = (mysqli_stmt_num_rows($check_stmt) > 0);
            mysqli_stmt_close($check_stmt);
        }

        if (!$user_exists) {
            $html .= "<p>No user found with ID: {$id}</p>";
        } else if ($current_user_id > 0 && $id === $current_user_id) {
            // Access granted - viewing your own profile
            $query = "SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = ? LIMIT 1";
            $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $html .= "
                        <div class=\"profile-info\">
                            <h3>User Profile</h3>
                            <p>User ID: {$row['user_id']}</p>
                            <p>Name: {$row['first_name']} {$row['last_name']}</p>
                            <p>Avatar: {$row['avatar']}</p>
                        </div>";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $html .= "<p>Access denied. You can only view your own profile.</p>";
        }

        // Log access attempts
        try {
            // First check if the bac_log table exists
            $check_table = "SHOW TABLES LIKE 'bac_log'";
            $table_exists = mysqli_query($GLOBALS["___mysqli_ston"], $check_table);

            if ($table_exists && mysqli_num_rows($table_exists) == 0) {
                // Create the table if it doesn't exist
                $create_table = "CREATE TABLE IF NOT EXISTS bac_log (
                   id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id INT(6) NULL,
                    target_id INT(6) NULL,
                    ip_address VARCHAR(50) NULL,
                    action VARCHAR(50) NULL,
                    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                mysqli_query($GLOBALS["___mysqli_ston"], $create_table);
            }

            // Log the access attempt
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $target_id = $user_exists ? $id : 0; // Use 0 for non-existent users
            $log_query = "INSERT INTO bac_log (user_id, target_id, ip_address) VALUES (?, ?, ?)";
            $log_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $log_query);
            if ($log_stmt) {
                mysqli_stmt_bind_param($log_stmt, "iis", $current_user_id, $target_id, $ip);
                mysqli_stmt_execute($log_stmt);
                mysqli_stmt_close($log_stmt);
            }
        } catch (Exception $e) {
            // Silently fail if logging doesn't work
        }
    }
}

// Show current user's role for context (display only - not used for any
// access decision above)
$role = isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : 'regular_user';
$html .= "<div class='info-banner'>Current Role: {$role}</div>";

// Set initial role cookie if not exists
if (!isset($_COOKIE['user_role'])) {
    setcookie('user_role', 'regular_user', time() + 3600, '/');
}
?>
