<?php
if(!isset($_SESSION)) session_start();
if(!isset($_GET['genotype_id']))
    die('0');
else{
    include_once 'connection.php';
    $update_last_genotype = $pdo_dbh->prepare("UPDATE `users` SET last_active_genotype = :genotype WHERE user_id = :user_id");
    $update_last_genotype->bindValue(':genotype',$_GET['genotype_id'],PDO::PARAM_INT);
    $update_last_genotype->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);        
    $result = $update_last_genotype->execute();
    if($result)
        $_SESSION['active_geno'] = $_GET['genotype_id'];
    else
        die('0');
    die('1');
}
?>
