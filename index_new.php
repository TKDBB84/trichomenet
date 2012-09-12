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
            <div class="header" id="logo"></div>
            <div class="header" id="logo_text">
                <a class="header" href="#"><span>TRICHOME<span>NET</span></span></a>
                <br/>
                
            </div>
    
            <div class="linkblock">
                <table id="link_table">
                    <tr><!--
                        <td><a href="#"> Add Genotypes </a></td>
                        <td><a href="#"> Add Leaves </a></td>
                        <td><a href="#"> Analyze </a></td>
                        <td><a href="#"> Log Out </a></td>-->
                    </tr>
                </table>
            </div>
        </div>
        <div style="height:100%; width: 100%; position: relative;">
            <div class="sidebar">
                <span>Please <a href="./login.php">Login</a><br/> Or <a href="./register.php">Register</a></span>
                <br/><br/>
            </div>
            <div class="contents">
                <div id="contents_header">
                    Welcome to TrichomeNet!
                </div>
                <div id="main_contents">
                    <p>
                    TrichomeNet is an online resource to help analyze trichome development patterns in Arabidopsis.  However it may be useful
                    for many other plants as well. more text blah blah marcelo can write an introduction.
                    </p>
                    
                    <!--<canvas style="height: 960px; width: 1280px; background:url(./img_0e5145924d9c945f931c3821b5518246.jpg) no-repeat center center;"/>-->
                </div>
            </div>
        </div>
        
    </body>
</html>
