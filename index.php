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
                <span>Please <a href="./login.php">Login</a><br/> Or <a href="./register.php">Register</a></span>
            </div>
            <div class="contents">
                <div id="contents_header">
                    Welcome to TrichomeNet!
                </div>
                <div id="main_contents">
                    <p>
                    Trichomenet is an trichome pattern analysis tool designed to work in conjuction with polarized light microscopy images of cleared leaves. The software can detect and analyze trichome positions including density heat map and next neighbor analyses.
                    </p>
                    <p>
                    For more detailed descriptions and protocols, see our publication in The Plant Journal (Details of paper will be added after peer review).
                    </p>
                    <!--<canvas style="height: 960px; width: 1280px; background:url(./img_0e5145924d9c945f931c3821b5518246.jpg) no-repeat center center;"/>-->
                </div>
            </div>            
        </div>
       <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
            <br/><br/><span>Email Us At: <a href="mailto:admin@trichomenet.com">admin@TrichomeNet.com</a></span>
       </div>
    </body>
</html>
