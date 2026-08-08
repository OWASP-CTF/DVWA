<?php
if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
    define('DVWA_WEB_PAGE_TO_ROOT', '../../../');
}

// Get current user's ID with prepared statement
$query = "SELECT user_id FROM users WHERE user = ? LIMIT 1";
$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
$current_user = dvwaCurrentUser();
mysqli_stmt_bind_param($stmt, "s", $current_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$current_user_id = ($result && mysqli_num_rows($result) > 0) ? intval(mysqli_fetch_assoc($result)['user_id']) : 0;
mysqli_stmt_close($stmt);

// Access control is decided from the session's user, never from a request token
$html = "";
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    if (!preg_match('/^\d+$/', $_GET['user_id'])) {
        $html .= "<p>Invalid user ID format. Please enter a number.</p>";
    } else {
        $id = intval($_GET['user_id']);
        $user_exists = false;

        // Check if user exists first
        $check_query = "SELECT user_id FROM users WHERE user_id = ? LIMIT 1";
        $check_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        $user_exists = (mysqli_stmt_num_rows($check_stmt) > 0);
        mysqli_stmt_close($check_stmt);

        if (!$user_exists) {
            $html .= "<p>No user found with ID: {$id}</p>";
        } else {
            if ($id === $current_user_id) {
                $query = "SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = ? LIMIT 1";
                $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $html .= "
                        <div class=\"profile-info\">
                            <h3>User Profile</h3>
                            <p>User ID: " . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "</p>
                            <p>Name: " . htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8') . " " .
                        htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8') . "</p>
                            <p>Avatar: " . htmlspecialchars($row['avatar'], ENT_QUOTES, 'UTF-8') . "</p>
                        </div>";
                }
                mysqli_stmt_close($stmt);
            } else {
                $html .= "<p>Access denied. You can only view your own profile.</p>";
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
            
            // Log the access attempt - only log numeric target_id
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $target_id = $user_exists ? $id : 0; // Use 0 for non-existent users

            $log_query = "INSERT INTO bac_log (user_id, target_id, ip_address) VALUES (?, ?, ?)";
            $log_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $log_query);
            mysqli_stmt_bind_param($log_stmt, "iis", $current_user_id, $target_id, $ip);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        } catch (Exception $e) {
            // Silently fail if logging doesn't work
        }
    }
}
?>