<?php

function validCookie($cookie,$pdo_dbh){
    //this function will validiate the cookie, and roll the last_crypt
    //if the user_id is valid; users who have had there cookie stolen,
    //or a logging in from a new IP will be force to re-loging
    $ret = false;
    if(isset($cookie)){
        if(isset($cookie['user_id'])){
            if(isset($cookie['creation'])){
                include_once 'connection.php';
                $user_id = $cookie['user_id'];
                $last_crypt = $cookie['last_crypt'];
                $stmt_chk_crypt = $pdo_dbh->prepare("SELECT 1 FROM users WHERE user_id = :user_id AND last_crypt = :last_crypt");
                $stmt_chk_crypt->bindValue(':user_id',$user_id,PDO::PARAM_INT);
                $stmt_chk_crypt->bindValue(':last_crypt',$last_crypt,PDO::PARAM_STR);
                $stmt_chk_crypt->execute();
                $result = $stmt_chk_crypt->fetch(PDO::FETCH_ASSOC);
                if($result !== false){
                    $ret = true;
                }
            }
        }
    }
    return $ret;
}

function doLogin($cookie,$pdo_dbh){
    $_SESSION['user_id'] = $cookie['user_id'];
    $user_id = $_COOKIE['user_id'];
    $new_crypt = uniqid('',true);
    $stmt_update_crypt = $pdo_dbh->prepare("UPDATE `users` SET `last_crypt` = :new_crypt WHERE `user_id`= :user_id");
    $stmt_update_crypt->bindValue(":new_crypt", $new_crypt, PDO::PARAM_STR);
    $stmt_update_crypt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt_update_crypt->execute();
    if(isset($_COOKIE['creation'])){
        setcookie("last_crypt",$new_crypt,$cookie['creation']);
    }
}
?>
