<?php
if(!isset($_SESSION)) session_start();
include_once 'connection.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

$user_id = $_SESSION['user_id'];

if(!isset($_GET['genotype_id'])) die("No Genotype Selected");
$genotype_id = $_GET['genotype_id'];
$stmt_get_cord_count_by_leafid = $pdo_dbh->prepare('SELECT count(xCord) as cnt FROM cords WHERE fk_leaf_id = :leaf_id');


$stmt_get_leafs_by_genotype = $pdo_dbh->prepare("SELECT `leaf_id`,`leaf_name`,`file_name` FROM leafs WHERE fk_genotype_id = :genotype_id AND owner_id = :user_id ORDER BY leaf_name");
$stmt_get_leafs_by_genotype->bindValue(':genotype_id', $genotype_id, PDO::PARAM_INT);
$stmt_get_leafs_by_genotype->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt_get_leafs_by_genotype->execute();


$stmt_get_cord_count_by_leafid->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);
$all_leafs = array();
$first_key = null;
while($row = $stmt_get_leafs_by_genotype->fetch(PDO::FETCH_ASSOC)){
    if(is_null($first_key)) $first_key = $row['leaf_id'];
    $leaf_id = $row['leaf_id'];
    $stmt_get_cord_count_by_leafid->execute();
    $row2 = $stmt_get_cord_count_by_leafid->fetch(PDO::FETCH_ASSOC);
    $all_leafs[$row['leaf_id']] = array();
    $all_leafs[$row['leaf_id']]['name'] = $row['leaf_name'];
    $all_leafs[$row['leaf_id']]['file'] = $row['file_name'];
    $all_leafs[$row['leaf_id']]['count'] = $row2['cnt'];
    $stmt_get_cord_count_by_leafid->closeCursor();
}
unset($leaf_id);
$stmt_get_leafs_by_genotype->closeCursor();
?>
<form action="alignpoints3.php" method="post" onSubmit="return loop_select();">
    <input type="hidden" name="genotype_id" value="<?php echo $genotype_id; ?>"/>
<table rules="groups">
    <tr>
        <td><strong>Select Leafs To Include:</strong></td>
        <td></td>
        <td><strong>Selected Leafs:</strong></td>
        <td></td>
        <td></td>
        <td>
        </td>
    </tr>
    <tr> 
        <td>
            <select name="full_list" id="fl" onChange="getLeafDetails(this);" size="5" multiple="multiple">
            <?php 
                foreach($all_leafs as $leaf_id => $leaf){
                    echo '<option value="',$leaf_id,'">',$leaf['name'],' (',$leaf['count'],')</option>';
                }
            ?>    
            </select>
        </td>
        <td align="center">
            <button type="button" name="add_this" onclick="moveLeaf(this.value);return false;"> &gt;&gt; </button><br/>
            <button type="button" onClick="moveBack();return false;"> &lt;&lt; </button>
        </td>
        <td>
            <select name="all_leaf_ids[]" id="selected" onChange="getLeafDetails(this);" size="5" multiple="multiple"></select>
        </td>
        <td>
            
        </td>
        <td></td>
        <td>
        </td>
    </tr>
    <tr>
        <td>
            <strong>Heat Map</strong>
        </td>
        <td>
            Show Box Outlines: <br/>
            <input type="radio" name="outline" value="1" <?php if(isset($_SESSION['outline']) && $_SESSION['outline'] == 1) echo 'checked'; ?>> Yes<br/>
            <input type="radio" name="outline" value="0" <?php if(!isset($_SESSION['outline']) || (isset($_SESSION['outline']) && $_SESSION['outline'] == 0) )  echo 'checked'; ?>> No
        </td>
        <td>
            Show Trichomes: <br/>
            <input type="radio" name="tricomes" value="1" <?php if(!isset($_SESSION['tricomes']) || (isset($_SESSION['tricomes']) && $_SESSION['tricomes'] == 1) )  echo 'checked'; ?>> Yes<br/>
            <input type="radio" name="tricomes" value="0" <?php if(isset($_SESSION['tricomes']) && $_SESSION['tricomes'] == 0) echo 'checked'; ?>> No
        </td>
        <td>
            Show Leaf Edge: <br/>
            <input type="radio" name="edge" value="1" id="edge1" <?php if(!isset($_SESSION['edge']) || (isset($_SESSION['edge']) && $_SESSION['edge'] == 1) )  echo 'checked'; ?>> Yes<br/>
            <input type="radio" name="edge" value="0" id="edge2" <?php if(isset($_SESSION['edge']) && $_SESSION['edge'] == 0) echo 'checked'; ?>> No
        </td>
        <td>
           Num Boxes:<br/>
               X-Axis: <input type="number" name="num_boxes_x" min="1" max="24" step="1" value="<?php if(isset($_SESSION['num_boxes_x']) && $_SESSION['num_boxes_x'] != 0) echo $_SESSION['num_boxes_x']; else echo '16'; ?>"/><br/>
               Y-Axis: <input type="number" name="num_boxes_y" min="1" max="24" step="1" value="<?php if(isset($_SESSION['num_boxes_y']) && $_SESSION['num_boxes_y'] != 0) echo $_SESSION['num_boxes_y']; else echo '16'; ?>"/>
        </td>
        <td>
            Show Values:<br/>
            <input type="radio" name="show_values" value="1" <?php if(!isset($_SESSION['show_values']) || (isset($_SESSION['show_values']) && $_SESSION['show_values'] == 1) )  echo 'checked'; ?>> Yes<br/>
            <input type="radio" name="show_values" value="0" <?php if(isset($_SESSION['show_values']) && $_SESSION['show_values'] == 0) echo 'checked'; ?>> No
        </td>    
    </tr>
    <tr>
        <td>
            <strong>Distances:</strong>
        </td>
        <td>
            Show Distance Averages:<br/>
            <input type="radio" name="show_bar_graph" value="1" <?php if(!isset($_SESSION['show_bar_graph']) || (isset($_SESSION['show_bar_graph']) && $_SESSION['show_bar_graph'] == 1) )  echo 'checked'; ?>> Yes<br/>
            <input type="radio" name="show_bar_graph" value="0" <?php if(isset($_SESSION['show_bar_graph']) && $_SESSION['show_bar_graph'] == 0) echo 'checked'; ?>> No
        </td>
        <td>
            Range of Distances:<br/>
            0 to <input type="number" name="bar_range" min="1000" max="5000" step="100" value="<?php if(isset($_SESSION['bar_range']) && $_SESSION['bar_range'] != 0) echo $_SESSION['bar_range']; else echo '2000'; ?>"/>
        </td>
        <td>
            Distance Bin Size: <br/>
            <input type="number" name="graph_bin_size" min="10" max="1000" step="5" value="<?php if(isset($_SESSION['graph_bin_size']) && $_SESSION['graph_bin_size'] != 0) echo $_SESSION['graph_bin_size']; else echo '100'; ?>"/>
        </td>
        <td>
            Range of Next Neighbor Distances:<br/>
            0 to <input type="number" name="nn_bar_range" min="100" max="500" step="10" value="<?php if(isset($_SESSION['nn_bar_range']) && $_SESSION['nn_bar_range'] != 0) echo $_SESSION['nn_bar_range']; else echo '200'; ?>"/>
        </td>
        <td>
            Next Neighbor Distance Bin Size: <br/>
            <input type="number" name="nn_graph_bin_size" min="1" max="100" step="1" value="<?php if(isset($_SESSION['nn_graph_bin_size']) && $_SESSION['nn_graph_bin_size'] != 0) echo $_SESSION['nn_graph_bin_size']; else echo '10'; ?>"/>
        </td>
    </tr>
    <tr>
        <td>
            <strong>General</strong>
        </td>
        <td>
            Count Outer Trichomes:<br/>
            <input type="radio" name="count_outer" value="1" onClick="document.getElementById('edge2').checked = true;document.getElementById('edge1').disabled = true;document.getElementById('edge2').disabled = true;" <?php if(isset($_SESSION['outline']) && $_SESSION['outline'] == 1) echo 'checked'; ?>> Yes<br/>
            <input type="radio" name="count_outer" value="0" onClick="document.getElementById('edge1').disabled = false;document.getElementById('edge2').disabled = false;" <?php if(!isset($_SESSION['count_outer']) || (isset($_SESSION['count_outer']) && $_SESSION['count_outer'] == 0) )  echo 'checked'; ?>> No
        </td>
    </tr>
    <tr>
        <td>
            <button type="Submit">Analyze Selected</button>
        </td>
    </tr>
</table>
</form>

<br/>
<div id="details"></div>
    
    
