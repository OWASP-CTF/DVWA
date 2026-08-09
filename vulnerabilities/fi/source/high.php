<?php

// The page we wish to display

$allowed = array ('include.php', 'file1.php', 'file2.php', 'file3.php');
if (isset ($_GET['page']) && !in_array ($_GET['page'], $allowed, true)) {
	// This isn't the page we want!
	echo "ERROR: File not found!";
	exit;
}

?>
