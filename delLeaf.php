<?php
if(!isset($_SESSION)) session_start();
include_once 'connection.php';


//   deleteGenotype Will Delete All Leaves 
//   and All Cords Associated with that Genotype!


if(isset($_GET['leaf_id'])){
    die(deleteLeaf($_GET['leaf_id'],$pdo_dbh));
}elseif(isset($_GET['genotype_id'])){
    die(deleteGenotype($_GET['genotype_id'],$pdo_dbh));
}else die('0');




function deleteLeaf($leaf_id,$pdo_dbh) {
    //include_once 'connection.php';
    $error = false;
    $error_text = '';
    $ini_settings = parse_ini_file('./settings.ini', true);
    $uploaddir = $ini_settings['Fiji']['Picture_Path'] . '/';
    //$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;", $DBUsername, $DBPassword);
    $stmt_get_image = $pdo_dbh->prepare('SELECT `file_name` FROM leafs WHERE leaf_id = :leaf_id LIMIT 1;');
    $stmt_get_image->bindValue(':leaf_id', $leaf_id, PDO::PARAM_INT);
    $stmt_get_image->execute();
    $result = $stmt_get_image->fetch(PDO::FETCH_ASSOC);
    $image_name = $result['file_name'];
    $stmt_del_leaf = $pdo_dbh->prepare("DELETE FROM leafs WHERE `leaf_id` = :leaf_id AND `owner_id` = :user_id LIMIT 1;");
    $stmt_del_cords = $pdo_dbh->prepare("DELETE FROM cords WHERE `fk_leaf_id` = :leaf_id");
    $stmt_del_leaf->bindValue(':leaf_id', $leaf_id, PDO::PARAM_INT);
    $stmt_del_leaf->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    if ($stmt_del_leaf->execute()) {
        if ($stmt_del_leaf->rowCount() === 1) {
            $stmt_del_cords->bindValue(':leaf_id', $leaf_id, PDO::PARAM_INT);
            if ($stmt_del_cords->execute()) {
                if (unlink($uploaddir . $image_name . '_thumb.jpg')) {
                    if (!(unlink($uploaddir . $image_name . '.jpg')))
                        $error = true;
                }else
                    $error = true;
            }else
                $error = true;
        }else
            $error = true;
    }else
        $error = true;

    return $error ? '0' : '1';
}

function deleteGenotype($geno_id,$pdo_dbh){
    //include_once 'connection.php';
    $error = false;
    $error_text = '';
    //$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;", $DBUsername, $DBPassword);
    
    $stmt_get_all_leaves = $pdo_dbh->prepare("SELECT leaf_id FROM leafs WHERE fk_genotype_id = :geno_id AND `owner_id` = :user_id;");
    $stmt_get_all_leaves->bindValue(':geno_id', $geno_id, PDO::PARAM_INT);
    $stmt_get_all_leaves->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    if ($stmt_get_all_leaves->execute()) {
        while($row = $stmt_get_all_leaves->fetch(PDO::FETCH_ASSOC)){
            deleteLeaf($row['leaf_id']);
        }
        if (!$error) {
            $stmt_del_geno = $pdo_dbh->prepare("DELETE FROM genotypes WHERE `genotype_id` = :geno_id AND `owner_id` = :user_id LIMIT 1;");
            $stmt_del_geno->bindValue(':geno_id', $geno_id, PDO::PARAM_INT);
            $stmt_del_geno->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            if ($stmt_del_geno->execute()) {
                if ($stmt_del_geno->rowCount() !== 1){
                    $error = true;
                    $error_text .= '\nMore Then 1 genotype found';
                }
            }else{
                $error = true;
                $error_text = "\nError Executing Delete Query";
            }
        }
    }else{
        $error = true;
        $error_text = "\nError Deleting Leaf";
    }
    return $error?$error_text:'1';
}
?>
