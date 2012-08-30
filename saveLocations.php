<?php
include_once 'connection.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

$raw_Xdata = substr($_POST['Xdata'],0,-1);
$Xdata = explode(",",$raw_Xdata);
$raw_Ydata = substr($_POST['Ydata'],0,-1);
$Ydata = explode(",",$raw_Ydata);
$raw_Typedata = substr($_POST['Typedata'],0,-1);
$Typedata = explode(",",$raw_Typedata);
$num_types = count($Typedata);
for($i = 0 ; $i <$num_types ; $i++){
    if($Typedata[$i]=='tip'){
       $tip_x = $Xdata[$i];
       $tip_y = $Ydata[$i];
       unset($Xdata[$i],$Ydata[$i],$Typedata[$i]);
       $Xdata = array_values($Xdata);
       $Ydata = array_values($Ydata);
       $Typedata = array_values($Typedata);
    }
}
$leaf_id = $_POST['leaf_id'];

$stmt_insert_tip = $pdo_dbh->prepare('UPDATE leafs SET `tip_x` = :tip_x , `tip_y` = :tip_y WHERE `leaf_id` = :leaf_id');
//     ^^giggity 
$stmt_insert_tip->execute(array(':tip_x'=>$tip_x,':tip_y'=>$tip_y,':leaf_id'=>$leaf_id)) or die($stmt_insert_tip->queryString.'lala<br/><br/>'.var_dump($stmt_insert_tip->errorInfo()));

$num_of_points = count($Xdata);
if($num_of_points != count($Ydata) || $num_of_points != count($Typedata)) die("Transmition Error!");

$stmt_delete_cords = $pdo_dbh->prepare('DELETE FROM `cords` WHERE fk_leaf_id = :leaf_id');
$stmt_delete_cords->execute(array(':leaf_id'=>$leaf_id)) or die($stmt_delete_cords->queryString.'lala<br/><br/>'.var_dump($stmt_delete_cords->errorInfo()));


$stmt_insert_cord = $pdo_dbh->prepare('INSERT IGNORE INTO `cords` (`xCord`,`yCord`,`cord_type`,`fk_leaf_id`) VALUES (:xCord,:yCord,:cord_type,:leaf_id)');
$stmt_insert_cord->bindParam(':xCord',$safe_Xdata,PDO::PARAM_INT);
$stmt_insert_cord->bindParam(':yCord',$safe_Ydata,PDO::PARAM_INT);
$stmt_insert_cord->bindParam(':cord_type',$safe_Typedata,PDO::PARAM_INT);
$stmt_insert_cord->bindValue(':leaf_id',$leaf_id,PDO::PARAM_INT);

for($i = 0 ; $i < $num_of_points ; $i++ ){
    $safe_Xdata = $Xdata[$i];
    $safe_Ydata = $Ydata[$i];
    $safe_Typedata = $Typedata[$i];
    $stmt_insert_cord->execute() or die($stmt_insert_cord->queryString.'lala<br/><br/>'.var_dump($stmt_insert_cord->errorInfo()));
}
echo "Saved!";
?>
