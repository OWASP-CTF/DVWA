<?php
if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
    define('DVWA_WEB_PAGE_TO_ROOT', '../../../');
}

// Get current user's ID
$current_user = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], dvwaCurrentUser());
$query = "SELECT user_id, role FROM users WHERE user = '" . $current_user . "';";
$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
$row = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : array('user_id' => 0, 'role' => '');
$current_user_id = intval($row['user_id']);
$role = $row['role'];

// Access control is decided server side, from the authenticated user held in
// the session. The user_id cookie is client controlled and is never trusted.
$html = "";
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    if (!preg_match('/^\d+$/', $_GET['user_id'])) {
        $html .= "<p>Invalid user ID format. Please enter a number.</p>";
    } else {
        $id = intval($_GET['user_id']);

        // Check if user exists first
        $check_query = "SELECT user_id FROM users WHERE user_id = $id";
        $check_result = mysqli_query($GLOBALS["___mysqli_ston"], $check_query);
        $user_exists = ($check_result && mysqli_num_rows($check_result) > 0);

        if (!$user_exists) {
            $html .= "<p>No user found with ID: {$id}</p>";
        } else if ($id !== $current_user_id) {
            // Deny by default, a user may only read their own profile.
            $html .= "<p>Access denied. You can only view your own profile.</p>";
        } else {
            // Access granted
            $query = "SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = $id;";
            $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);

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
            $log_query = "INSERT INTO bac_log (user_id, target_id, ip_address) VALUES
                        (?, ?, ?)";
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

// Show current user's role for context. The role is read from the database
// record of the authenticated user, never from the client supplied cookie.
$role = ($role === null || $role === '') ? 'regular_user' : $role;
$html .= "<div class='info-banner'>Current Role: " . htmlspecialchars($role, ENT_QUOTES, 'UTF-8') . "</div>";

// Set initial role cookie if not exists (display only, never trusted)
if (!isset($_COOKIE['user_role'])) {
    setcookie('user_role', $role, time() + 3600, '/');
}
?>
