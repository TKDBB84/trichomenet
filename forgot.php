<?php
if(!isset($_SESSION)) session_start();
if(isset($_POST) && isset($_POST['email'])){
    $error = false;
    $error_text = '';
    include_once 'connection.php';
    include_once 'Bcrypt.php';
    $email = $_POST['email'];
    $stmt_get_email = $pdo_dbh->prepare('SELECT `email`,`user_id` FROM `users` WHERE `email`=:email');
    $stmt_get_email->bindValue(':email',$email,PDO::PARAM_STR);
    $stmt_get_email->execute();
    $result = $stmt_get_email->fetch(PDO::FETCH_ASSOC);
    if($result === false){
        $error = true;
        $error_text .= 'Email Address Not Found...<br/>';
    }else{
        $to = $result['email'];
        $user_id = $result['user_id'];
        $newPassword = generatePassword();
        $bcrypt = new Bcrypt(15);
        $hash = $bcrypt->hash($newPassword);
        if(!$bcrypt->verify($newPassword, $hash)){
            $error = true;
            $error_text .= 'Error Encrypting Password, Please Try Again...<br/>';
        }else{
            $stmt_update_password = $pdo_dbh->prepare("UPDATE `users` SET `password`=:hash WHERE `user_id` = :user_id");
            $stmt_update_password->bindValue(':hash',$hash,PDO::PARAM_STR);
            $stmt_update_password->bindValue(':user_id',$user_id,PDO::PARAM_INT);
            $exec_stmt = $stmt_update_password->execute();
            if(!$exec_stmt){
                $error = true;
                $error_text .= 'Error Updating New Password In Database, Please Try Again...<br/>';
            }
            $subject = "Your TrichomeNet Password Has Been Reset";
            $message = "Your Password Has been Reset... \n
                        The new Password to access your account is:\n\n
                        $newPassword";
            $headers = "From: " . "TrichomeNet";
            mail($to,$subject,$message,$headers);
        }
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <LINK href="./css/trichomenet.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css" media="screen">

        </style>
        <title>TrichomeNet</title>
    </head>

    <body>
        <div class="header">
            <div id="logo"></div>
                <div class="header" id="logo_text">
                    <a class="header" href="./index.php"><span>TRICHOME<span>NET</span></span></a>
                </div>

            <div class="linkblock">
                &nbsp;
            </div>
        </div>
        <div style="height:100%; width: 100%; position: relative;">
            <div class="sidebar">
                <span>
                      Thank you for your Interested in TrichomeNet. 
                      If you have any problems with the software, 
                      please leave any issues at: 
                      <a href="https://github.com/TKDBB84/trichomenet">
                        TrichomeNet On Github
                      </a>
                      <br/><br/>
                      Or Email Us At: <a href="mailto:admin@trichomenet.com">admin@TrichomeNet.com</a>
                </span>
                
            </div>
            <div class="contents">
                <div id="contents_header">
                    
                </div>
                <div id="main_contents">
                    <div id="framed">
                        <?php if(!isset($error)){ ?>
                        <div style="float: right; margin-right: 50px;">
                            <strong>Just Looking Around?</strong><br/>Login As A Guest Using:<br/>&nbsp;&nbsp;&nbsp;&nbsp; Login: guest<br/>&nbsp;&nbsp;&nbsp;&nbsp; Password: guest<br/>
                            This Will Let You View Our Sample Data
                        </div>
                        <form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
                            Login/Email:<br/>
                            <input type="text" name="email"/><br/>
                        <br/>
                        <button type="submit">E-Mail Me My Password!</button><br/>
                        </form>
                        <?php }elseif(isset($error) && $error === false){ ?>
                            Your Password Has Been Successfully Reset<br/>It Is Being E-Mailed To You
                        <?php }elseif(isset($error) && $error === true){ ?>
                            An Error Occured Attempting to Reset Your Password<br/>
                            <span style="color:red;"><?php echo $error_text ?></span>
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
            <br/><br/><span>Email Us At: <a href="mailto:admin@trichomenet.com">admin@TrichomeNet.com</a></span>
        </div>
    </body>
</html>





<?php

function generatePassword($length = 8) {
    $password = "";
    $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
    $maxlength = strlen($possible);
    if ($length > $maxlength) {
        $length = $maxlength;
    }
    $i = 0;
    while ($i < $length) {
        $char = substr($possible, mt_rand(0, $maxlength - 1), 1);
        if (!strstr($password, $char)) {
            $password .= $char;
            $i++;
        }
    }
    return $password;
}
?>