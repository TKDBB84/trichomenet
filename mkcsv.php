<?php
include_once 'connection2.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

if(!isset($_POST['leaf_ids'])) die('ERROR FINDING LEAFS');
if(!isset($_POST['bin_details'])) die('ERROR FINDING LEAF DETAILS');
if(!isset($_POST['bin_step'])) die('ERROR FINDING BIN DETAILS');
if(!isset($_POST['nn_bin_details'])) die('ERROR FINDING NN LEAF DETAILS');
if(!isset($_POST['nn_bin_step'])) die('ERROR FINDING NN BIN DETAILS');

//die(var_dump(stripslashes($_GET['bin_details'])));

$bin_details = json_decode(stripslashes($_POST['bin_details']));
$bin_step = $_POST['bin_step'];


$nn_bin_details = json_decode(stripslashes($_POST['nn_bin_details']));
$nn_bin_step = $_POST['nn_bin_step'];


//die(var_dump($bin_details));
$output = '"Leaf Name","Num Trichomes"'.PHP_EOL;
$leaf_ids = explode(',',$_POST['leaf_ids']);
$leaf_names = array();

unset($leaf_id);
$stmt_get_leaf_details = $pdo_dbh->prepare('SELECT `leaf_name`,count(xCord) as cnt FROM leafs JOIN cords ON cords.fk_leaf_id = leaf_id WHERE leaf_id = :leaf_id');
$stmt_get_leaf_details->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);

foreach($leaf_ids as $leaf_id){
    $stmt_get_leaf_details->execute();
    $row = $stmt_get_leaf_details->fetch(PDO::FETCH_ASSOC);
    $leaf_names[$leaf_id] = $row['leaf_name'];
    $output .= '"'.$row['leaf_name'].'",'.$row['cnt'].PHP_EOL;
    $stmt_get_leaf_details->closeCursor();
}

$output .= '"Leaf Name","Bin","Num_Tricomes"'.PHP_EOL;
foreach($bin_details as $leaf_id => $bins){
    foreach($bins as $bin_num => $num_points){
        $bin_size = "<".(($bin_num + 1)*$bin_step);
        $output .= '"'.$leaf_names[$leaf_id].'","'.$bin_size.'",'.$num_points.PHP_EOL;
    }
}

$output .= '"Leaf Name","Next Neighbor Bin","Num_Tricomes"'.PHP_EOL;
foreach($nn_bin_details as $leaf_id => $bins){
    foreach($bins as $bin_num => $num_points){
        $bin_size = "<".(($bin_num + 1)*$nn_bin_step);
        $output .= '"'.$leaf_names[$leaf_id].'","'.$bin_size.'",'.$num_points.PHP_EOL;
    }
}

$file_name = 'lala';
while(!file_exists('/home/eglabdb/html5test/csvs/'.$file_name.'.csv')){
    $file_name = uniqid('tmp_');
    touch('/home/eglabdb/html5test/csvs/'.$file_name.'.csv');
    if(file_exists('/home/eglabdb/html5test/csvs/'.$file_name.'.csv')) break;
}

$fp = fopen('/home/eglabdb/html5test/csvs/'.$file_name.'.csv','w');
fwrite($fp,$output);
fclose($fp);

echo '<a href="./csvs/'.$file_name.'.csv">Download CSV</a>';
//echo $output;
?>
