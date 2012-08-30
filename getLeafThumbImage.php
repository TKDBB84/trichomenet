<?php
if(!isset($_GET['leaf_id'])) die("ERROR PASSING SHAPE ID");
include_once 'connection.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

$leaf_id = $_GET['leaf_id'];

$stmt_get_image = $pdo_dbh->prepare('SELECT `file_name` FROM leafs WHERE leaf_id = :leaf_id LIMIT 1;');
$stmt_get_image->bindValue(':leaf_id',$leaf_id,PDO::PARAM_INT);

$stmt_get_image->execute();
$result = $stmt_get_image->fetchAll(PDO::FETCH_ASSOC);
if(count($result) <= 0) die("COULD NOT FIND SHAPE");
echo "<img src='./pics/",$result[0][`file_name`],"_thumb.jpg'/>";
?>
