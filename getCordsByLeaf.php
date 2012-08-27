<?php
include_once 'connection2.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

if(!isset($_GET['leaf_id'])) die("No Leaf Selected");
$leaf_id = $_GET['leaf_id'];
$x_cords = array();
$y_cords = array();
$cord_types = array();

$stmt_get_tip = $pdo_dbh->prepare("SELECT tip_x,tip_y FROM leafs WHERE leaf_id = :leaf_id");
$stmt_get_tip->bindValue(':leaf_id', $leaf_id, PDO::PARAM_INT);
$stmt_get_tip->execute();

$row = $stmt_get_tip->fetch(PDO::FETCH_ASSOC);
$x_cords[] = $row['tip_x'];
$y_cords[] = $row['tip_y'];
$cord_types[] = 'tip';
unset($row);
$stmt_get_tip->closeCursor();


$stmt_get_cords = $pdo_dbh->prepare("SELECT xCord,yCord,cord_type FROM cords WHERE fk_leaf_id = :leaf_id");
$stmt_get_cords->bindValue(':leaf_id', $leaf_id, PDO::PARAM_INT);

while($row = $stmt_get_cords->fetch(PDO::FETCH_ASSOC)){
    $x_cords[] = $row['xCord'];
    $y_cords[] = $row['yCord'];
    $cord_types[] = $row['cord_type'];
}
$stmt_get_cords->closeCursor();

echo implode(',', $x_cords),
 '~',
 implode(',', $y_cords),
 '~',
 implode(',', $cord_types);
?>
