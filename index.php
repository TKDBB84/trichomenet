<?php 
if(!isset($_SESSION))    session_start();


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
                <span>Please <a href="./login.php">Login</a><br/> Or <a href="./register.php">Register</a></span>
            </div>
            <div class="contents">
                <div id="contents_header">
                    Welcome to TRICHOMENET!
                </div>
                <div id="main_contents">
                    <p>
                    TRICHOMENET is an trichome pattern analysis tool designed to work in conjuction with polarized light microscopy images of cleared leaves. The software can mark and perform trichome positional analyses including density heat maps and trichome distance distributions.
                    </p>
                    <p>
                    For more detailed descriptions and protocols, see our publication (Details of paper will be added after peer review).
                    </p>
                    <p>
                        To setup a private local server, the repository and instructions are available through Github at <a href="https://github.com/TKDBB84/trichomenet">https://github.com/TKDBB84/trichomenet</a>
                    </p>
                    <p align="center"><br/>
                     <img src="./pics/home.png"/>
                    </p>
                </div>
            </div>            
        </div>
       <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
            <br/><br/><span>Email Us At: <a href="mailto:admin@trichomenet.com">admin@TrichomeNet.com</a></span>
       </div>
    </body>
</html>
