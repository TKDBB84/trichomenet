<?php
include_once 'connection.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

if(!isset($_GET['leaf_id'])) die("No Leaf Selected");
$leaf_ids = array();
if(strpos($_GET['leaf_id'],',') === false){
    $leaf_ids[0] = $_GET['leaf_id'];
}else{
    $leaf_ids = explode(',', $_GET['leaf_id']);
}

$stmt_get_leaf_details = $pdo_dbh->prepare('SELECT `leaf_name`,`file_name` FROM leafs WHERE leaf_id = :leaf_id');
$stmt_get_leaf_details->bindParam(':leaf_id', $leaf_id);
echo '<table border="2">';
foreach($leaf_ids as $leaf_id){
    $stmt_get_leaf_details->execute();
    $row = $stmt_get_leaf_details->fetch(PDO::FETCH_ASSOC);
    echo '<tr>',
            '<td>',$row['leaf_name'],'</td>',
            '<td><img src="./pics/',$row['file_name'],'_thumb.jpg"/></td>',
         '</tr>';
    $stmt_get_leaf_details->closeCursor();
}
echo '</table>';
?>