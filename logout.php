<?php
if(!isset($_SESSION))    session_start();
foreach($_SESSION as $id => &$item){
    $item = '';
    unset($_SESSION[$id]);
}
session_unset();
session_destroy();
header('Location: ./index.php');
?>
