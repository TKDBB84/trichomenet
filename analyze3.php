<?php
if (!isset($_SESSION))
    session_start();
include_once 'connection.php';
include_once 'chkcookie.php';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else if (validCookie($_COOKIE, $pdo_dbh)) {
    doLogin($_COOKIE, $pdo_dbh);
    $user_id = $_SESSION['user_id'];
} else {
    $_SESSION['error'] = true;
    $_SESSION['error_text'] = '<br/>You Do Not Appear To Be Logged In<br/>
                               Or Your Sessison Has Expired';
    header('Location: ./login.php');
    die();
}

$active_geno = -1;
if (!isset($_SESSION['active_geno'])) {
    $stmt_get_last_genotype = $pdo_dbh->prepare('SELECT last_active_genotype FROM `users` WHERE `user_id` = :user_id');
    $stmt_get_last_genotype->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_get_last_genotype->execute();
    $result = $stmt_get_last_genotype->fetch(PDO::FETCH_ASSOC);
    if ($result['last_active_genotype'] != null)
        $active_geno = $result['last_active_genotype'];
    $_SESSION['active_geno'] = $active_geno;
}else {
    $active_geno = $_SESSION['active_geno'];
}


$genotypes = array();
$stmt_get_genotypes = $pdo_dbh->prepare('SELECT genotype_id,genotype FROM genotypes WHERE `owner_id` = :user_id');
$stmt_get_genotypes->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt_get_genotypes->execute();
$result = $stmt_get_genotypes->fetchAll(PDO::FETCH_ASSOC);

if (count($result) > 0) {
    foreach ($result as $row) {
        $genotypes[$row['genotype_id']] = $row['genotype'];
    }
} else {
    $genotypes[0] = "No Genotypes";
}

if ($active_geno !== -1) {
    $stmt_get_cord_count_by_leafid = $pdo_dbh->prepare('SELECT count(xCord) as cnt FROM cords WHERE fk_leaf_id = :leaf_id');


    $stmt_get_leafs_by_genotype = $pdo_dbh->prepare("SELECT `leaf_id`,`leaf_name`,`file_name` FROM leafs WHERE fk_genotype_id = :genotype_id AND owner_id = :user_id ORDER BY leaf_name");
    $stmt_get_leafs_by_genotype->bindValue(':genotype_id', $active_geno, PDO::PARAM_INT);
    $stmt_get_leafs_by_genotype->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_get_leafs_by_genotype->execute();


    $stmt_get_cord_count_by_leafid->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);
    $all_leafs = array();
    $first_key = null;
    while ($row = $stmt_get_leafs_by_genotype->fetch(PDO::FETCH_ASSOC)) {
        if (is_null($first_key))
            $first_key = $row['leaf_id'];
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
}
?>
<!DOCTYPE html>
<html>
    <head>
        <LINK href="./css/trichomenet.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css" media="screen"></style>
        <title>TrichomeNet</title>
        <script type="text/javascript">
<?php
if (isset($genotypes[0]) && $genotypes[0] === "No Genotypes") {
    echo 'document.addEventListener("DOMContentLoaded", function()
                                    {',
    'overlay("no_genotypes");',
    '}, false);';
} elseif ($active_geno === -1) {
    echo 'document.addEventListener("DOMContentLoaded", function()
                                    {',
    'overlay("no_active_type");',
    '}, false);';
} elseif (isset($_SESSION['all_ids'])) {
    foreach ($_SESSION['all_ids'] as $leaf_id)
        echo 'moveLeaf("', $leaf_id, '");';
    unset($_SESSION['all_ids']);
}
?>
    
                              function getLeafDetails(list){
                                  var selected = new Array();
                                  for (var i = 0; i < list.options.length; i++)
                                      if (list.options[ i ].selected)
                                          selected.push(list.options[ i ].value);
                                  if(selected.length == 0) return;
                                  if(selected.length == 1)
                                      leaf_id_list = selected[0];
                                  else
                                      leaf_id_list = selected.join();
                                  var xmlhttp;
                                  if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
                                      xmlhttp=new XMLHttpRequest();
                                  }else{// code for IE6, IE5
                                      xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                                  }
                                  xmlhttp.onreadystatechange=function(){
                                      if (xmlhttp.readyState==4 && xmlhttp.status==200){
                                          document.getElementById('details').innerHTML=xmlhttp.responseText;
                                      }
                                  }
                                  var sendstr = "?leaf_id="+leaf_id_list;
                                  xmlhttp.open("GET","leafDetails.php"+sendstr,true);
                                  xmlhttp.send();
                              }
    
                              function moveLeaf(leaf_id){
                                  if( leaf_id === -1 || leaf_id === "-1") return false;
                                  var remove_from = document.getElementById('fl');  
                                  var i;
                                  var newOptions = new Array();
                                  //var elOptNew = document.createElement('option');
                                  for (i = remove_from.length - 1; i>=0; i--) {
                                      if (remove_from.options[i].selected || remove_from.options[i].value == leaf_id) {
                                          newOptions.push(document.createElement('option'));
                                          var arr_index = newOptions.length - 1;
                                          newOptions[arr_index].text = remove_from.options[i].text;
                                          newOptions[arr_index].value = remove_from.options[i].value;
                                          remove_from.remove(i);
                                      }
                                  }
                                  var add_to = document.getElementById('selected');
                                  for(var i = 0 ; i < newOptions.length ; i++){
                                      try{
                                          add_to.add(newOptions[i], null); 
                                      }catch(ex){
                                          add_to.add(newOptions[i]);
                                      }
                                  }
                                  sortSelect(add_to);
                                  return false;
                              }
    
                              function moveBack(){
                                  var remove_from = document.getElementById('selected');  
                                  var i;
                                  var newOptions = new Array();
                                  //var elOptNew = document.createElement('option');
                                  for (i = remove_from.length - 1; i>=0; i--) {
                                      if (remove_from.options[i].selected) {
                                          newOptions.push(document.createElement('option'));
                                          var arr_index = newOptions.length - 1;
                                          newOptions[arr_index].text = remove_from.options[i].text;
                                          newOptions[arr_index].value = remove_from.options[i].value;
                                          remove_from.remove(i);
                                      }
                                  }
                                  var add_to = document.getElementById('fl');
                                  for(var i = 0 ; i < newOptions.length ; i++){
                                      try{
                                          add_to.add(newOptions[i], null); 
                                      }catch(ex){
                                          add_to.add(newOptions[i]);
                                      }
                                  }
                                  sortSelect(add_to);
                                  return false;
                              }
    
                              function sortSelect(selElem) {
                                  var tmpAry = new Array();
                                  for (var i=0;i<selElem.options.length;i++) {
                                      tmpAry[i] = new Array();
                                      tmpAry[i][0] = selElem.options[i].text;
                                      tmpAry[i][1] = selElem.options[i].value;
                                  }
                                  tmpAry.sort();
                                  while (selElem.options.length > 0) {
                                      selElem.options[0] = null;
                                  }
                                  for (var i=0;i<tmpAry.length;i++) {
                                      var op = new Option(tmpAry[i][0], tmpAry[i][1]);
                                      selElem.options[i] = op;
                                  }
                                  return;
                              }
    
                              function loop_select() {
                                  var select_box = document.getElementById('selected');
                                  if(select_box.options.length < 2){
                                      alert('You Must Select At Least 2 Leaves');
                                      return false;
                                  }
                                  for(i=0;i<=select_box.options.length-1;i++)
                                      select_box.options[i].selected = true;
                                  var select_box = document.getElementById('fl');
                                  for(i=0;i<=select_box.options.length-1;i++)
                                      select_box.options[i].selected = false;
                                  return true;
                              }
    
                              function overlay(){
                                  var e = document.getElementById("overlay");
                                  if(e.style.visibility == "visible"){
                                      e.style.visibility = "hidden";
                                      document.body.style.overflow = 'auto';
                                  }else{
                                      e.style.visibility = "visible";
                                      document.body.style.overflow = 'hidden';
                                  }
                              }
        </script>
    </head>
    <body onload="">
        <div class="header">
            <div id="logo"></div>
            <div class="header" id="logo_text">
                <a class="header" href="./index.php"><span>TRICHOME<span>NET</span></span></a>
            </div>

            <div class="linkblock">
                <table id="link_table">
                    <tr>
<?php include 'linktable.php'; ?>
                    </tr>
                </table>
            </div>
        </div>
        <div class="sidebar">
            <span>Step 1: Define Genotypes</span>
            <br/><br/>
            <span>Step 2: Upload Leaf Images</span>
            <br/><br/>
            <span>Step 3: Detect Trichomes</span>
            <br/><br/>
            <span>Step 4: Conduct Analyses</span>
            <br/><br/>
            <span style="position: absolute; bottom: 0; right: 0;">
                If you have any problems with the software, 
                please leave any issues at: 
                <a href="https://github.com/TKDBB84/trichomenet">
                    TrichomeNet On Github
                </a>
                <br/><br/>
            </span>
        </div>
        <div class="contents">
            <div id="contents_header">
                <b>Trichome Positional Analysis:</b>
            </div>
            <div id="main_contents">
                Select leaves and options for positional analysis. Grouping of multiple leaves is currently limited to leaves within a genotype.
                <div id="framed">
                    <div id="main"><form action="alignpoints3.php" method="post" onSubmit="return loop_select();">
                            <input type="hidden" name="genotype_id" value="<?php echo $genotype_id; ?>"/>
                            <table rules="groups">
                                <thead>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td><strong>Select Leafs To Include:</strong></td>
                                        <td></td>
                                        <td><strong>Selected Leafs:</strong></td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr> 
                                        <td></td>
                                        <td>
                                        </td>
                                        <td>
                                            <select name="full_list" id="fl" onChange="getLeafDetails(this);" size="5" multiple="multiple">
                                                <?php
                                                foreach ($all_leafs as $leaf_id => $leaf) {
                                                    echo '<option value="', $leaf_id, '">', $leaf['name'], ' (', $leaf['count'], ')</option>';
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>Heat Map</strong>
                                        </td>
                                        <td>
                                            Show Box Outlines: <br/>
                                            <input type="radio" name="outline" value="1" <?php if (isset($_SESSION['outline']) && $_SESSION['outline'] == 1) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="outline" value="0" <?php if (!isset($_SESSION['outline']) || (isset($_SESSION['outline']) && $_SESSION['outline'] == 0)) echo 'checked'; ?>> No
                                        </td>
                                        <td>
                                            Show Trichomes: <br/>
                                            <input type="radio" name="tricomes" value="1" <?php if (!isset($_SESSION['tricomes']) || (isset($_SESSION['tricomes']) && $_SESSION['tricomes'] == 1)) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="tricomes" value="0" <?php if (isset($_SESSION['tricomes']) && $_SESSION['tricomes'] == 0) echo 'checked'; ?>> No
                                        </td>
                                        <td>
                                            Show Leaf Edge: <br/>
                                            <input type="radio" name="edge" value="1" id="edge1" <?php if (!isset($_SESSION['edge']) || (isset($_SESSION['edge']) && $_SESSION['edge'] == 1)) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="edge" value="0" id="edge2" <?php if (isset($_SESSION['edge']) && $_SESSION['edge'] == 0) echo 'checked'; ?>> No
                                        </td>
                                        <td>
                                            Num Boxes:<br/>
                                            X-Axis: <input type="number" name="num_boxes_x" min="1" max="24" step="1" value="<?php if (isset($_SESSION['num_boxes_x']) && $_SESSION['num_boxes_x'] != 0) echo $_SESSION['num_boxes_x']; else echo '16'; ?>"/><br/>
                                            Y-Axis: <input type="number" name="num_boxes_y" min="1" max="24" step="1" value="<?php if (isset($_SESSION['num_boxes_y']) && $_SESSION['num_boxes_y'] != 0) echo $_SESSION['num_boxes_y']; else echo '16'; ?>"/>
                                        </td>
                                        <td>
                                            Show Values:<br/>
                                            <input type="radio" name="show_values" value="1" <?php if (!isset($_SESSION['show_values']) || (isset($_SESSION['show_values']) && $_SESSION['show_values'] == 1)) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="show_values" value="0" <?php if (isset($_SESSION['show_values']) && $_SESSION['show_values'] == 0) echo 'checked'; ?>> No
                                        </td>    
                                    </tr>
                                </tbody>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>Distances:</strong>
                                        </td>
                                        <td>
                                            Show Distance Averages:<br/>
                                            <input type="radio" name="show_bar_graph" value="1" <?php if (!isset($_SESSION['show_bar_graph']) || (isset($_SESSION['show_bar_graph']) && $_SESSION['show_bar_graph'] == 1)) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="show_bar_graph" value="0" <?php if (isset($_SESSION['show_bar_graph']) && $_SESSION['show_bar_graph'] == 0) echo 'checked'; ?>> No
                                        </td>
                                        <td>
                                            Range of Distances:<br/>
                                            0 to <input type="number" name="bar_range" min="1000" max="5000" step="100" value="<?php if (isset($_SESSION['bar_range']) && $_SESSION['bar_range'] != 0) echo $_SESSION['bar_range']; else echo '2000'; ?>"/>
                                        </td>
                                        <td>
                                            Distance Bin Size: <br/>
                                            <input type="number" name="graph_bin_size" min="10" max="1000" step="5" value="<?php if (isset($_SESSION['graph_bin_size']) && $_SESSION['graph_bin_size'] != 0) echo $_SESSION['graph_bin_size']; else echo '100'; ?>"/>
                                        </td>
                                        <td>
                                            Range of Next Neighbor Distances:<br/>
                                            0 to <input type="number" name="nn_bar_range" min="100" max="500" step="10" value="<?php if (isset($_SESSION['nn_bar_range']) && $_SESSION['nn_bar_range'] != 0) echo $_SESSION['nn_bar_range']; else echo '200'; ?>"/>
                                        </td>
                                        <td>
                                            Next Neighbor Distance Bin Size: <br/>
                                            <input type="number" name="nn_graph_bin_size" min="1" max="100" step="1" value="<?php if (isset($_SESSION['nn_graph_bin_size']) && $_SESSION['nn_graph_bin_size'] != 0) echo $_SESSION['nn_graph_bin_size']; else echo '10'; ?>"/>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>General</strong>
                                        </td>
                                        <td>
                                            Count Outer Trichomes:<br/>
                                            <input type="radio" name="count_outer" value="1" onClick="document.getElementById('edge2').checked = true;document.getElementById('edge1').disabled = true;document.getElementById('edge2').disabled = true;" <?php if (isset($_SESSION['outline']) && $_SESSION['outline'] == 1) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="count_outer" value="0" onClick="document.getElementById('edge1').disabled = false;document.getElementById('edge2').disabled = false;" <?php if (!isset($_SESSION['count_outer']) || (isset($_SESSION['count_outer']) && $_SESSION['count_outer'] == 0)) echo 'checked'; ?>> No
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <button type="Submit">Analyze Selected</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>

                        <br/>
                        <div id="details"></div></div>
                </div>
            </div>
            <div id="push"></div>
        </div>
        <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
            <br/><br/><span>Email Us At: <a href="admin@trichomenet.com">admin@TrichomeNet.com</a></span>
        </div>
    </body>
    <div id="overlay">
        <div>
            <p><b>It Appears You Have No Genotypes<b/><br/><br/>
                    You Must Add The Genotypes You Are Working With
                    Before You Can Use Any Other Pages!</p>
            <button type="button" onClick="overlay();window.location = './addGenotypes.php';">Take Me To GenoType Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick='overlay();'>Ignore</button>
        </div>
    </div>
</html>