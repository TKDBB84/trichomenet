<?php
if(!isset($_SESSION)) session_start();
$page = basename($_SERVER['PHP_SELF']);
echo '<td><a href="./addGenotypes.php" onClick="',($page == 'addGenotypes.php')?'return false;':'','"> Add Genotypes </a></td>',
     '<td><a href="./addLeafs.php" onClick="',($page == 'addLeafs.php')?'return false;':'','"> Add Leaves </a></td>',
     '<td><a href="./analyze3.php" onClick="',($page == 'analyze3.php')?'return false;':'','"> Analyze </a></td>',
     '<td><a href="./logout.php"> Log Out </a></td>';
?>
