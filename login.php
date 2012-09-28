<?php
if(!isset($_SESSION)) session_start();
include_once 'chkcookie.php';
include_once 'connection.php';

if(isset($_SESSION['user_id'])){
    header('Location: ./addGenotypes.php');
}else if(isset($_COOKIE['creation'])){
    if(validCookie($_COOKIE,$pdo_dbh)){
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        header('Location: ./chkUser.php');
    }else{
        $cookie_keys = array_keys($_COOKIE);
        if(!empty($cookie_keys))
            foreach($cookie_keys as $keyis)
                setcookie($keyis,'',time() - 3600);
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
        <title>TRICHOMENET</title>
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
                      Thank you for your Interested in TRICHOMENET. 
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
                        <?php
                            if (isset($_SESSION['error_text'])) {
                                echo '<span style="color:red;">', $_SESSION['error_text'], '</span>';
                                unset($_SESSION['error_text']);
                            }
                            ?>
                        <br/>
                        Don't Have An Account? <a href="./register.php">Register For Free</a><br/>
                        <br/>
                        <div style="float: right; margin-right: 50px;">
                            <strong>Just Looking Around?</strong><br/>Login As A Guest Using:<br/>&nbsp;&nbsp;&nbsp;&nbsp; Login: guest<br/>&nbsp;&nbsp;&nbsp;&nbsp; Password: guest<br/>
                            This Will Let You View Our Sample Data
                        </div>
                        <form method="post" action="chkUser.php">
                            Login (email):<br/>
                            <input type="text" name="email"/><br/>
                            Password:<br/>
                            <input type="password" name="pass"/><br/>
                            <input type="checkbox" name="remember">Remember Me<br/>
                        <br/>
                        <button type="submit">Login</button><br/>
                        <a href="forgot.php">Forgot Password?</a>
                        </form>
                        
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
