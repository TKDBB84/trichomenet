<?php
if(!isset($_SESSION)) session_start();
include_once 'connection.php';
include_once 'Bcrypt.php';
if(isset($_POST['email']) && isset($_POST['pass'])){
   $email = $_POST['email'];
   $supplied_pass = $_POST['pass'];
   $bcrypt = new Bcrypt(15);
   $stmt_get_hash = $pdo_dbh->prepare("SELECT `user_id`,`password` FROM `users` WHERE `email` = :email LIMIT 1;");
   $stmt_get_hash->bindValue(':email',$email,PDO::PARAM_STR);
   $stmt_get_hash->execute();
   $row = $stmt_get_hash->fetch(PDO::FETCH_ASSOC);
   $hash = $row['password'];
   $user_id = $row['user_id'];
   $stmt_get_hash->closeCursor();
   if($bcrypt->verify($supplied_pass, $hash)){
      $_SESSION['user_id'] = $user_id;
      header('Location: ./addGenotypes.php');
   }else{
      $_SESSION['error_text'] = "Invalid Username & Password<br/>";
      header('Location: ./login.php');
   }
 } ?>