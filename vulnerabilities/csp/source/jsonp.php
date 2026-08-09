<?php
header('Content-Type: application/javascript; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
echo 'solveSum(' . json_encode(array('answer' => 15)) . ');';
?>
