<?php
if(!isset($_SESSION)) session_start();
$page = basename($_SERVER['PHP_SELF']);
echo '<td><a href="./addGenotypes.php" onClick="',($page == 'addGenotypes.php')?'return false;':'','">',($page == 'addGenotypes.php')?'<span>':'',' Add Genotypes ',($page == 'addGenotypes.php')?'</span>':'','</a></td>',
     '<td><a href="./addLeafs.php" onClick="',($page == 'addLeafs.php')?'return false;':'','">',($page == 'addLeafs.php')?'<span>':'',' Add Leaves ',($page == 'addLeafs.php')?'</span>':'','</a></td>',
     '<td><a href="./analyze3.php" onClick="',($page == 'analyze3.php')?'return false;':'','">',($page == 'analyze3.php')?'<span>':'',' Analyze ',($page == 'analyze3.php')?'</span>':'','</a></td>',
     '<td><a href="./logout.php"> Log Out </a></td>';
?>

