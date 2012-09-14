<?php
if(!isset($_SESSION)) session_start();
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
                            <strong>Just Looking Around?</strong><br/>Login As A Guest Using:<br/>&nbsp;&nbsp;&nbsp;&nbsp; Username: guest<br/>&nbsp;&nbsp;&nbsp;&nbsp; Password: guest<br/>
                            This Will Let You View Our Sample Data
                        </div>
                        <form method="post" action="chkUser.php">
                            Username:<br/>
                            <input type="text" name="email"/><br/>
                            Password:<br/>
                            <input type="password" name="pass"/><br/>
                            <input type="checkbox" name="remember">Remember Me<br/>
                        <br/>
                        <button type="submit">Login</button><br/>
                        <a href="#">Forgot Password?</a>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
        </div>
    </body>
</html>
