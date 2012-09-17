<?php
if (!isset($_SESSION))
    session_start();
?>
<script type="text/javascript">
    function validateEmail(email) { 
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    } 
    
    function chkEmail(){
        var email = document.getElementById('email').value;
        if(validateEmail(email)){
            document.getElementById('email_error').innerHTML = "<img src='./pics/greenchk.png'/>";
        }else if(email === ''){
            document.getElementById('email_error').innerHTML = "";
        }else{
            document.getElementById('email_error').innerHTML = "<img src='./pics/redx.png'/>";
        }   
    }
    
    function chkPass(){
        var pass = document.getElementById('pass1').value;
        var pass2 = document.getElementById('pass2').value;
        if(pass === ''){
            document.getElementById('pass1_error').innerHTML = '';
        }else if(pass.length < 6){
            document.getElementById('pass1_error').innerHTML = "<img src='./pics/redx.png'/>";
        }else{
            document.getElementById('pass1_error').innerHTML = "<img src='./pics/greenchk.png'/>";
        }
        
        if(pass2 === ''){
            document.getElementById('pass2_error').innerHTML = '';
        }else if(pass2.length < 6){
            document.getElementById('pass2_error').innerHTML = "<img src='./pics/redx.png'/>";
        }
        
        if( (pass.length > 5 && pass2.length > 5) && (pass === pass2) ) {
            document.getElementById('pass1_error').innerHTML = "<img src='./pics/greenchk.png'/>";
            document.getElementById('pass2_error').innerHTML = "<img src='./pics/greenchk.png'/>";
        }
    }
</script>
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
                    Thank You For Registering
                </div>
                <div id="main_contents">
                    <div id="framed">
                        <form method="post" action="./mkuser.php">
                            <?php
                            if (isset($_SESSION['error_text'])) {
                                echo '<span style="color:red;">', $_SESSION['error_text'], '</span>';
                                unset($_SESSION['error_text']);
                            }
                            ?>
                            <table>
                                <tr>
                                    <td align="right">E-Mail:<br/>
                                        <span style="font-size: .7em;">this will act as your login</span></td>
                                    <td><input type="email" name="email" id="email" onchange="chkEmail();" onblur="chkEmail();"/></td>
                                    <td><div id="email_error"/></td>
                                </tr>
                                <tr>
                                    <td align="right">Name:</td>
                                    <td><input type="text" name="name"/></td>
                                </tr>
                                <tr>
                                    <td align="right">Organization:</td>
                                    <td><input type="text" name="org"/></td>
                                </tr>
                                <tr>
                                    <td align="right">Password:</td>
                                    <td><input type="password" name="password" onkeyup="chkPass();" onblur="chkPass();" id="pass1"/></td>
                                    <td><div id="pass1_error"/></td>
                                </tr>
                                <tr>
                                    <td align="right">Confirm:</td>
                                    <td><input type='password' name="password2" onkeyup="chkPass();" onblur="chkPass();" id="pass2"/></td>
                                    <td><div id="pass2_error"/></td>
                                </tr>
                                <tr>
                                    <td align="right" colspan="2">
                                        <span style="font-size: .7em;">password must be at least 6 characters</span>
                                    </td>
                                    <td/>
                                </tr>
                                <tr>
                                    <td/>
                                    <td align="right"><button type="submit">Register</button></td>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
            <br/><br/><span>Email Us At: <a href="admin@trichomenet.com">admin@TrichomeNet.com</a></span>
        </div>
    </body>


