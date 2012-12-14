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

$ini_settings = parse_ini_file('./settings.ini',true);
$HEAT_MAP_COLORS = $ini_settings['HeatMap']['HeatMap_Colors'];

if(!isset($_SESSION['HEAT_MAP_VALUES'])){
    $HEAT_MAP_VALUES = $ini_settings['HeatMap']['HeatMap_MaxValue'];
    $_SESSION['HEAT_MAP_VALUES'] = $HEAT_MAP_VALUES;
}else{
    $HEAT_MAP_VALUES = $_SESSION['HEAT_MAP_VALUES'];
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
    $genotypes[0] = "No Categories";
}

if ($active_geno !== -1) {
    $stmt_get_cord_count_by_leafid = $pdo_dbh->prepare('SELECT count(xCord) as cnt FROM cords WHERE fk_leaf_id = :leaf_id');


    $stmt_get_leafs_by_genotype = $pdo_dbh->prepare("SELECT `leaf_id`,`leaf_name`,`file_name` FROM leafs WHERE fk_genotype_id = :genotype_id AND owner_id = :user_id ORDER BY leaf_name");
    $stmt_get_leafs_by_genotype->bindValue(':genotype_id', $active_geno, PDO::PARAM_INT);
    $stmt_get_leafs_by_genotype->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_get_leafs_by_genotype->execute();


    $stmt_get_cord_count_by_leafid->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);
    $all_leafs = array();
    while ($row = $stmt_get_leafs_by_genotype->fetch(PDO::FETCH_ASSOC)) {
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

$has_leafs = (isset($all_leafs) && count($all_leafs) !== 0);
$stmt_count_trichomes = $pdo_dbh->prepare("SELECT xCord FROM `cords` JOIN `leafs` ON fk_leaf_id = leaf_id WHERE owner_id = :user_id LIMIT 1");
$stmt_count_trichomes->bindValue(':user_id',$user_id,PDO::PARAM_INT);
$stmt_count_trichomes->execute();
$result2 = $stmt_count_trichomes->fetch(PDO::FETCH_ASSOC);
$has_cords = ($result2 !== false);
?>
<!DOCTYPE html>
<html>
    <head>
        <LINK href="./css/trichomenet.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css" media="screen"></style>
        <title>TRICHOMENET</title>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $('#overlay').hide();
            
                    
                $('.leaf_list').on("click",function(e){
                    var $list = $(e.target).parent();
                    var $options = $("option:selected",$list);
                    var selected = $options.map(function(){
                            return this.value;
                    }).get().join(",");
                    if(selected == "") return;
                    $.get("leafDetails.php",{"leaf_id":selected},
                            function(data){
                                $('#details').html(data);
                            },"html");
                });
                
                $('#add_this').on("click",function(){
                    $("#fl > option:selected").each(function(){
                        $(this).remove().appendTo("#selected");
                    });
                    var $r = $("#selected option");
                    $r.sort(function(a,b){
                        if(a.text < b.text) return -1;
                        if(a.text == b.text) return 0;
                        return 1;
                    });
                    $($r).remove();
                    $("#selected").append($($r));

                    var $r = $("#fl option");
                    $r.sort(function(a,b){
                        if(a.text < b.text) return -1;
                        if(a.text == b.text) return 0;
                        return 1;
                    });
                    $($r).remove();
                    $("#fl").append($($r));   
                    return false;
               });
                
               $('#remove_this').on("click",function(){
                    $("#selected > option:selected").each(function(){
                        $(this).remove().appendTo("#fl");
                    });
                    var $r = $("#selected option");
                    $r.sort(function(a,b){
                        if(a.text < b.text) return -1;
                        if(a.text == b.text) return 0;
                        return 1;
                    });
                    $($r).remove();
                    $("#selected").append($($r));

                    var $r = $("#fl option");
                    $r.sort(function(a,b){
                        if(a.text < b.text) return -1;
                        if(a.text == b.text) return 0;
                        return 1;
                    });
                    $($r).remove();
                    $("#fl").append($($r));   
                    return false;
               });
               
               $('.colors').on("change",function(){
                    var id_str = '#heat_val_';
                    var $e = $(id_str+'0');
                    $e.attr('min', 0)
                    var $e_p1 = $(id_str+'1');
                    var val = Math.round(+$e.val()*10)/10;
                    $e_p1.attr('min',val);
                    val = Math.round(($e_p1.val()*10))/10;
                    $e.attr('max',Math.round((+val)*10)/10);
                    for(var i = 1 ; i < <?php echo count($HEAT_MAP_VALUES)-1; ?> ; i++){
                            var $e = $(id_str+i);
                            var $e_m1 = $(id_str+(i-1));
                            var $e_p1 = $(id_str+(i+1));
                            val = Math.round(+$e.val()*10)/10;
                            $e_p1.attr('min',Math.round((+val)*10)/10);
                            $e_m1.attr('max',Math.round((+val)*10)/10);
                    }
                    $e = $(id_str + '<?php echo count($HEAT_MAP_VALUES)-1; ?>');
                    $e.attr('max',99);
                    var $e_m1 = $(id_str + '<?php echo count($HEAT_MAP_VALUES)-2; ?>');
                    val = Math.round(+$e.val()*10)/10;
                    $e_m1.attr('max',Math.round((+val)*10)/10);
               });
               
               $('#main_form').on('submit',function(event){
                    if( $("#selected > option").length < 2){
                        alert('You Must Select At Least 2 Leaves');
                        event.preventDefault();
                        return false;
                    }

                    for(var i = 0 ; i < <?php echo count($HEAT_MAP_VALUES); ?> ; i++){
                        var id_str = '#heat_val_';
                        var $n = $(id_str+i);
                        if(!jQuery.isNumeric($n.val())){
                            alert('Hat Map Color Values Must Be Numbers');
                            event.preventDefault();
                            return false;
                        }else{
                            if(i !== 0){
                                var n_m1 = parseFloat($(id_str+(i-1)).val());
                                if(parseFloat($n.val()) < n_m1){
                                    alert('Heat Map Color Values Must Be Ascending');
                                    event.preventDefault();
                                    return false;
                                }
                            }
                        }
                    }

                   var num_inputs = new Array("#boxes_1","#boxes_2","#bar_range","#graph_bin_size","#nn_bar_range","#nn_graph_bin_size") 
                   for(var i = 0 ; i < num_inputs.length ; i++){
                      if(!jQuery.isNumeric($(num_inputs[i]).val())){
                          var error;
                          switch(num_inputs[i]){
                              case "#boxes_1":
                                  error = 'Heat Map Grid Size For X-Axis ';
                                  break;
                              case "#boxes_2":
                                  error = 'Heat Map Grid Size For Y-Axis ';
                                  break;
                              case "#bar_range":
                                  error = 'All Trichomes Distance Range ';
                                  break;
                              case "#graph_bin_size":
                                  error = 'All Trichomes Distance Bin Size ';
                                  break;
                              case "#nn_bar_range":
                                  error = 'Next Neighbor Distance Range ';
                                  break;
                              case "#nn_graph_bin_size":
                                  error = 'Next Neighbor Distance Bin Size ';
                                  break;
                          }
                          error = error + 'Must Be Numeric';
                          alert(error);
                          event.preventDefault();
                          return false;
                      }
                   }

                   $("#selected > option").attr('selected',true);
                   $("#fl > option").attr('selected',false);

               });
               
               <?php
            if(isset($genotypes[0]) && $genotypes[0] === "No Genotypes"){
               echo 'overlay("no_genotypes");';
            } elseif ($active_geno === -1) {
               echo 'overlay("no_active_type");';
            } elseif((isset($has_leafs) && $has_leafs === false)){
               echo 'overlay("no_leafs");';
            }elseif(isset($has_cords) && $has_cords === false){
                echo 'overlay("no_points");';
            }elseif (isset($_SESSION['all_ids'])) {
                foreach ($_SESSION['all_ids'] as $leaf_id)
                    echo '$(\'#fl > option[value="',$leaf_id,'"]\').attr("selected",true);';
                echo '$("#add_this").trigger("click");';
                unset($_SESSION['all_ids']);
            }
            ?>
            });
                  
          function overlay(arg){
                var e_overlay = $("#overlay");
                if(e_overlay.is(':visible')){
                    if($.browser.msie && parseInt($.browser.version) < 9)
                        $('html').css('overflow','auto');
                    $('body').css('overflow','auto').css('padding-right','0');
                }else{
                    $("html,body").animate({scrollTop: 0});
                    switch(arg){
                        case "no_genotypes":
                            e_overlay.html('<div><p><b>It Appears You Have No Genotypes<b/><br/><br/>'+
                                          'You Must Add The Categories You Are Working With'+
                                          'Before You Can Use Any Other Pages!</p>'+
                                          '<button type="button" onClick="overlay();window.location = \'./addGenotypes.php\';">'+
                                          'Take Me To Category Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick="overlay();">Ignore</button></div>');
                            break;
                        case "no_active_type":
                            e_overlay.html('<div><p><b>It Appears You Have Not Activated A Category<b/><br/><br/>'+
                                          'You Must Activate A Category To Working With'+
                                          'Before You Can Use Any Other Pages!</p>'+
                                          '<button type="button" onClick="overlay();window.location = \'./addGenotypes.php\';">'+
                                          'Take Me To Category Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick="overlay();">Ignore</button></div>');
                            break;
                        case "no_leafs":
                            e_overlay.html('<div><p><b>It Appears You Have Not Add Any Leaves To This Category<b/><br/><br/>'+
                                          'You Cannot Analyze Leaves Without First Adding Them</p>'+
                                          '<button type="button" onClick="overlay();window.location = \'./addLeafs.php\';">'+
                                          'Take Me To Add Leaves Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick="overlay();">Ignore</button></div>');
                            break;
                        case "no_points":
                            e_overlay.html('<div><p><b>It Appears You Have Not Add Any Points To Any Leaves<b/><br/><br/>'+
                                          'You Cannot Analyze Leaves Without Points</p>'+
                                          '<button type="button" onClick="overlay();window.location = \'./addLeafs.php\';">'+
                                          'Take Me To Add Leaves Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick="overlay();">Ignore</button></div>');
                            break;
                        }
                    if($.browser.msie && parseInt($.browser.version) < 9)
                        $('html').css('overflow','hidden');
                    $('body').css('overflow','hidden').css('padding-right','17px');
                }
                e_overlay.toggle();
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
                <span>Step 1: Define Categories</span>
                <br/><br/>
                <span>Step 2: Upload Images/Mark Trichomes</span>
                <br/><br/>
                <span>Step 3: Analyze</span>
                <br/><br/>
                <span style="position: absolute; bottom: 0; right: 0;">
                    If you have any problems with the software, 
                      please leave any issues at: 
                      <a href="https://github.com/TKDBB84/trichomenet">
                        TRICHOMENET On Github
                      </a>
                      <br/><br/>
                </span>
            </div>
        <div class="contents">
            <div id="contents_header">
                <b>3 - Analyze: Trichome Positional Analysis</b>
            </div>
            <div id="main_contents"><br/>
                Select leaves and options for positional analysis. Only leafs in the active category may be analyzed. To analyze other categories you must activate them at <a href="./addGenotypes.php">Step 1</a>.
                <div id="framed">
                    <div id="main"><form action="alignpoints3.php" method="post" id="main_form">
                            <table rules="groups">
                                <thead>
                                    <tr>
                                        <th colspan="6">Leaf Selection</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td><strong>Available Leaves:</strong></td>
                                        <td></td>
                                        <td><strong>Selected Leaves:</strong></td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr> 
                                        <td></td>
                                        <td>
                                        </td>
                                        <td>
                                            <select name="full_list" id="fl" class="leaf_list" size="5" multiple="multiple">
                                                <?php
                                                foreach ($all_leafs as $leaf_id => $leaf) {
                                                    echo '<option value="', $leaf_id, '">', $leaf['name'], ' (', $leaf['count'], ')</option>';
                                                }
                                                ?>    
                                            </select>
                                        </td>
                                        <td align="center">
                                            <button type="button" id="add_this" name="add_this" onclick="moveLeaf(this.value);return false;"> &gt;&gt; </button><br/>
                                            <button type="button" id="remove_this" onClick="moveBack();return false;"> &lt;&lt; </button>
                                        </td>
                                        <td>
                                            <select name="all_leaf_ids[]" id="selected" class="leaf_list" size="5" multiple="multiple"></select>
                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                </tbody>
                                <thead>
                                    <tr>
                                        <th colspan="6"><br/><br/>Analysis Options<td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>General</strong>
                                        </td>
                                        <td colspan="6">
                                            Include Marginal Trichomes In Analysis:<br/>
                                            <input type="radio" name="count_outer" value="1" onClick="document.getElementById('edge2').checked = true;document.getElementById('edge1').disabled = true;document.getElementById('edge2').disabled = true;" <?php if (isset($_SESSION['outline']) && $_SESSION['outline'] == 1) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="count_outer" value="0" onClick="document.getElementById('edge1').disabled = false;document.getElementById('edge2').disabled = false;" <?php if (!isset($_SESSION['count_outer']) || (isset($_SESSION['count_outer']) && $_SESSION['count_outer'] == 0)) echo 'checked'; ?>> No
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>Heat Map</strong>
                                        </td>
                                        <td>
                                            Show Gird Lines: <br/>
                                            <input type="radio" name="outline" value="1" <?php if (isset($_SESSION['outline']) && $_SESSION['outline'] == 1) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="outline" value="0" <?php if (!isset($_SESSION['outline']) || (isset($_SESSION['outline']) && $_SESSION['outline'] == 0)) echo 'checked'; ?>> No
                                        </td>
                                        <td>
                                            Show Trichome Points: <br/>
                                            <input type="radio" name="tricomes" value="1" <?php if (!isset($_SESSION['tricomes']) || (isset($_SESSION['tricomes']) && $_SESSION['tricomes'] == 1)) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="tricomes" value="0" <?php if (isset($_SESSION['tricomes']) && $_SESSION['tricomes'] == 0) echo 'checked'; ?>> No
                                        </td>
                                        <td>
                                            Show Leaf Outline: <br/>
                                            <input type="radio" name="edge" value="1" id="edge1" <?php if (!isset($_SESSION['edge']) || (isset($_SESSION['edge']) && $_SESSION['edge'] == 1)) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="edge" value="0" id="edge2" <?php if (isset($_SESSION['edge']) && $_SESSION['edge'] == 0) echo 'checked'; ?>> No
                                        </td>
                                        <td>
                                            Grid Size:<br/>
                                            X-Axis: <input type="number" id="boxes_1" name="num_boxes_x" min="1" max="24" step="1" value="<?php if (isset($_SESSION['num_boxes_x']) && $_SESSION['num_boxes_x'] != 0) echo $_SESSION['num_boxes_x']; else echo '16'; ?>"/><br/>
                                            Y-Axis: <input type="number" id="boxes_2" name="num_boxes_y" min="1" max="24" step="1" value="<?php if (isset($_SESSION['num_boxes_y']) && $_SESSION['num_boxes_y'] != 0) echo $_SESSION['num_boxes_y']; else echo '16'; ?>"/>
                                        </td>
                                        <td>
                                            Show Local Density Values:<br/>
                                            <input type="radio" name="show_values" value="1" <?php if (!isset($_SESSION['show_values']) || (isset($_SESSION['show_values']) && $_SESSION['show_values'] == 1)) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="show_values" value="0" <?php if (isset($_SESSION['show_values']) && $_SESSION['show_values'] == 0) echo 'checked'; ?>> No
                                        </td>    
                                    </tr>
                                </tbody>
                            </table>
                            <table border="1" frame="ABOVE">
                                <tbody>
                                    <tr>
                                        <td rowspan="2">
                                            <strong>Heat Map Colors</strong>
                                        </td>
                                        <?php
                                        //$HEAT_MAP_COLORS
                                        //$HEAT_MAP_VALUES
                                            //echo '<td align="center">0</td>';
                                            echo '<td align="center">&lt;<input type="number" class="colors" name="HEAT_MAP_RANGES[0]" id="heat_val_0" value="',$HEAT_MAP_VALUES[0],'"max="',$HEAT_MAP_VALUES[1],'" min="0" step="0.1" style="width: 40px;"/></td>';
                                            $NUM_VALUES = count($HEAT_MAP_VALUES);
                                            for($i = 1 ; $i < ($NUM_VALUES-1) ; $i++){
                                                $min_value = $HEAT_MAP_VALUES[$i-1];
                                                $value = $HEAT_MAP_VALUES[$i];
                                                $max_value = $HEAT_MAP_VALUES[$i+1];
                                                echo '<td align="center">&lt;<input type="number" class="colors" name="HEAT_MAP_RANGES[',$i,']" id="heat_val_',$i,'" value="',$value,'"max="',$max_value,'" min="',$min_value,'" step="0.1" style="width: 40px;"/></td>';
                                            }
                                            echo '<td align="center">&lt;<input type="number" class="colors" name="HEAT_MAP_RANGES[',$NUM_VALUES-1,']" id="heat_val_',$NUM_VALUES-1,'" value="',$HEAT_MAP_VALUES[$NUM_VALUES-1],'"max="99" min="',$HEAT_MAP_VALUES[$NUM_VALUES-2],'" step="0.1" style="width: 40px;"/></td>';
                                        ?>
                                        <td>
                                            ++
                                        </td>
                                    </tr>
                                    <tr>
                                            <?php
                                            
                                            foreach($HEAT_MAP_COLORS as $color)
                                                echo '<td align="center"><div style="border:1px solid; height: 15px; width: 15px; background-color: ',$color,';"/></td>';
                                            ?>
                                        
                                    </tr>
                                </tbody>
                            </table>
                            <table rules="groups">
                                <thead>
                                    <tr>
                                        <th colspan="6">
                                            <br/>
                                            Distances Analysis:
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>
                                            All Trichome Distances:
                                        </th>
                                        <td colspan="2">
                                            Show DistanceGraphs:<br/>
                                            <input type="radio" name="show_bar_graph" value="1" <?php if (!isset($_SESSION['show_bar_graph']) || (isset($_SESSION['show_bar_graph']) && $_SESSION['show_bar_graph'] == 1)) echo 'checked'; ?>> Yes<br/>
                                            <input type="radio" name="show_bar_graph" value="0" <?php if (isset($_SESSION['show_bar_graph']) && $_SESSION['show_bar_graph'] == 0) echo 'checked'; ?>> No
                                        </td>
                                        <td>
                                            Distance Range:<br/>
                                            0 to <input type="number" id="bar_range" name="bar_range" min="1000" max="5000" step="100" value="<?php if (isset($_SESSION['bar_range']) && $_SESSION['bar_range'] != 0) echo $_SESSION['bar_range']; else echo '2000'; ?>"/>
                                        </td>
                                        <td>
                                            Distance Bin Size: <br/>
                                            <input type="number" id="graph_bin_size" name="graph_bin_size" min="10" max="1000" step="5" value="<?php if (isset($_SESSION['graph_bin_size']) && $_SESSION['graph_bin_size'] != 0) echo $_SESSION['graph_bin_size']; else echo '100'; ?>"/>
                                        </td>
                                        <td/>
                                    </tr>
                                </tbody>
                                <tbody>
                                    <tr>
                                        <th>
                                            Next Neighbor Distances:
                                        </th>
                                        <td>
                                            Distance Range:<br/>
                                            0 to <input type="number" id="nn_bar_range" name="nn_bar_range" min="100" max="500" step="10" value="<?php if (isset($_SESSION['nn_bar_range']) && $_SESSION['nn_bar_range'] != 0) echo $_SESSION['nn_bar_range']; else echo '200'; ?>"/>
                                        </td>
                                        <td>
                                            Distance Bin Size: <br/>
                                            <input type="number" id="nn_graph_bin_size" name="nn_graph_bin_size" min="1" max="100" step="1" value="<?php if (isset($_SESSION['nn_graph_bin_size']) && $_SESSION['nn_graph_bin_size'] != 0) echo $_SESSION['nn_graph_bin_size']; else echo '10'; ?>"/>
                                        </td>
                                        <td/>
                                        <td/>
                                        <td/>
                                    </tr>
                                </tbody>
                                    <tr>
                                        <td align="center" colspan="6"><br/>
                                            <button style="left: 500px;" type="Submit">Analyze Selected</button>
                                        </td>
                                    </tr>
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
            <br/><br/><span>Email Us At: <a href="mailto:admin@trichomenet.com">admin@TrichomeNet.com</a></span>
        </div>
    </body>
    <div id="overlay"></div>
</html>