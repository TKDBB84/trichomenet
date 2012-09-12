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
                    <tr>
                        <!--<td><a href="#"> Add Genotypes </a></td>
                        <td><a href="#"> Add Leaves </a></td>
                        <td><a href="#"> Analyze </a></td>
                        <td><a href="#"> Log Out </a></td>-->
                    </tr>
                </table>
            </div>
        </div>
        <div style="height:100%; width: 100%; position: relative;">
            <div class="sidebar">
                <span>Thank you for your Interested in TrichomeNet</span>
                <br/>
                <span>If you have any problems with the software</span>
                <br/>
                <span>Please leave any issues at: <a href="https://github.com/TKDBB84/trichomenet">TrichomeNet On Github</a></span>
                <br/><br/>
            </div>
            <div class="contents">
                <div id="contents_header">
                    
                </div>
                <div id="main_contents">
                    <form method="post" action="chkUser.php">
                        Username: <input type="text" name="email"/>
                        <br/>
                        Password: <input type="password" name="pass"/>
                        <br/>
                        <button type="submit">submit</button>
                    </form>
                    <br/>
                    <br/>
                    Or: <a href="./register.php">Register A New User</a>
                </div>
            </div>
        </div>

    </body>
</html>
