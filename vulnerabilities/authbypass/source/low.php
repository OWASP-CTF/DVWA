<?php

/*
The flaw at this level was that the only thing keeping a non-admin away from
the user manager was the menu entry being hidden - hiding a link is not an
access control.

The control is now enforced where the privileged data actually lives:
get_user_data.php refuses to list users and change_user_details.php refuses to
update them for anyone who is not the admin, at every security level. The page
itself holds no privileged content - it is a shell the browser fills from those
endpoints - so it is left to render and simply tells the caller it has nothing
for them.
*/

if (dvwaCurrentUser() !== 'admin') {
	$html .= '<p>Unauthorised - you do not have permission to manage users.</p>';
}

?>
