<?php
if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
    define('DVWA_WEB_PAGE_TO_ROOT', '../../../');
}

// Get current user's ID -- resolved from the authenticated session, via a
// prepared statement.
$current_user_id = 0;
$role = '';

$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT user_id, role FROM users WHERE user = ? LIMIT 1");
if ($stmt) {
    $current_user = dvwaCurrentUser();
    mysqli_stmt_bind_param($stmt, "s", $current_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : false;
    mysqli_stmt_close($stmt);

    if ($row) {
        $current_user_id = intval($row['user_id']);
        $role = $row['role'];
    }
}

$html = "";
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    if (!preg_match('/^\d+$/', $_GET['user_id'])) {
        $html .= "<p>Invalid user ID format. Please enter a number.</p>";
    } else {
        $id = intval($_GET['user_id']);

        // Check if user exists first
        $user_exists = false;
        $check_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT user_id FROM users WHERE user_id = ? LIMIT 1");
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "i", $id);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            $user_exists = (mysqli_stmt_num_rows($check_stmt) > 0);
            mysqli_stmt_close($check_stmt);
        }

        if (!$user_exists) {
            $html .= "<p>No user found with ID: " . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . "</p>";
        } else {
            // The old check compared the requested id against a "user_id"
            // cookie, which the requester writes themselves -- so any profile
            // could be read just by setting the cookie to match. Authorisation
            // is now decided against the id the session actually belongs to.
            if ($id === $current_user_id) {
                $profile_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = ? LIMIT 1");

                if ($profile_stmt) {
                    mysqli_stmt_bind_param($profile_stmt, "i", $id);
                    mysqli_stmt_execute($profile_stmt);
                    $profile_result = mysqli_stmt_get_result($profile_stmt);
                    $profile = $profile_result ? mysqli_fetch_assoc($profile_result) : false;
                    mysqli_stmt_close($profile_stmt);

                    if ($profile) {
                        $html .= "
                            <div class=\"profile-info\">
                                <h3>User Profile</h3>
                                <p>User ID: " . htmlspecialchars($profile['user_id'], ENT_QUOTES, 'UTF-8') . "</p>
                                <p>Name: " . htmlspecialchars($profile['first_name'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($profile['last_name'], ENT_QUOTES, 'UTF-8') . "</p>
                                <p>Avatar: " . htmlspecialchars($profile['avatar'], ENT_QUOTES, 'UTF-8') . "</p>
                            </div>";
                    }
                }
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

            // X-Forwarded-For is a request header, so it went straight into
            // the INSERT as attacker-controlled SQL. Bind it instead.
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $target_id = $user_exists ? $id : 0; // Use 0 for non-existent users

            $log_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "INSERT INTO bac_log (user_id, target_id, ip_address) VALUES (?, ?, ?)");
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

// Show the role this session actually has, not one the client asserted in a
// cookie.
$html .= "<div class='info-banner'>Current Role: " . htmlspecialchars($role !== '' ? $role : 'regular_user', ENT_QUOTES, 'UTF-8') . "</div>";
?>
