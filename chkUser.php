<?php
if(!isset($_SESSION)) session_start();
include_once 'connection.php';
include_once 'Bcrypt.php';
include_once 'chkcookie.php';

if(isset($_POST['email']) && isset($_POST['pass'])) {
    $email = strtolower($_POST['email']);
    $supplied_pass = $_POST['pass'];
    $bcrypt = new Bcrypt(15);
    $stmt_get_hash = $pdo_dbh->prepare("SELECT `user_id`,`password` FROM `users` WHERE `email` = :email LIMIT 1;");
    $stmt_get_hash->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt_get_hash->execute();
    $row = $stmt_get_hash->fetch(PDO::FETCH_ASSOC);
    $hash = $row['password'];
    $user_id = $row['user_id'];
    $stmt_get_hash->closeCursor();

    if($bcrypt->verify($supplied_pass, $hash)){
        $new_crypt = uniqid('', true);
        $stmt_update_crypt = $pdo_dbh->prepare("UPDATE `users` SET `last_crypt` = :new_crypt WHERE `user_id`= :user_id");
        $stmt_update_crypt->bindValue(":new_crypt", $new_crypt, PDO::PARAM_STR);
        $stmt_update_crypt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt_update_crypt->execute();
        if(isset($_POST['remember'])){
            setcookie("user_id", $user_id, time() + 3600 * 24 * 30);
            setcookie("last_crypt", $new_crypt, time() + 3600 * 24 * 30);
            setcookie("creation", time(), time() + 3600 * 24 * 30);
        }
        $_SESSION['user_id'] = $user_id;
        header('Location: ./addGenotypes.php');
    }else{
        $_SESSION['error_text'] = "Invalid Username & Password<br/>";
        header('Location: ./login.php');
    }
}elseif(validCookie($_COOKIE,$pdo_dbh)){
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $user_id = $_COOKIE['user_id'];
    $new_crypt = uniqid('',true);
    $stmt_update_crypt = $pdo_dbh->prepare("UPDATE `users` SET `last_crypt` = :new_crypt WHERE `user_id`= :user_id");
    $stmt_update_crypt->bindValue(":new_crypt", $new_crypt, PDO::PARAM_STR);
    $stmt_update_crypt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt_update_crypt->execute();
    if(isset($_COOKIE['creation'])){
        setcookie("last_crypt",$new_crypt,$_COOKIE['creation']+3600*24*30);
        header('Location: ./addGenotypes.php');
    }else{
        die('Unrecoverable Cookie Error,creation date does not exist, Please Contact Webmaster');
    }
}
?>