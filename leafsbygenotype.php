<?php
if(!isset($_SESSION)) session_start();
if(!isset($_GET['genotype_id'])) die('No Genotype ID Provided');
include_once 'connection.php';
$user_id = $_SESSION['user_id'];
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

$genotype = $_GET['genotype_id'];

$stmt_get_genotypes = $pdo_dbh->prepare('SELECT genotype FROM genotypes WHERE genotype_id = :genotype');
$stmt_get_genotypes->bindValue(':genotype', $genotype,PDO::PARAM_INT);
$stmt_get_genotypes->execute();
$result = $stmt_get_genotypes->fetchAll(PDO::FETCH_ASSOC);
if(count($result) > 0)
    $genotype_name = $result[0]['genotype'];
else
    $genotype_name = "Currently There Are No Genotypes";

echo '<header><b>',$genotype_name,'</b></header>';

$stmt_get_leafs_by_genotype = $pdo_dbh->prepare('SELECT leaf_id,leaf_name,file_name FROM leafs WHERE fk_genotype_id = :genotype AND owner_id = :user_id');
$stmt_get_leafs_by_genotype->bindValue(':genotype', $genotype,PDO::PARAM_INT);
$stmt_get_leafs_by_genotype->bindValue(':user_id', $user_id,PDO::PARAM_INT);

$stmt_get_num_tricomes = $pdo_dbh->prepare('Select COUNT(xCord) as cnt FROM cords WHERE fk_leaf_id = :leaf_id AND cord_type = :type');
$stmt_get_num_tricomes->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);

$stmt_get_leafs_by_genotype->execute();
$result = $stmt_get_leafs_by_genotype->fetchAll(PDO::FETCH_ASSOC);
echo '<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />';
echo '<input type="hidden" name="genotype_id" value="',$genotype,'"/>';
echo '<table border="1">',
        '<tr>',
            '<th>Leaf Name</td>',
            '<th>Image</td>',
            '<th>Number of<br/>Marked Tricombs</th>',
            //'<th/>',
        '</tr>';

if(count($result) > 0){
    foreach($result as $row){
        $leaf_id = $row['leaf_id'];
        
        $stmt_get_num_tricomes->bindValue(':type', 'inner', PDO::PARAM_STR);
        $stmt_get_num_tricomes->execute();
        $row2 = $stmt_get_num_tricomes->fetch(PDO::FETCH_ASSOC);
        $inner = $row2['cnt'];
        $stmt_get_num_tricomes->closeCursor();
        
        $stmt_get_num_tricomes->bindValue(':type', 'outter', PDO::PARAM_STR);
        $stmt_get_num_tricomes->execute();
        $row2 = $stmt_get_num_tricomes->fetch(PDO::FETCH_ASSOC);
        $outer = $row2['cnt'];
        $stmt_get_num_tricomes->closeCursor();
        
        $stmt_get_num_tricomes->bindValue(':type', 'auto', PDO::PARAM_STR);
        $stmt_get_num_tricomes->execute();
        $row2 = $stmt_get_num_tricomes->fetch(PDO::FETCH_ASSOC);
        $auto = $row2['cnt'];
        $stmt_get_num_tricomes->closeCursor();
        
        echo '<tr>',
                '<td>',$row['leaf_name'],'</td>',
                '<td align="center"><img src="./pics/',$row['file_name'],'_thumb.jpg"/></td>',
                '<td> Marginal: ',$outer,'<br/>Laminal: ',$inner,'<br/>Auto: ',$auto,'<br/></td>',
                '<td><a href="./findtricomes.php?leaf_id=',$row['leaf_id'],'">Edit</a></td>',
             '</tr>';
        
    }
}
echo '<tr>',
            '<td><input type="text" name="new_leaf_name"/></td>',
            '<td><input type="file" name="new_leaf_file" /></td>',
            '<td><button type="submit" name="add_new_leaf">Add New</button></td>',
    '</tr>',
 '</table>','<em>pictures MUST BE 5 MB or smaller</em>';
?>
