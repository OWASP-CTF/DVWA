<?php
if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
    define('DVWA_WEB_PAGE_TO_ROOT', '../../../');
}

// This level compared against a $_SESSION['user_id'] that was seeded once
// and never re-synced, so it kept the previous user's id across a change
// of account. The id is now resolved from the session username each time.

$html = "";

/*
 * Record an access attempt.
 *
 * The IP is taken from REMOTE_ADDR and validated. X-Forwarded-For used to be
 * concatenated into the INSERT unchecked, which was both a log injection
 * (CWE-117) and a second order SQL injection.
 */
function bacLogAccess($user_id, $target_id, $action)
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        $ip = 'unknown';
    }

    $user_id   = intval($user_id);
    $target_id = intval($target_id);

    $stmt = mysqli_prepare(
        $GLOBALS["___mysqli_ston"],
        "INSERT INTO bac_log (user_id, target_id, ip_address, action) VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        // Never swallow this silently: a security log that stops recording
        // without telling anyone is worse than no log at all.
        error_log("bac: unable to prepare audit insert: " . mysqli_error($GLOBALS["___mysqli_ston"]));
        return;
    }

    mysqli_stmt_bind_param($stmt, "iiss", $user_id, $target_id, $ip, $action);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("bac: audit insert failed: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
}

// Resolve who is asking from the server side session. A cookie, a request
// parameter or a hard coded token are all attacker controlled and cannot carry
// an authorisation decision.
$current_user_id   = 0;
$current_user_role = '';

$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT user_id, role FROM users WHERE user = ? LIMIT 1");
if ($stmt) {
    $current_user = dvwaCurrentUser();
    mysqli_stmt_bind_param($stmt, "s", $current_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && ($row = mysqli_fetch_assoc($result))) {
        $current_user_id   = intval($row['user_id']);
        $current_user_role = (string) $row['role'];
    }
    mysqli_stmt_close($stmt);
}

if (isset($_GET['action']) && isset($_GET['user_id'])) {
    if (!preg_match('/^\d+$/', $_GET['user_id'])) {
        $html .= "<p>Invalid user ID format. Please enter a number.</p>";
    } else {
        $id = intval($_GET['user_id']);

        // Authorisation decision: your own profile, and only your own. Made
        // before the row is read, not after.
        //
        // There is deliberately no admin exception here. impossible.php has the
        // same branch commented out: this module is about whether one user can
        // read another user's record, and "unless you are an admin" is exactly
        // the hole it is demonstrating.
        $can_access = ($id === $current_user_id);

        if (!$can_access) {
            $html .= "<p>Access denied. You can only view your own profile.</p>";
            bacLogAccess($current_user_id, $id, 'unauthorized_access');
        } else {
            $stmt = mysqli_prepare(
                $GLOBALS["___mysqli_ston"],
                "SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = ? LIMIT 1"
            );

            if (!$stmt) {
                error_log("bac: unable to prepare profile select: " . mysqli_error($GLOBALS["___mysqli_ston"]));
                $html .= "<p>An error occurred. Please try again later.</p>";
            } else {
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row    = ($result) ? mysqli_fetch_assoc($result) : false;
                mysqli_stmt_close($stmt);

                if (!$row) {
                    $html .= "<p>No user found with ID: " . intval($id) . "</p>";
                    bacLogAccess($current_user_id, $id, 'non_existent_user_access');
                } else {
                    $html .= "
                        <div class=\"profile-info\">
                            <h3>User Profile</h3>
                            <p>User ID: " . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "</p>
                            <p>Name: " . htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8') . " " .
                                         htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8') . "</p>
                            <p>Avatar: " . htmlspecialchars($row['avatar'], ENT_QUOTES, 'UTF-8') . "</p>
                        </div>";
                    bacLogAccess($current_user_id, $id, 'view_profile_success');
                }
            }
        }
    }
}

$html .= "<div class='info-banner'>Current Role: " . htmlspecialchars($current_user_role, ENT_QUOTES, 'UTF-8') . "</div>";
?>
