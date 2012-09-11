<?php
if(!isset($_SESSION)) session_start();
include_once 'connection.php';
include_once 'stndev.php';



//~~~~~~~~~~~~~~~~~~~~~~STORE SETTINGS TO SESSION~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$_SESSION['show_bar_graph'] = $_POST['show_bar_graph'];
$_SESSION['bar_range'] = $_POST['bar_range'];
$_SESSION['num_boxes_x'] = $_POST['num_boxes_x'];
$_SESSION['num_boxes_y'] = $_POST['num_boxes_y'];
$_SESSION['edge'] = isset($_POST['edge'])?$_POST['edge']:0;
$_SESSION['count_outer'] = $_POST['count_outer'];
$_SESSION['show_values'] = $_POST['show_values'];
$_SESSION['graph_bin_size'] = $_POST['graph_bin_size'];
$_SESSION['outline'] = $_POST['outline'];
$_SESSION['tricomes'] = $_POST['tricomes'];
$_SESSION['nn_bar_range'] = $_POST['nn_bar_range'];
$_SESSION['nn_graph_bin_size'] = $_POST['nn_graph_bin_size'];
$_SESSION['current_genotype'] = $_POST['genotype_id'];
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~






//~~~~~~~~~~~~~~~~~~~~GET HEATMAP SETTINGS FROM INI~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $ini_settings = parse_ini_file('./settings.ini',true);
    
    $LEAF_COLORS = $ini_settings['HeatMap']['Leaf_Outline_Colors'];
    $LEAF_COLOR_NAMES = $ini_settings['HeatMap']['Color_Name'];
    
    $HEAT_MAP_COLORS = $ini_settings['HeatMap']['HeatMap_Colors'];
    $temp_array = $ini_settings['HeatMap']['HeatMap_MaxValue'];
    array_unshift($temp_array, 0);
    $HEAT_MAP_RANGES = $temp_array;
    if(count($HEAT_MAP_COLORS) !== count($HEAT_MAP_RANGES) ) die("HEATMAP COLOR SETUP MISS-MATCH!");
    if(count($LEAF_COLORS) !== count($LEAF_COLOR_NAMES) ) die("LEAF COLOR SETUP MISS-MATCH!");
    $NUM_COLORS = count($HEAT_MAP_COLORS);
    $NUM_LEAF_COLORS = count($LEAF_COLORS);
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    
    
    
    
//~~~~~~~~~~~~~~~~~~~~~~~~~GET LIST OF LEAFS SELECTED~~~~~~~~~~~~~~~~~~~~~~~~~~~
$leaf_ids = array();
$table_name = uniqid("tbl_",false);
foreach($_POST['all_leaf_ids'] as $value){
        $leaf_ids[] = (int)$value;
}
$all_ids = implode(",", $leaf_ids);
$_SESSION['all_ids'] = $leaf_ids;
$number_of_leafs = count($leaf_ids);
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    
    



    
//~~~~~~~~~~~~~~~~~~~~~~~~SET THE SETTINGS~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if(isset($_POST['additional_boxes'])){
    $additional_boxes = $_POST['additional_boxes'];
}else
    $additional_boxes = 0;

$SHOW_BIN_GRAPH = TRUE;
if(isset($_POST['show_bar_graph']))
    if( !$_POST['show_bar_graph'] == 1 && !$_POST['show_bar_graph'] == "1")
        $SHOW_BIN_GRAPH = FALSE;

    
    //num_boxes
if(isset($_POST['num_boxes_x'])){
    $num_boxes_x = $_POST['num_boxes_x'];
}else
    $num_boxes_x = 16;

if(isset($_POST['num_boxes_y'])){
    $num_boxes_y = $_POST['num_boxes_y'];
}else
    $num_boxes_y = 16;   
    
$SHOW_LEAF_EDGE = TRUE; 
if(isset($_POST['edge']) && ($_POST['edge'] == 0 || $_POST['edge'] == "0"))
        $SHOW_LEAF_EDGE = FALSE;

if(isset($_POST['count_outer'])){
    if($_POST['count_outer'] == 1 || $_POST['count_outer'] == "1"){
        $COUNT_OUTER = TRUE;
        $SHOW_LEAF_EDGE = FALSE;
    }else{
        $COUNT_OUTER = FALSE;
    }
}else{
    $COUNT_OUTER = FALSE;
}

$SHOW_VALUES = FALSE;
if(isset($_POST['show_values']) && ($_POST['show_values'] == 1 || $_POST['show_values'] == "1"))
        $SHOW_VALUES = TRUE;

if(isset($_POST['bar_range']))
    $bar_range = $_POST['bar_range'];
else
    $bar_range = 2000;

    //BIN SIZES FOR DISTANCES
    $BIN_SIZES = array();
if(isset($_POST['graph_bin_size'])){
    $incrm = $_POST['graph_bin_size'];
    for($i = 0 ; $i < $bar_range ; $i+=$incrm)
        $BIN_SIZES[] = $i;
}else
    $BIN_SIZES = array(0,100,200,300,400,500,600,700,800,900,1000,1100,1200,1300,
                        1400,1500,1600,1700,1800,1900,2000);


if(isset($_POST['nn_bar_range']))
    $nn_bar_range = $_POST['nn_bar_range'];
else
    $nn_bar_range = 2000;

    //BIN SIZES FOR DISTANCES
    $NN_BIN_SIZES = array();
if(isset($_POST['nn_graph_bin_size'])){
    $nn_incrm = $_POST['nn_graph_bin_size'];
    for($i = 0 ; $i < $nn_bar_range ; $i+=$nn_incrm)
        $NN_BIN_SIZES[] = $i;
}else
    $NN_BIN_SIZES = array(0,10,20,30,40,50,60,70,80,90,100,110,120,130,
                        140,150,160,170,180,190,200);


if(isset($_POST['outline'])){
    if($_POST['outline'] == 1 || $_POST['outline'] == "1")
        $SHOW_BOX_OUTLINES = TRUE;
    else
        $SHOW_BOX_OUTLINES = FALSE;
}else
    $SHOW_BOX_OUTLINES = FALSE;



if(isset($_POST['tricomes'])){
    if($_POST['tricomes'] == 0 || $_POST['tricomes'] == "0")
        $SHOW_TRICOMBS = FALSE;    
    else
        $SHOW_TRICOMBS = TRUE;
}else
    $SHOW_TRICOMBS = TRUE;

if(!isset($_POST['outline_leaf_ids']) && $SHOW_LEAF_EDGE){
    $outline_leaf_ids = $leaf_ids;
}else{
    if($SHOW_LEAF_EDGE)
        $outline_leaf_ids = $_POST['outline_leaf_ids'];
}
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~




//~~~~~~~~~~~~~~~~~~~~~~~~~Get Max Size Of Images~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $sql_image = $pdo_dbh->prepare("SELECT file_name FROM `leafs` WHERE leaf_id = :leaf_id");
    $sql_image->bindParam(':leaf_id',$leaf_id,PDO::PARAM_INT);
    $tmp_height = -1;
    $tmp_width = -1;
    foreach($leaf_ids as $leaf_id){
        $sql_image->execute();
        $result = $sql_image->fetch(PDO::FETCH_ASSOC);
        $file_name = $result['file_name'];
        $sql_image->closeCursor();
        $filepath = './pics/'.$file_name.'.jpg';
        list($width, $height, $type, $attr) = getimagesize($filepath);
        if($width > $tmp_width) $tmp_width = $width;
        if($height > $tmp_height) $tmp_height = $height;
        unset($width,$height);
    }
    $image_x = $tmp_width;
    $image_y = $tmp_height;
    unset($tmp_width,$tmp_height);
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


    
//~~~~~~~~~~~~~~~~~~~~~~~~~CREATE TEMP TABLE OF CORDS~~~~~~~~~~~~~~~~~~~~~~~~~~~
$sql_drop_temp_table = $pdo_dbh->prepare('DROP TEMPORARY TABLE IF EXISTS `:temp_table`');
$sql_drop_temp_table->bindParam(':temp_table', $table_name, PDO::PARAM_STR);
$sql_drop_temp_table->execute() or die($sql_drop_temp_table->queryString.'lala<br/><br/>'.var_dump($sql_drop_temp_table->errorInfo()));
$sql_copy_cords_to_temp = $pdo_dbh->prepare('CREATE TEMPORARY TABLE `:temp_table` LIKE `cords`');
$sql_copy_cords_to_temp->bindParam(':temp_table', $table_name, PDO::PARAM_STR);
$sql_copy_cords_to_temp->execute() or die($sql_copy_cords_to_temp->queryString.'lala<br/><br/>'.var_dump($sql_copy_cords_to_temp->errorInfo()));
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~




//~~~~~~~~~~~~~~SHIFT ALL POINTS SO TIPS ARE ON 0,0 (IN TEMP TABLE)~~~~~~~~~~~~~
$sql_select_tip_from_leafid = $pdo_dbh->prepare('SELECT tip_x,tip_y FROM leafs WHERE leaf_id = :leaf_id');

$sql_shift_points_in_temp_table_for_leaf_id = $pdo_dbh->prepare('INSERT INTO `:temp_tale` (xCord,yCord,fk_leaf_id,cord_type) SELECT
              (xCord-:tip_x) as xCord,(yCord-:tip_y) as yCord,fk_leaf_id,cord_type
               FROM cords WHERE fk_leaf_id = :leaf_id');
$sql_shift_points_in_temp_table_for_leaf_id->bindParam(':temp_tale',$table_name,PDO::PARAM_STR);
$sql_shift_points_in_temp_table_for_leaf_id->bindParam(':tip_x',$tip_x,PDO::PARAM_INT);
$sql_shift_points_in_temp_table_for_leaf_id->bindParam(':tip_y',$tip_y,PDO::PARAM_INT);
$sql_shift_points_in_temp_table_for_leaf_id->bindParam(':leaf_id',$leaf_id,PDO::PARAM_INT);
    
foreach($leaf_ids as $leaf_id){
    $sql_select_tip_from_leafid->bindValue(':leaf_id',$leaf_id,PDO::PARAM_INT);
    $sql_select_tip_from_leafid->execute() or die($sql_select_tip_from_leafid->queryString.'lala<br/><br/>'.var_dump($sql_select_tip_from_leafid->errorInfo()));
    $row = $sql_select_tip_from_leafid->fetch(PDO::FETCH_ASSOC);
    $tip_x = $row['tip_x'];
    $tip_y = $row['tip_y'];
    
    if(is_null($tip_x) || is_null($tip_y)) die('one of the leafs has no defined tip!');
    $sql_select_tip_from_leafid->closeCursor();
    
    $sql_shift_points_in_temp_table_for_leaf_id->execute() or die($sql_shift_points_in_temp_table_for_leaf_id->queryString.'lala<br/><br/>'.var_dump($sql_shift_points_in_temp_table_for_leaf_id->errorInfo()));
}
unset($tip_x,$tip_y);
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~



//~~~~~~~~~~~CREATE 2ND COPY OF CORDS TO JOIN 1ST COPY WITH~~~~~~~~~~~~~~~~~~~~~
$super_temp_table = uniqid("tmp_tbl", false);
$sql_copy_to_temp_table_from_table = $pdo_dbh->prepare('CREATE TEMPORARY TABLE `:to_table` (SELECT * FROM `:from_table`)');
$sql_copy_to_temp_table_from_table->bindParam(':to_table', $super_temp_table, PDO::PARAM_STR);
$sql_copy_to_temp_table_from_table->bindParam(':from_table', $table_name, PDO::PARAM_STR);
$sql_copy_to_temp_table_from_table->execute() or die($sql_copy_to_temp_table_from_table->queryString.'lala<br/><br/>'.var_dump($sql_copy_to_temp_table_from_table->errorInfo()));
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~




//~~~~~~~~~JOIN BOTH TEMP COPIES OF POINTS, TO CALCULATE DISTANCES~~~~~~~~~~~~~~
$temp_dist_table = uniqid("tmp_dist_tbl", false);

$temp_string = 'CREATE TEMPORARY TABLE `:dist_table` (SELECT c1.xCord AS x1,c1.yCord AS y1,c1.fk_leaf_id as leaf_id,
          sqrt(pow((c2.xCord - c1.xCord),2)+pow((c2.yCord - c1.yCord),2)) as distance
          FROM `:table1` as c1 JOIN `:table2` as c2 ON c1.fk_leaf_id = c2.fk_leaf_id
            WHERE (c1.xCord != c2.xCord OR c1.yCord != c2.yCord)';
$temp_string .= (!$COUNT_OUTER)?" AND (c1.cord_type = 'inner' OR c1.cord_type = 'auto') AND (c2.cord_type = 'inner' OR c2.cord_type = 'auto')":'';
$temp_string .= ')';

$sql_cross_join_both_temp_tables = $pdo_dbh->prepare($temp_string);
$sql_cross_join_both_temp_tables->bindParam(':dist_table', $temp_dist_table, PDO::PARAM_STR);
$sql_cross_join_both_temp_tables->bindParam(':table1', $table_name, PDO::PARAM_STR);
$sql_cross_join_both_temp_tables->bindParam(':table2', $super_temp_table, PDO::PARAM_STR);
$sql_cross_join_both_temp_tables->execute() or die($sql_cross_join_both_temp_tables->queryString.'lala<br/><br/>'.var_dump($sql_cross_join_both_temp_tables->errorInfo()));

$sql_drop_temp_table->bindParam(':temp_table', $super_temp_table, PDO::PARAM_STR );
$sql_drop_temp_table->execute() or die($sql_drop_temp_table->queryString.'lala<br/><br/>'.var_dump($sql_drop_temp_table->errorInfo()));

$sql_get_distances = $pdo_dbh->prepare("SELECT distance,leaf_id FROM `:dist_table`");
$sql_get_distances->bindParam(':dist_table', $temp_dist_table, PDO::PARAM_STR);
$sql_get_distances->execute()  or die($sql_get_distances->queryString.'lala<br/><br/>'.var_dump($sql_get_distances->errorInfo()));

$num_dists = array();
$all_dist = array();
$bins_per_leaf = array();
$Bins = array();


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~





//~~~~~~~~~~~~~~~~~~FIND AVERAGE DISTANCE AND BIN SIZES~~~~~~~~~~~~~~~~~~~~~~~~~
    while($row = $sql_get_distances->fetch(PDO::FETCH_ASSOC)){
        if( !isset($bins_per_leaf[$row['leaf_id']]) ) $bins_per_leaf[$row['leaf_id']] = array();
        $distance = $row['distance'];
        $NUM_OF_BINS = count($BIN_SIZES);
        $k = 0;
        
        for( ; $k < $NUM_OF_BINS-1 ; $k++ ){
            if( $distance >= $BIN_SIZES[$k] && $distance < $BIN_SIZES[$k+1] ){
                if(isset($bins_per_leaf[$row['leaf_id']][$k])){
                    $bins_per_leaf[$row['leaf_id']][$k]++;
                    
                }else{
                    $bins_per_leaf[$row['leaf_id']][$k] = 1;
                    
                }
            }
        }
        if($distance > $BIN_SIZES[$k]){
            if(isset($bins_per_leaf[$row['leaf_id']][$k])){
                $bins_per_leaf[$row['leaf_id']][$k]++;
            }else{
                $bins_per_leaf[$row['leaf_id']][$k] = 1;
                
            }
        }
        if(!isset($all_dist[$row['leaf_id']])) $all_dist[$row['leaf_id']] = 0;
        $all_dist[$row['leaf_id']] += $distance;
        if(!isset($num_dists[$row['leaf_id']])) $num_dists[$row['leaf_id']] = 0;
        $num_dists[$row['leaf_id']]++;
    }
$sql_cross_join_both_temp_tables->closeCursor();
$all_leaf_avg = 0;
foreach($leaf_ids as $leaf_id){
    $all_leaf_avg += $all_dist[$leaf_id] / $num_dists[$leaf_id];
    
}


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~






//~~~~~~~~~~~~~~~~~~GET NEXT NEIGHBOR DISTANCES AND BIN THEM~~~~~~~~~~~~~~~~~~~~
$next_neighbor_distances_bins = array();
$all_next_neighbor_dist = array();
$num_next_neighbor_dists = array();
$sql_get_nnd_from_dist_table_by_leaf_id = $pdo_dbh->prepare('SELECT min(distance) as dist FROM `:dist_table` WHERE leaf_id = :leaf_id group by x1,y1');
$sql_get_nnd_from_dist_table_by_leaf_id->bindParam(':dist_table',$temp_dist_table,PDO::PARAM_STR);
$sql_get_nnd_from_dist_table_by_leaf_id->bindParam(':leaf_id',$leaf_id,PDO::PARAM_INT);
foreach($leaf_ids as $leaf_id){
    $sql_get_nnd_from_dist_table_by_leaf_id->execute() or die($sql_get_nnd_from_dist_table_by_leaf_id->queryString.'lala<br/><br/>'.var_dump($sql_get_nnd_from_dist_table_by_leaf_id->errorInfo()));
    while($row = $sql_get_nnd_from_dist_table_by_leaf_id->fetch(PDO::FETCH_ASSOC)){
    if( !isset($next_neighbor_distances_bins[$leaf_id]) ) $next_neighbor_distances_bins[$leaf_id] = array();
        $distance = $row['dist'];
        $NN_NUM_OF_BINS = count($NN_BIN_SIZES);
        $k = 0;
        
        for( ; $k < $NN_NUM_OF_BINS-1 ; $k++ ){
            if( $distance >= $NN_BIN_SIZES[$k] && $distance < $NN_BIN_SIZES[$k+1] ){
                if(isset($next_neighbor_distances_bins[$leaf_id][$k])){
                    $next_neighbor_distances_bins[$leaf_id][$k]++;
                    
                }else{
                    $next_neighbor_distances_bins[$leaf_id][$k] = 1;
                    
                }
            }
        }
        if($distance > $NN_BIN_SIZES[$k]){
            if(isset($next_neighbor_distances_bins[$leaf_id][$k])){
                $next_neighbor_distances_bins[$leaf_id][$k]++;
            }else{
                $next_neighbor_distances_bins[$leaf_id][$k] = 1;
                
            }
        }
        if(!isset($all_next_neighbor_dist[$leaf_id])) $all_next_neighbor_dist[$leaf_id] = 0;
        $all_next_neighbor_dist[$leaf_id] += $distance;
        if(!isset($num_next_neighbor_dists[$leaf_id])) $num_next_neighbor_dists[$leaf_id] = 0;
        $num_next_neighbor_dists[$leaf_id]++;
    }
}
$sql_drop_temp_table->bindParam(':temp_table', $temp_dist_table, PDO::PARAM_STR);
$sql_drop_temp_table->execute() or die($sql_drop_temp_table->queryString.'lala<br/><br/>'.var_dump($sql_drop_temp_table->errorInfo()));
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~







//~~~~~~~~~~~~~~~~~~~~~~~~~~~FIND CENTER OF LEAF~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$temp_string = 'SELECT xCord,yCord FROM `:table`';
$temp_string .= (!$COUNT_OUTER)?" WHERE cord_type = 'inner' OR cord_type = 'auto'":'';
$sql_get_all_cords_from_table = $pdo_dbh->prepare($temp_string);
$sql_get_all_cords_from_table->bindParam(':table',$table_name,PDO::PARAM_STR);
$num_of_cords = 0;
$min_x = $image_x;
$min_y = 99999;
$max_y = -1;

$sql_get_all_cords_from_table->execute() or die($sql_get_all_cords_from_table->queryString.'lala<br/><br/>'.var_dump($sql_get_all_cords_from_table->errorInfo()));
while($row = $sql_get_all_cords_from_table->fetch(PDO::FETCH_ASSOC)){
    $num_of_cords++;
    if($row['yCord'] < $min_y) $min_y = $row['yCord'];
    if($row['yCord'] > $max_y) $max_y = $row['yCord'];
}
$sql_get_all_cords_from_table->closeCursor();

$min_y = abs($min_y);
$max_y = abs($max_y);
$mid_y = ($min_y + $max_y)/2;
$min_y += (.5*$image_y)-$mid_y;
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~






//~~~~~~~~~~~~~~~~~~~~~~~~SHIFT CORDS BACK INTO FRAME~~~~~~~~~~~~~~~~~~~~~~~~~~~
$sql_shift_points_back_in_temp_table = $pdo_dbh->prepare('UPDATE `:table_name` SET xCord = (xCord + :min_x), yCord = (yCord + :min_y)');
$sql_shift_points_back_in_temp_table->bindParam(':table_name', $table_name, PDO::PARAM_STR);
$sql_shift_points_back_in_temp_table->bindParam(':min_x', $min_x, PDO::PARAM_INT);
$sql_shift_points_back_in_temp_table->bindParam(':min_y', $min_y, PDO::PARAM_INT);
$sql_shift_points_back_in_temp_table->execute() or die($sql_shift_points_back_in_temp_table->queryString.'lala<br/><br/>'.var_dump($sql_shift_points_back_in_temp_table->errorInfo()));
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~






//~~~~~~~~~~~~~~~~~~~~~~~~~CREATE EACH BOX~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$x_divs = array();
$y_divs = array();
for($i = 0 ; $i < $num_boxes_x ; $i++){
    $x_divs[$i] = ($image_x/$num_boxes_x) * ($i+1);
}
for($i = 0 ; $i < $num_boxes_y ; $i++){
    $y_divs[$i] = ($image_y/$num_boxes_y) * ($i+1);
}
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~






//~~~~~~~~~~~~~~~~~~~DIVIDE POINTS FROM EACH LEAF INTO BOXES~~~~~~~~~~~~~~~~~~~~
$boxes_by_leaf = array();

$temp_string = 'SELECT xCord,yCord FROM `:table_name` WHERE fk_leaf_id = :leaf_id';
$temp_string .= (!$COUNT_OUTER)?" AND cord_type = 'inner' OR cord_type = 'auto'":'';

$sql_get_cords_from_table_by_leaf = $pdo_dbh->prepare($temp_string);
$sql_get_cords_from_table_by_leaf->bindParam(':table_name',$table_name,PDO::PARAM_STR);
$sql_get_cords_from_table_by_leaf->bindParam(':leaf_id',$leaf_id,PDO::PARAM_INT);

$found_boxes[] = array();
foreach($leaf_ids as $leaf_id){
    $boxes_by_leaf[$leaf_id] = array();
    $box_number = 0;
    for($i = 0 ; $i < ($num_boxes_y) ; $i++){
        for($j = 0 ; $j < ($num_boxes_x) ; $j++){
            $found_trichomes = false;
            $sql_get_cords_from_table_by_leaf->execute() or die($sql_get_cords_from_table_by_leaf->queryString.'lala<br/><br/>'.var_dump($sql_get_cords_from_table_by_leaf->errorInfo()));
            while($row = $sql_get_cords_from_table_by_leaf->fetch(PDO::FETCH_ASSOC)){
                $cord = array();
                $cord['x'] = $row['xCord'];
                $cord['y'] = $row['yCord'];
                if($j == 0 && $i == 0){
                    if($cord['x'] <= $x_divs[$j] && $cord['y'] <= $y_divs[$i]){
                        if(!isset($boxes_by_leaf[$leaf_id][$box_number])) $boxes_by_leaf[$leaf_id][$box_number] = 0;
                        $boxes_by_leaf[$leaf_id][$box_number]++;
                        $found_trichomes = true;
                    }
                }elseif($j == 0){
                    if($cord['x'] <= $x_divs[$j] && ($cord['y'] <= $y_divs[$i] && $cord['y'] > $y_divs[$i-1])){
                        if(!isset($boxes_by_leaf[$leaf_id][$box_number])) $boxes_by_leaf[$leaf_id][$box_number] = 0;
                        $boxes_by_leaf[$leaf_id][$box_number]++;
                        $found_trichomes = true;
                    }
                }elseif($i == 0){
                    if(($cord['x'] <= $x_divs[$j] && $cord['x'] > $x_divs[$j-1]) && $cord['y'] <= $y_divs[$i]){
                        if(!isset($boxes_by_leaf[$leaf_id][$box_number])) $boxes_by_leaf[$leaf_id][$box_number] = 0;
                        $boxes_by_leaf[$leaf_id][$box_number]++;
                        $found_trichomes = true;
                    }
                }else{
                    if(($cord['x'] <= $x_divs[$j] && $cord['x'] > $x_divs[$j-1]) && ($cord['y'] <= $y_divs[$i] && $cord['y'] > $y_divs[$i-1])){
                        if(!isset($boxes_by_leaf[$leaf_id][$box_number])) $boxes_by_leaf[$leaf_id][$box_number] = 0;
                        $boxes_by_leaf[$leaf_id][$box_number]++;
                        $found_trichomes = true;
                    }
                }
            }
            $box_number++;
            if($found_trichomes && !in_array($box_number, $found_boxes)){
                    $found_boxes[] = $box_number;
            }
        }
    }
}
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~







//~~~~~~~~~~~~~~~~~~~FIND MIN/MAX/TOTAL FOR EACH BOX~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$all_boxes = array();
$min_in_box = array();
$max_in_box = array();
$std_div_by_leaf = array();
$total_num_boxes = $num_boxes_x * $num_boxes_y;
foreach($leaf_ids as $leaf_id){
    for($i = 0 ; $i < $total_num_boxes ; $i++){
        if(isset($boxes_by_leaf[$leaf_id][$i])){
            if(!isset($max_in_box[$i])) $max_in_box[$i] = 0;
            if(!isset($min_in_box[$i])) $min_in_box[$i] = 999999;
            if($boxes_by_leaf[$leaf_id][$i] > $max_in_box[$i]) $max_in_box[$i] = $boxes_by_leaf[$leaf_id][$i];
            if($boxes_by_leaf[$leaf_id][$i] < $min_in_box[$i]) $min_in_box[$i] = $boxes_by_leaf[$leaf_id][$i];
            if(!isset($all_boxes[$i])) $all_boxes[$i]=0;
            $all_boxes[$i] += $boxes_by_leaf[$leaf_id][$i];
            if(!isset($std_div_by_leaf[$leaf_id])) $std_div_by_leaf[$leaf_id]=0;
            $std_div_by_leaf[$leaf_id] += $boxes_by_leaf[$leaf_id][$i];
        }
    }
    $std_div_by_leaf[$leaf_id] /= (count($found_boxes) - 1 );
}

$box_std_dev = array();

foreach($all_boxes as $box_num => $total_in_box){
    $sum = 0;
    $avg = $total_in_box / $number_of_leafs;
    foreach($leaf_ids as $leaf_id){
        $addition = (isset($boxes_by_leaf[$leaf_id][$box_num]))?$boxes_by_leaf[$leaf_id][$box_num]:0
                - $avg;
        $sum += ($addition*$addition);
    }
    $sum /= ($number_of_leafs - 1);
    $box_std_dev[$box_num] = sqrt($sum);
}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~




?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

      // Load the Visualization API and the piechart package.
      
      google.load('visualization', '1.0', {'packages':['corechart']});
      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {
          // Create the data table.
          <?php
            $sql_get_leaf_name_by_id = $pdo_dbh->prepare('SELECT `leaf_name` from leafs WHERE leaf_id = :leaf_id');
            $sql_get_leaf_name_by_id->bindParam(':leaf_id',$leaf_id,PDO::PARAM_INT);
            $output_string = '[\'Distances\',';
            foreach($leaf_ids as $leaf_id){
                $sql_get_leaf_name_by_id->execute();
                $row = $sql_get_leaf_name_by_id->fetch(PDO::FETCH_ASSOC);
                $output_string .= '\''.$row['leaf_name'].'\',';
            }
            $output_string .= '\'Average\'';//substr($output_string,0,-1);
            $output_string .= '],';
            foreach($BIN_SIZES as $bin_num => $size){
                $tmp_string = '[\'>'.$size.'\',';
                $is_zero = 0;
                $all_values = array();
                foreach($leaf_ids as $leaf_id){
                    if(isset($bins_per_leaf[$leaf_id][$bin_num])){
                        $tmp_string .= $bins_per_leaf[$leaf_id][$bin_num].',';
                        $all_values[] = $bins_per_leaf[$leaf_id][$bin_num];
                    }else{
                        $tmp_string .= "0,";
                        $is_zero++;
                        $all_values[] = 0;
                    }
                }
                $tmp_string .= array_sum($all_values)/count($all_values);
                $tmp_string .= "],";
                if($is_zero == count($leaf_ids)) $tmp_string = '';
                $output_string .= $tmp_string;
            }
            $output_string = substr($output_string,0,-1);
         ?>
        var data = google.visualization.arrayToDataTable([<?php echo $output_string; ?>]);

        // Set chart options
        var options = {title : 'Next Neighbor Distances',
                        width: 800,
                        height: 600,
                        vAxis: {title: "Number Found"},
                        hAxis: {title: "Distance Bins"},
                        seriesType: "bars",
                        series: {<?php echo count($leaf_ids); ?>: {type: "line"} }
                      };

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
        <?php if($SHOW_BIN_GRAPH)
                echo 'chart.draw(data, options);'; ?>
        init();
      }
      
      
        function init() {
            
            
            
            
            sessionStorage.clear();
            <?php
               
                $box_number = 0;
                $box_width = $image_x/$num_boxes_x;
                $box_height = $image_y/$num_boxes_y;
                for($i = 0 ; $i < ($num_boxes_y) ; $i++){
                    for($j = 0 ; $j < ($num_boxes_x) ; $j++){
                        if(!isset($all_boxes[$box_number]))  $all_boxes[$box_number]=0;
                        if(!isset($box_std_dev[$box_number])) $box_std_dev[$box_number]=0;
                        $avg_per_leaf = $all_boxes[$box_number]/$number_of_leafs;
                        $std_dev = $box_std_dev[$box_number];
                        $box_color = null;
                        //~~~~~~~~~~~~~~~~~~~~~~~GET BOX COLOR~~~~~~~~~~~~~~~~~~
                        $k = 0;
                        for( ; $k < $NUM_COLORS-1 ; $k++ ){
                            if($avg_per_leaf > $HEAT_MAP_RANGES[$k] && $avg_per_leaf <= $HEAT_MAP_RANGES[$k+1]){
                                $box_color = $HEAT_MAP_COLORS[$k];
                            }
                        }
                        if($avg_per_leaf > $HEAT_MAP_RANGES[$k]) $box_color = $HEAT_MAP_COLORS[$k];
                        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
                        if($j == 0 && $i == 0){
                            $x1 = 0;
                            $y1 = 0;
                            if(!is_null($box_color))
                                echo "drawBox($x1,$y1,$box_width,$box_height,'$box_color',$avg_per_leaf,$std_dev);";
                            if($SHOW_BOX_OUTLINES)
                                echo "drawBox($x1,$y1,$box_width,$box_height);";
                            
                        }elseif($j == 0){
                            $x1 = 0;
                            $y1 = $y_divs[$i-1];
                            if(!is_null($box_color))
                                echo "drawBox($x1,$y1,$box_width,$box_height,'$box_color',$avg_per_leaf,$std_dev);";
                                
                            if($SHOW_BOX_OUTLINES)
                                echo "drawBox($x1,$y1,$box_width,$box_height);";
                            
                        }elseif($i == 0){
                            $x1 = $x_divs[$j-1];
                            $y1 = 0;
                            if(!is_null($box_color))
                                echo "drawBox($x1,$y1,$box_width,$box_height,'$box_color',$avg_per_leaf,$std_dev);";
                            if($SHOW_BOX_OUTLINES)
                                echo "drawBox($x1,$y1,$box_width,$box_height);";
                            
                        }else{
                            $x1 = $x_divs[$j-1];
                            $y1 = $y_divs[$i-1];
                            if(!is_null($box_color))
                                echo "drawBox($x1,$y1,$box_width,$box_height,'$box_color',$avg_per_leaf,$std_dev);";
                            if($SHOW_BOX_OUTLINES)
                                echo "drawBox($x1,$y1,$box_width,$box_height);";
                            
                        }
                    $box_number++;
                    }
                }
                
                $color_key = '<table border="1"><tr><td>Leaf Name</td><td>Color</td><td>Include Leaf ?</td><td>Include Outline</td></tr>';
                $i = 0;
                    
                    $color_by_leaf_id = array();
                    //$sql_get_leaf_name_by_id = $pdo_dbh->prepare('SELECT `leaf_name` from leafs WHERE leaf_id = :leaf_id');
                    $sql_get_leaf_name_by_id->bindParam(':leaf_id',$leaf_id,PDO::PARAM_INT);
                    foreach($leaf_ids as $leaf_id){
                        $sql_get_leaf_name_by_id->execute() or die($sql_get_cords_from_table_by_leaf->queryString.'lala<br/><br/>'.var_dump($sql_get_cords_from_table_by_leaf->errorInfo()));
                        $row = $sql_get_leaf_name_by_id->fetch(PDO::FETCH_ASSOC);
                        $color_arg = $i++ % $NUM_LEAF_COLORS;
                        $color = $LEAF_COLORS[$color_arg];
                        $color_by_leaf_id[$leaf_id] = $color;
                        $color_key .= '<tr><td>'.$row['leaf_name']."</td>
                                            <td>".$LEAF_COLOR_NAMES[$color_arg]."</td>
                                            <td align='center'><input type='checkbox' name='all_leaf_ids[]' value='$leaf_id' id='include_$leaf_id' 
                                            onclick='document.getElementById(\"outline_$leaf_id\").checked=this.checked; document.getElementById(\"outline_$leaf_id\").disabled = !this.checked;' checked></td>
                                            <td align='center'><input type='checkbox' name='outline_leaf_ids[]' id='outline_$leaf_id' value='$leaf_id' ";
                        $color_key .= (isset($outline_leaf_ids) && in_array($leaf_id, $outline_leaf_ids))?"checked":"";
                        $color_key .= "></td></tr>";
                        $sql_get_leaf_name_by_id->closeCursor();
                    }
                    $color_key .= '</table>';
                    
                if($SHOW_LEAF_EDGE){
                    $sql_get_outer_cords_from_table_by_leaf = $pdo_dbh->prepare('SELECT xCord,yCord FROM `:table_name` WHERE fk_leaf_id = :leaf_id AND cord_type = "outter"');
                    $sql_get_outer_cords_from_table_by_leaf->bindParam(":leaf_id", $leaf_id,PDO::PARAM_INT);
                    $sql_get_outer_cords_from_table_by_leaf->bindParam(":table_name", $table_name,PDO::PARAM_STR);
                    
                    foreach($outline_leaf_ids as $leaf_id){
                        if(count($outline_leaf_ids) == 1)
                            $color = "black";
                        else
                            $color = $color_by_leaf_id[$leaf_id];
                        
                        $sql_get_outer_cords_from_table_by_leaf->execute() or die($sql_get_outer_cords_from_table_by_leaf->queryString.'lala<br/><br/>'.var_dump($sql_get_outer_cords_from_table_by_leaf->errorInfo()));
                        echo 'var c=document.getElementById("myCanvas");',
                                 'var ctx=c.getContext("2d");',
                                 'ctx.strokeStyle = \'',$color,'\';',
                                'ctx.beginPath();';
                        $first = true;
                        while($row = $sql_get_outer_cords_from_table_by_leaf->fetch(PDO::FETCH_ASSOC)){
                            if($first){
                                echo 'ctx.moveTo(',$row['xCord'],',',$row['yCord'],');';
                                $first = !$first;
                            }else{
                                echo 'ctx.lineTo(',$row['xCord'],',',$row['yCord'],');';
                            }
                    }
                    echo 'ctx.closePath();',
                         'ctx.stroke();';
                    }
                    unset($leaf_id);
                    reset($leaf_ids);
                }
                
                if($SHOW_TRICOMBS){
                    $i = 0;
                    $sql_get_inner_cords_from_table_by_leaf = $pdo_dbh->prepare('SELECT xCord,yCord FROM `:table_name` WHERE cord_type = "inner" AND fk_leaf_id = :leaf_id');
                    $sql_get_inner_cords_from_table_by_leaf->bindParam(":leaf_id", $leaf_id,PDO::PARAM_INT);
                    $sql_get_inner_cords_from_table_by_leaf->bindParam(":table_name", $table_name,PDO::PARAM_STR);
                    foreach($leaf_ids as $leaf_id){
                        $color_arg = $i++ % $NUM_LEAF_COLORS;
                        $color = $LEAF_COLORS[$color_arg];
                        $sql_get_inner_cords_from_table_by_leaf->execute() or die($sql_get_inner_cords_from_table_by_leaf->queryString.'lala<br/><br/>'.var_dump($sql_get_inner_cords_from_table_by_leaf->errorInfo()));
                        while($row = $sql_get_inner_cords_from_table_by_leaf->fetch(PDO::FETCH_ASSOC)){
                            echo "addPoint(",$row['xCord'],",",$row['yCord'],",'$color');";
                        }
                    }
                }
                
            ?>
            
        }


    function addline(x1,y1,x2,y2,txt){
        var c=document.getElementById("barCanvas");
        var ctx=c.getContext("2d");
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(x1,y1);
        ctx.lineTo(x2,y2);
        ctx.closePath();
        ctx.stroke();
        ctx.font = "normal normal 16px Helvetica"; // set font weight, size, etc
        ctx.textBaseline = "middle"; // how to align the text vertically
        ctx.textAlign = "end"; // how to align the text horizontally
        
        ctx.fillText(txt, (x2+x1)/2 , y1-10);
    }
    
    function addBar(height,width,bin,color,avg,std_txt){
        width = Math.round(width*100)/100;
        //var average = Math.round(avg*100)/100;
        var c=document.getElementById("barCanvas");
        var ctx=c.getContext("2d");
        var num_bars = sessionStorage.getItem('num_bars');
        if(num_bars === undefined || num_bars == '' || num_bars === null){
            num_bars = 0;
        }
        
        if(color === undefined)
            ctx.strokeStyle = "#000000";    
        else
            ctx.fillStyle = color;
        
        var x1 = (width+5) * num_bars;
        var y1 = <?php echo $image_y; ?>;
        
        ctx.strokeRect(x1, y1, width, -1*avg);
        ctx.fillStyle = "#000000";
        ctx.font = "normal normal 16px Helvetica"; // set font weight, size, etc
        ctx.textBaseline = "middle"; // how to align the text vertically
        ctx.textAlign = "end"; // how to align the text horizontally
        
        ctx.fillText(">"+bin, x1+(width/2), <?php echo $image_y - 10; ?>); // text, x, y
        var top_text = <?php echo $image_y; ?> - avg + 20;
        if(top_text < 50)   
            top_text = 50;
        else if(top_text >= (<?php echo $image_y; ?> - 20) )
            top_text = <?php echo $image_y - 50; ?>;
        ctx.fillText(Math.round(std_txt*100)/100, x1+(width/2), top_text);
        //      x1,y2,x2,y2
        
        addline(x1,<?php echo $image_y; ?> - (avg-height),x1+width,<?php echo $image_y; ?> - (avg-height),'');
        
        addline(x1,<?php echo $image_y; ?> - (avg+height),x1+width,<?php echo $image_y; ?> - (avg+height),'');
        
        addline((2* x1 + width)/2,<?php echo $image_y; ?> - (avg+height),(2* x1 + width)/2,<?php echo $image_y; ?> - (avg-height),'');
        
        sessionStorage.removeItem('num_bars');
        sessionStorage.setItem('num_bars',++num_bars);
    }
    
  
    function updateShapeImg(leaf_id,div_id){
        if(leaf_id == -1 || leaf_id == '-1') return;
        var xmlhttp;
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                document.getElementById(div_id).innerHTML=xmlhttp.responseText;
            }
        }
        var sendstr = "?leaf_id="+leaf_id;
        xmlhttp.open("GET","getLeafThumbImage.php"+sendstr,true);
        xmlhttp.send();
    }
    
    function writeText(){
        
    }
    
    //getCordsByLeaf
    function addCordsByLeaf(leaf_id,height,width){
        if(leaf_id == -1 || leaf_id == '-1') return;
        resize(height,width);
        var xmlhttp;
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                var result = xmlhttp.responseText;
                if(result == '0' || result == 0){
                    alert("ERROR PROCESSING DATA");
                    return;
                }
                var split = result.split('~');
                var xCords = split[0].split(',');
                var yCords = split[1].split(',');
                var Types  = split[2].split(',');
                addAllPoints(xCords,yCords,Types);
            }
        }
        var sendstr = "?leaf_id="+leaf_id;
        xmlhttp.open("GET","getCordsByLeaf.php"+sendstr,true);
        xmlhttp.send();
    }
    
    function addAllPoints(x_cords,y_cords,Types){
        var num_elements = x_cords.length;
        for(var i = 0 ; i < num_elements ; i++){
            addPoint(x_cords[i],y_cords[i],Types[i])
        }
    }
    
    function resize(height,width){
        var c=document.getElementById("myCanvas");
        c.height  = height;
        c.width   = width;
    }
    
    function drawLine(x1,y1,x2,y2){
        var c=document.getElementById("myCanvas");
        var ctx=c.getContext("2d");
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(x1,y1);
        ctx.lineTo(x2,y2);
        ctx.closePath();
        ctx.stroke();
    }
    
    function drawBox(x1,y1,x2,y2,color,avg,std_dev){
        var c=document.getElementById("myCanvas");
        var ctx=c.getContext("2d");
        if(color === undefined){
            ctx.strokeStyle = "#000000";
            ctx.strokeRect(x1, y1, x2, y2);
        }else{
            ctx.fillStyle = color;
            ctx.fillRect(x1, y1, x2, y2);
        }
        if(avg !== undefined){
            if(avg > 0){
                <?php 
                    if($SHOW_VALUES){ ?>
                ctx.fillStyle = "#000000";
                ctx.font = "normal normal 16px Helvetica"; // set font weight, size, etc
                ctx.textBaseline = "middle"; // how to align the text vertically
                ctx.textAlign = "end"; // how to align the text horizontally
                //var textstr = "("+x+","+y+") "+weight;
                
                avg = Math.round(avg*100)/100;
                ctx.fillText(avg, x1+(x2/2), y1+(y2/2)); // text, x, y
                //ctx.textBaseline = "middle";
                //ctx.textAlign = "end";
                //std_dev = Math.round(std_dev*1000)/1000;
                //ctx.fillText(std_dev, x1+(x2/2)+10, y1+(y2/2)+20); // text, x, y
                <?php } ?>
            }
        }
    }
    
    function addPoint(x,y,color){
        var c=document.getElementById("myCanvas");
        var ctx=c.getContext("2d");
        if(color === undefined)
            color = '#000000';
       
        ctx.strokeStyle = color;
        ctx.fillStyle = color;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(x,y,3,0,Math.PI*2,true);
        ctx.closePath();
        ctx.fill();
        

    }
    
    function makeCSV(){
        var leaf_ids = "<?php echo $all_ids; ?>";
        var bin_step = <?php echo $incrm ?>;
        var nn_bin_step = <?php echo $nn_incrm ?>;
        var bin_details = '<?php echo json_encode($bins_per_leaf); ?>';
        var nn_bin_details = '<?php echo json_encode($next_neighbor_distances_bins); ?>'
        var xmlhttp;
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                document.getElementById('csv').innerHTML=xmlhttp.responseText;
            }
        }
        //var sendstr = "?leaf_ids="+leaf_ids+"&bin_details="+'<?php echo json_encode($bins_per_leaf); ?>'+"&bin_step="+bin_step+"&nn_bin_details="+'<?php echo json_encode($next_neighbor_distances_bins); ?>'+"&nn_bin_step="+nn_bin_step;
        var parameters = "leaf_ids="+encodeURIComponent(leaf_ids)+"&bin_step="+encodeURIComponent(bin_step)+"&nn_bin_step="+encodeURIComponent(nn_bin_step)+"&bin_details="+encodeURIComponent(bin_details)+"&nn_bin_details="+encodeURIComponent(nn_bin_details);
        //xmlhttp.open("GET","mkcsv.php"+sendstr,true);
        xmlhttp.open("POST", "mkcsv.php", true)
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
        xmlhttp.send(parameters);
    }
</script>
<style type="text/css" media="screen">
    canvas, img { display:block;  border:1px solid black; }
    canvas { background:url('./pics/blank.jpg') }
</style>
<?php

echo '<br/><br/> AVG PER BOX PER LEAF: ',array_sum($all_boxes)/(count($found_boxes) - 1 + $additional_boxes)/$number_of_leafs,' +/- ',stndev($std_div_by_leaf),
     ' <em>(this only counts boxes that have tricomes in them)</em><br/>';
?>
<canvas id="myCanvas" width="<?php echo $image_x; ?>" height="<?php echo $image_y; ?>"></canvas>
<?php
    echo "<hr/><form method='post' action='",htmlentities($_SERVER['PHP_SELF']),"'>",$color_key,"<br/>","<input type='hidden' name='show_bar_graph' value='$_POST[show_bar_graph]' />",
            "<input type='hidden' name='bar_range' value='$_POST[bar_range]' />",
            "<input type='hidden' name='num_boxes_x' value='$_POST[num_boxes_x]' />",
            "<input type='hidden' name='num_boxes_y' value='$_POST[num_boxes_y]' />",
            "<input type='hidden' name='edge' value='$_POST[edge]' />",
            "<input type='hidden' name='count_outer' value='$_POST[count_outer]' />",
            "<input type='hidden' name='show_values' value='$_POST[show_values]' />",
            "<input type='hidden' name='graph_bin_size' value='$_POST[graph_bin_size]' />",
            "<input type='hidden' name='outline' value='$_POST[outline]' />",
            "<input type='hidden' name='tricomes' value='$_POST[tricomes]' />",
            "<input type='hidden' name='nn_bar_range' value='$_POST[nn_bar_range]' />",
            "<input type='hidden' name='nn_graph_bin_size' value='$_POST[nn_graph_bin_size]' />",
            "Add <input type='number' name='additional_boxes' min='0' max='",$_POST['num_boxes_x']*$_POST['num_boxes_y'],"' step='1' value='0'> Empty Boxes <br/>",
            "<button type='submit'>ReAnalayze</button></form>";
    ?>
<br/>
    <div id="chart_div"></div>
    <div id="csv" style="padding-bottom: 50px; padding-top: 50px;">
        <button type="button" onClick="makeCSV();">Export Data</button>
    </div>
<br/><br/>

    
    
    
