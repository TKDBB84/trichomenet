<?php
$file = $_GET['file'];
$fp = fopen($file, 'r');
header("Content-Type: application/csv");
header("Content-Length: " . filesize($file));
header("Content-Disposition: attachment; filename=saved_output.csv");
fpassthru($fp);
?>
