<?php
if(!isset($_GET['leaf_id'])) die("ERROR PASSING SHAPE ID");
include_once 'connection.php';
$leaf_id = mysql_real_escape_string($_GET['leaf_id']);
$query = "SELECT `file_name` FROM leafs WHERE leaf_id = $leaf_id LIMIT 1;";
//die($query);
$result = mysql_query($query) or die("ERROR: INVALID SHAPE ID");
if(mysql_num_rows($result) <= 0) die("COULD NOT FIND SHAPE");
$row = mysql_fetch_assoc($result);
echo "<img src='./pics/",$row[shape_image],"_thumb.jpg'/>";
?>
