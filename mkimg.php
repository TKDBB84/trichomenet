<?php
if(!isset($_SESSION)) session_start();
include_once 'connection.php';

$leaf_id = $_GET['leaf_id'];
$user_id = $_SESSION['user_id'];
$get_leaf_name_and_image = $pdo_dbh->prepare('SELECT leaf_name, file_name FROM `leafs` WHERE leaf_id = :leaf_id AND owner_id = :user_id;');
$get_leaf_name_and_image->bindParam(':user_id',$user_id,PDO::PARAM_INT);
$get_leaf_name_and_image->bindParam(':leaf_id',$leaf_id,PDO::PARAM_INT);
$get_leaf_name_and_image->execute() or die($get_leaf_name_and_image->queryString.'<br/><br/>'.var_dump($get_leaf_name_and_image->errorInfo()));
$row = $get_leaf_name_and_image->fetch(PDO::FETCH_ASSOC);
//die("./pics/$row[file_name].jpg");
// Picture_Path
$ini_settings = parse_ini_file('./settings.ini', true);
$uploaddir = $ini_settings['Fiji']['Picture_Path'] . '/';
$leaf_name = $row['leaf_name'];
$file = $uploaddir.$row['file_name' ].".jpg";
$get_leaf_name_and_image->closeCursor();
$fp = fopen($file, 'r');
header("Content-Type: image/jpeg");
header("Content-Length: " . filesize($file));
header("Content-Disposition: attachment; filename=$leaf_name.jpg");
fpassthru($fp);
fclose($fp);
?>