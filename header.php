<?php
if(!isset($_SESSION)) session_start();
include_once 'connection.php';
$user_name = "Anonymous";
if(isset($_SESSION['user_id'])){
    $stmt_get_username = $pdo_dbh->prepare("SELECT `name` FROM `users` WHERE `user_id` = :user_id");
    $stmt_get_username->bindValue(':user_id',$_SESSION['user_id'],PDO::PARAM_INT);
    $stmt_get_username->execute();
    $result = $stmt_get_username->fetch(PDO::FETCH_ASSOC);
    $user_name = $result['name'];
}
    
?>
  <span>Welcome, <?php echo $user_name; ?> </span>
<table border="1">
    
    <tr>
        <td><a href="./addLeafs.php" onClick='sessionStorage.clear();'>Add/Edit Leafs</a></td>
        <td><a href="./admin.php" onClick='sessionStorage.clear();'>Add GenoTypes</a></td>
        <td><a href="./analyze3.php" onClick='sessionStorage.clear();'>Analyze</a></td>
    </tr>
</table>
<hr/>