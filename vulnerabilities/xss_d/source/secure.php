<?php

$allowedLanguages = array('English', 'French', 'Spanish', 'German');
$selectedLanguage = (string) ($_GET['default'] ?? 'English');

if (!in_array($selectedLanguage, $allowedLanguages, true)) {
	$selectedLanguage = 'English';
}

?>
