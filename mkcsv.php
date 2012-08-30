<?php
include_once 'connection.php';
$ini_settings = parse_ini_file('./settings.ini',true);
if(isset($ini_settings['Fiji'])){
    $output_Path = $ini_settings['Fiji']['CSV_Output_Dir'];
}else{
    die("YOU MUST SET YOUR DATABASE SETTINGS IN: ./settings.ini");
}
    

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
$leaf_ids = explode(',',$_POST['leaf_ids']);

$output = '';

unset($leaf_id);
$stmt_get_cords_by_leafid = $pdo_dbh->prepare('SELECT xCord,yCord,cord_type FROM cords WHERE fk_leaf_id = :leaf_id');
$stmt_get_cords_by_leafid->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);
$stmt_get_leaf_name_by_leafid = $pdo_dbh->prepare('SELECT `leaf_name` FROM leafs WHERE leaf_id = :leaf_id');
$stmt_get_leaf_name_by_leafid->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);


foreach($leaf_ids as $leaf_id){
    $stmt_get_leaf_name_by_leafid->execute();
    $row = $stmt_get_leaf_name_by_leafid->fetch(PDO::FETCH_ASSOC);
    $output .= '"Leaf Name: '.$row['leaf_name'].'"'.PHP_EOL.'"X Cords","Y Cords","Cord Type"'.PHP_EOL;
    $stmt_get_leaf_name_by_leafid->closeCursor();
    $stmt_get_cords_by_leafid->execute();
    while($row = $stmt_get_cords_by_leafid->fetch(PDO::FETCH_ASSOC)){
        $output .= $row['xCord'].','.$row['yCord'].',';
        if($row['cord_type'] == 'inner') $output .= '"Laminal"';
        elseif($row['cord_type'] == 'outter') $output .= '"Marginal"';
        elseif($row['cord_type'] == 'auto') $output .= '"Auto"';
        $output .= PHP_EOL;
    }
    $stmt_get_cords_by_leafid->closeCursor();
}

//die(var_dump($bin_details));
$output .= '"Leaf Name","Num Trichomes"'.PHP_EOL;
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
while(!file_exists($output_Path.'/'.$file_name.'.csv')){
    $file_name = uniqid('tmp_');
    touch($output_Path.'/'.$file_name.'.csv');
    if(file_exists($output_Path.'/'.$file_name.'.csv')) break;
}

$file = $output_Path.'/'.$file_name.'.csv';
$fp = fopen($file,'w');
fwrite($fp,$output);
fclose($fp);
?>
<iframe src="./dlCSV.php?file=<?php echo $file ?>" style="display:none;">
</iframe>
