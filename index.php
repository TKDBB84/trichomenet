<?php
if(!isset($_SESSION)) session_start();
if(isset($_SESSION['error_text'])){
    echo '<span style="color:red;">',$_SESSION['error_text'],'</span>';
    unset($_SESSION['error_text']);
}
?>


Please Login: <br/>
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