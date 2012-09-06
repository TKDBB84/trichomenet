<?php
if(!isset($_SESSION)) session_start();

include_once 'connection.php';
$ini_settings = parse_ini_file('./settings.ini',true);
$uploaddir = $ini_settings['Fiji']['Picture_Path'].'/';

$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);
$leaf_id = $_GET['leaf_id'];

$stmt_get_image = $pdo_dbh->prepare('SELECT `file_name` FROM leafs WHERE leaf_id = :leaf_id LIMIT 1;');
$stmt_get_image->bindValue(':leaf_id', $leaf_id,PDO::PARAM_INT);
$stmt_get_image->execute();
$result = $stmt_get_image->fetch(PDO::FETCH_ASSOC);
$image_name = $result['file_name'];

$stmt_del_leaf = $pdo_dbh->prepare("DELETE FROM leafs WHERE `leaf_id` = :leaf_id AND `owner_id` = :user_id LIMIT 1;");
$stmt_del_cords = $pdo_dbh->prepare("DELETE FROM cords WHERE `fk_leaf_id` = :leaf_id");

$stmt_del_leaf->bindValue(':leaf_id', $leaf_id,PDO::PARAM_INT);
$stmt_del_leaf->bindValue(':user_id', $_SESSION['user_id'],PDO::PARAM_INT);
$stmt_del_leaf->execute() or die('0');
if($stmt_del_leaf->rowCount() != 1) die('0');

$stmt_del_cords->bindValue(':leaf_id', $leaf_id,PDO::PARAM_INT);
$stmt_del_cords->execute() or die('0');

unlink($uploaddir.$image_name.'_thumb.jpg') or die('0');
unlink($uploaddir.$image_name.'.jpg') or die('0');

die('1');
?>
