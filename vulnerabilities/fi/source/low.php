<?php

// The page we wish to display
$allowed = array('include.php', 'file1.php', 'file2.php');
$file = $_GET['page'] ?? '';
if (!in_array($file, $allowed, true)) {
	$file = 'include.php';
}

?>
