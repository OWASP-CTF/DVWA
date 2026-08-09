<?php
if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
    define('DVWA_WEB_PAGE_TO_ROOT', '../../../');
}

// Get current user's ID and role, bound as a parameter rather than
// concatenated.
$current_username = dvwaCurrentUser();
$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT user_id, role FROM users WHERE user = ?");
mysqli_stmt_bind_param($stmt, 's', $current_username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : array('user_id' => 0, 'role' => '');
mysqli_stmt_close($stmt);
$current_user_id = intval($row['user_id']);
$role = $row['role'];

// Basic attempt at access control (but easily bypassed)
$html = "";
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    if (!preg_match('/^\d+$/', $_GET['user_id'])) {
        $html .= "<p>Invalid user ID format. Please enter a number.</p>";
    } else {
        $id = intval($_GET['user_id']);
        $user_exists = false;

        // Check if user exists first
        $check_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT user_id FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($check_stmt, 'i', $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $user_exists = ($check_result && mysqli_num_rows($check_result) > 0);
        mysqli_stmt_close($check_stmt);

        // A static, publicly-known token string proves nothing about who is
        // making the request. Ownership must be checked against the
        // server-side, authenticated user's own ID instead.
        if (!$user_exists) {
            $html .= "<p>No user found with ID: {$id}</p>";
        } else if ($id == $current_user_id) {
            $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $html .= "
                    <div class=\"profile-info\">
                        <h3>User Profile</h3>
                        <p>User ID: " . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "</p>
                        <p>Name: " . htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8') . "</p>
                        <p>Avatar: " . htmlspecialchars($row['avatar'], ENT_QUOTES, 'UTF-8') . "</p>
                    </div>";
            }
            mysqli_stmt_close($stmt);
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

            // Log the access attempt. The X-Forwarded-For header is
            // attacker-controlled and was previously concatenated straight
            // into this INSERT - bind it as a parameter instead.
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $target_id = $user_exists ? $id : 0; // Use 0 for non-existent users
            $log_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "INSERT INTO bac_log (user_id, target_id, ip_address) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($log_stmt, 'iis', $current_user_id, $target_id, $ip);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        } catch (Exception $e) {
            // Silently fail if logging doesn't work
        }
    }
}

// Show current user's role for context. This used to be read back from a
// 'user_role' cookie the client can set to whatever it likes (this lesson's
// own help text names that exact cookie as the medium-level bypass) - use
// the value looked up from the database above instead, so the banner can
// never be made to claim a role the account doesn't actually have.
$html .= "<div class='info-banner'>Current Role: " . htmlspecialchars($role, ENT_QUOTES, 'UTF-8') . "</div>";
?>
