<?php
if(!isset($_SESSION)) session_start();
?>

Please Login: <br/>
Username: <input type="text" name="username"/>
<br/>
Password: <input type="password" name="password"/>
<br/>
<br/>
Or: <a href="./register.php">Register A New User</a>