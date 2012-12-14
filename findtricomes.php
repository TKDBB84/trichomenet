<?php 
if(!isset($_SESSION)) session_start();
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

$has_tip = array();
$leaf_id = $_GET['leaf_id'];

$stmt_get_leaf_details = $pdo_dbh->prepare("SELECT file_name,tip_x,tip_y FROM `leafs` WHERE leaf_id = :leaf_id");
$stmt_get_leaf_details->bindValue(':leaf_id', $leaf_id, PDO::PARAM_INT);
$stmt_get_leaf_details->execute();

$row = $stmt_get_leaf_details->fetch(PDO::FETCH_ASSOC);
if(is_null($row['tip_x']) || is_null($row['tip_y'])){
    $has_tip['has'] = false;
}else{
    $has_tip['has'] = true;
    $has_tip['x'] = $row['tip_x'];
    $has_tip['y'] = $row['tip_y'];
}
$file_name = $row['file_name'];
$filepath = './pics/'.$row['file_name'].'.jpg';
list($width, $height, $type, $attr) = getimagesize($filepath);
$stmt_get_leaf_details->closeCursor();

$first_leaf = false;
$stmt_count_leafs = $pdo_dbh->prepare("SELECT count(leaf_id) as cnt FROM `leafs` WHERE `owner_id` = :user_id");
$stmt_count_leafs->bindValue(':user_id',$user_id,PDO::PARAM_INT);
$stmt_count_leafs->execute();
$result = $stmt_count_leafs->fetch(PDO::FETCH_ASSOC);
if($result['cnt'] == 1){
    $first_leaf = true;
}

$stmt_get_leaf_cords = $pdo_dbh->prepare("Select `xCord`,`yCord`,`cord_type` FROM `cords` WHERE `fk_leaf_id` = :leaf_id");
$stmt_get_leaf_cords->bindValue(':leaf_id', $leaf_id, PDO::PARAM_INT);
$stmt_get_leaf_cords->execute();

$xCordsOutter = array();
$yCordsOutter = array();
$cordType = array();
while($row = $stmt_get_leaf_cords->fetch(PDO::FETCH_ASSOC)){
    $xCordsOutter[] = $row['xCord'];
    $yCordsOutter[] = $row['yCord'];
    $cordType[] = $row['cord_type'];
}
$stmt_get_leaf_cords->closeCursor();
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
        $("#next_button_div").hide();
        $('#del').css({'-webkit-appearance':'checkbox','-moz-appearance':'checkbox','-ms-appearance':'checkbox','-o-appearance':'checkbox'});
        <?php
            if($first_leaf){
                echo 'overlay("first_leaf");';
            }
        ?>
        sessionStorage.clear();
        sessionStorage.setItem('option','outter');
        <?php
           for($i = 0 ; $i < count($xCordsOutter) ; $i++){
               echo 'addPoint(',$xCordsOutter[$i],',',$yCordsOutter[$i],',\'',$cordType[$i],'\');';
           }
           if($has_tip['has']){
               echo 'addPoint(',$has_tip['x'],',',$has_tip['y'],',\'tip\');';
           }
        ?>
          
          
          $('#auto_mark').on('click',function(){
            var noise = $('#txt').val();
            var confm = false;
            if(sessionStorage.length > 1)
                confm = confirm("This Will Clear All Points,\nDo You Want To Contiune?");
            else confm = true;
            if(confm){
                loading(true);
                noise = (noise * -1) + 255;
                if(noise == 0) noise = 1;
                var canvas = document.getElementById("myCanvas");
                var context = canvas.getContext("2d");
                context.clearRect(0, 0, canvas.width, canvas.height);
                var outter_x = new Array();
                var outter_y = new Array();
                var tip_x = -1;
                var tip_y = -1;
                var len = (sessionStorage.length-1) / 3;
                for(var i=0;i<len;i++){
                    var namX = 'X'+i.toString();
                    var namY = 'Y'+i.toString();
                    var typeName = 'type'+i.toString();
                    var xCord = sessionStorage.getItem(namX);
                    var yCord = sessionStorage.getItem(namY);
                    var typeName = sessionStorage.getItem(typeName);
                    if(typeName == 'outter'){
                        outter_x.push(xCord);
                        outter_y.push(yCord);
                    }else if(typeName == 'tip'){
                        tip_x = xCord;
                        tip_y = yCord;
                    }
                }
                sessionStorage.clear();
                sessionStorage.setItem('option','outter');


                $.post("ajaxfindcords.php",{'noise':noise,'curr_file':"<?php echo $file_name; ?>"},function(data){
                    loading(false);
                    var found_points = data;
                    if(tip_x != -1 && tip_y != -1){
                        addPoint(tip_x,tip_y,'tip');
                    }
                    for(j=0; j<outter_x.length;j++){
                        addPoint(outter_x[j],outter_y[j],'outter');
                    }
                    for(i=0; i<found_points.length; i++) {
                        var point = found_points[i];
                        var do_point_add = true;

                        for(j=0; j<outter_x.length && do_point_add;j++){
                            if( getDistance(point.x,point.y,outter_x[j],outter_y[j]) <= 10 ){
                                do_point_add = false;
                            }
                        }
                        if(do_point_add)
                            addPoint(point.x,point.y,'auto');
                    }
                    $('#tip').prop('disabled',false);
                },'json');
            }
          });
        
        
        $('#btn_clear').on('click',function(){
             clearcanvas(false);
        });
        
        $('#btn_save').on('click',function(){
            var len = sessionStorage.length;
            var max = (len - 1) / 3;
            var foundtip = false;
            var allX = new Array();
            var allY = new Array();
            var allType = new Array();
            for(var i=0;i<=max;i++){
                var namX = 'X'+i.toString();
                var namY = 'Y'+i.toString();
                var typeName = 'type'+i.toString();
                var xCord = sessionStorage.getItem(namX);
                var yCord = sessionStorage.getItem(namY);
                var Type = sessionStorage.getItem(typeName);
                    allX[i] = xCord;
                    allY[i] = yCord;
                    allType[i] = Type;
                if(Type == 'tip'){
                    foundtip = true;
                }
            }
            if(!foundtip){
                alert("You Must Mark A Tip Before Saving!");
                return false;
            }
            var phpX = allX.toString();
            var phpY = allY.toString();
            var phpType = allType.toString();
            //sendData(phpX,phpY,phpType);
            $("#csv").html('');
            $.post('saveLocations.php',{'Xdata':phpX,'Ydata':phpY,'Typedata':phpType,'leaf_id':"<?php echo $leaf_id; ?>"},function(data){
                $("#csv").html(data);
            },'text');
        });
        
        $('#fiji_info').on('click',function(){
            window.open('./fijiinfo.html','','width=375, height=350, left=200, top=200, screenX=200, screenY=200');
            return false;
        });
        
        $('#rng').on('change',function(){
            $('#txt').val($('#rng').val());
        });
        
        $('#myCanvas').on('click',function(event){
            var type;
            if($('#inner').is(':checked')){
                type = 'inner';
            }else if($('#outter').is(':checked')){
                type = 'outter';
            }else if($('#tip').is(':checked')){
                type = 'tip';
                $('#tip').prop('disabled',true);
                $('#outter').prop('checked',true);
            }else if(!$('#del').is(':checked')){
                alert('You Must Select A Type Of Trichome');
                return;
            }
            var c=document.getElementById("myCanvas");
            var rect = c.getBoundingClientRect();
            var x = Math.round(event.clientX - rect.left);
            var y = Math.round(event.clientY - rect.top);

            var removed = -1;
            var len = (sessionStorage.length-1) / 3;
            if($('#del').is(':checked')){

                for(var i=0;i<len;i++){
                    var namX = 'X'+i.toString();
                    var namY = 'Y'+i.toString();
                    var typeName = 'type'+i.toString();
                    var xCord = sessionStorage.getItem(namX);
                    var yCord = sessionStorage.getItem(namY);
                    var typeName = sessionStorage.getItem(typeName);
                    if( (+x >= +(+xCord - 5) &&  +x <= +(+xCord + 5)) && (+y >= +(+yCord - 5) &&  +y <= +(+yCord + 5) )){
                        if(typeName == 'tip'){
                            $('#tip').prop('disabled',false);
                        }
                        sessionStorage.removeItem(namX);
                        sessionStorage.removeItem(namY);
                        sessionStorage.removeItem(typeName);
                        removed = i;
                    }                
                }
                if(removed != -1){
                    var newCount = 0;
                    var allTypes = new Array();
                    var allX = new Array();
                    var allY = new Array();
                    for(var i=0;i<len;i++){
                        if(i != removed){
                            var namX = 'X'+i.toString();
                            var namY = 'Y'+i.toString();
                            var typeName = 'type'+i.toString();
                            var xCord = sessionStorage.getItem(namX);
                            var yCord = sessionStorage.getItem(namY);
                            var typeT = sessionStorage.getItem(typeName);
                            allTypes[newCount] = typeT;
                            allX[newCount] = xCord;
                            allY[newCount++] = yCord;
                        }
                    }
                    clearcanvas(true);
                    for(var i=0;i<len-1;i++){
                        addPoint(allX[i],allY[i],allTypes[i]);
                    }
                }
            }else{
                for(var i=0;i<len;i++){
                    var namX = 'X'+i.toString();
                    var namY = 'Y'+i.toString();
                    var typeName = 'type'+i.toString();
                    var xCord = sessionStorage.getItem(namX);
                    var yCord = sessionStorage.getItem(namY);
                    var typeName = sessionStorage.getItem(typeName);
                    if( (+x >= +(+xCord - 5) &&  +x <= +(+xCord + 5)) && (+y >= +(+yCord - 5) &&  +y <= +(+yCord + 5) )){
                        sessionStorage.removeItem(namX);
                        sessionStorage.removeItem(namY);
                        sessionStorage.removeItem(typeName);
                        removed = i;
                    }
                }
                if(removed != -1){
                    var newCount = 0;
                    var allTypes = new Array();
                    var allX = new Array();
                    var allY = new Array();
                    for(var i=0;i<len;i++){
                        if(i != removed){
                            var namX = 'X'+i.toString();
                            var namY = 'Y'+i.toString();
                            var typeName = 'type'+i.toString();
                            var xCord = sessionStorage.getItem(namX);
                            var yCord = sessionStorage.getItem(namY);
                            var typeT = sessionStorage.getItem(typeName);
                            allTypes[newCount] = typeT;
                            allX[newCount] = xCord;
                            allY[newCount++] = yCord;
                        }
                    }
                    clearcanvas(true);
                    for(var i=0;i<len-1;i++){
                        addPoint(allX[i],allY[i],allTypes[i]);
                    }
                }
                addPoint(x,y,type);
            }
        });
    });
    
    function loading(swt){
        var canvas = document.getElementById("myCanvas");
        if(swt){
            canvas.height = 500;
            canvas.width = 500;
            canvas.style.backgroundImage="url('./pics/analyzing.gif')";
        }else{
            canvas.height = <?php echo $height; ?>;
            canvas.width = <?php echo $width; ?>;
            canvas.style.backgroundImage="url('<?php echo $filepath; ?>')";
        }
    }
      
    function getDistance(x1,y1,x2,y2){
        var xs = 0;
        var ys = 0;
        xs = x2 - x1;
        xs = xs * xs;
        ys = y2 - y1;
        ys = ys * ys;
       return Math.sqrt( xs + ys );
    }

    function clearcanvas(is_confirmed){
        var confm = true;
        if(!is_confirmed){
            confm = confirm("Are You Sure You\n\nWant To Clear?")}
        if(confm){
            var option = sessionStorage.getItem('option');
            sessionStorage.clear();
            sessionStorage.setItem('option',option);
            var c=document.getElementById("myCanvas");
            var ctx=c.getContext("2d");
            ctx.save();
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, c.width, c.height);
            ctx.restore();
            $('#tip').prop('disabled',false);
        }
    }
    
    function addPoint(x,y,type){
        var c=document.getElementById("myCanvas");
        var ctx=c.getContext("2d");
        if(type === 'inner'){
            ctx.strokeStyle = '#00f';
        }else if( type === 'outter'){
            ctx.strokeStyle = '#f00';
        }else if( type === 'auto'){
            ctx.strokeStyle = '#0ff';
        }else if( type === 'tip'){
            ctx.strokeStyle = '#0f0';
            $("#tip").prop('disabled',true);
        }else{
            alert("An Error Has Occured");
            return;
        }
        ctx.lineWidth = 2;
        ctx.beginPath();
        //arc(x,y,r,startAngle,EndAngle,clockwise)
        ctx.arc(x,y,5,0,Math.PI*2,true);
        ctx.closePath();
        ctx.stroke();
        var i = (sessionStorage.length - 1 )/ 3;
        var key = "X"+i;
        sessionStorage.setItem(key, x);
        var key = "Y"+i;
        sessionStorage.setItem(key, y);
        var key = "type"+i;
        sessionStorage.setItem(key, type);
    }
    
    function walkthrough(step){
        var outline_on = {'outline':'3px dotted red'};
        var outline_off = {'outline':'none'};
        switch(step){
            case 1:
                $('#next_button_div').show();
                $('#tip_div').css(outline_on);
                $('#outter_div').css(outline_off);
                $('#inner_div').css(outline_off);
                $('#btn_stp2').show();
                $('#btn_stp3').hide();
                $('#btn_stp4').hide();
                overlay();
                overlay("first_step");
                break;
            case 2:
                $('#tip_div').css(outline_off);
                $('#outter_div').css(outline_on);
                $('#inner_div').css(outline_off);
                $('#btn_stp2').hide();
                $('#btn_stp3').show();
                $('#btn_stp4').hide();
                overlay("second_step");
                break;
            case 3:
                $('#tip_div').css(outline_off);
                $('#outter_div').css(outline_off);
                $('#inner_div').css(outline_on);
                $('#btn_stp2').hide();
                $('#btn_stp3').hide();
                $('#btn_stp4').show();
                overlay("third_step");
                break;
           case 4:
                $('#next_button_div').hide();
                $('#tip_div').css(outline_off);
                $('#outter_div').css(outline_off);
                $('#inner_div').css(outline_off);
                $('#btn_stp2').hide();
                $('#btn_stp3').hide();
                $('#btn_stp4').hide();
                overlay("forth_step");
                break;
        }
    }
    
    
    function overlay(arg){
        var e_overlay = $("#overlay");
        if(e_overlay.is(':visible')){
            if($.browser.msie && parseInt($.browser.version) < 9)
                $('html').css('overflow','auto');
            $('body').css('overflow','auto').css('padding-right','0');
        }else{
            $("html,body").animate({scrollTop: 0});
            switch(arg){
                case "first_leaf":
                    e_overlay.html('<div><p><b>It Looks Like This Is Your First Leaf!<b/><br/><br/>'+
                                  'Would You Like Us To Walk You Through How '+
                                  'To Mark Trichomes </p>'+
                                  '<button type="button" onClick="walkthrough(1);">'+
                                  'Yes</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick="overlay();">No</button></div>');
                    break;
                case "first_step":
                    e_overlay.html('<div><p><b>Great!<b/><br/><br/>'+
                                  '<p>First we start by marking the `Tip` of the leaf.  '+
                                  'Be consistent as this is the point that will be used '+
                                  'to align this leaf with other leaves for analysis.</p>' +
                                  '<p>Select `Tip` from the settings below and click on the '+
                                  'area of the leaf you wish to be the tip. A green circle '+
                                  'will appear marking your selection. If you are unhappy '+
                                  'with its position, you may delete it by choosing the '+
                                  '`delete` option from the menu and selecting the mark '+
                                  'you desire to remove.</p>'+
                                  '<p>When you are satisfied with your selection, please click the [Next Step] button</p>'+
                                  '<button type="button" onClick="overlay();">OK</button></div>');
                    break;
                case "second_step":
                    e_overlay.html('<div><p><b>Good<b/><br/><br/>'+
                                  '<p>Now you will be asked to mark the marginal trichomes on your leaf.</p>'+
                                  '<p>Select `Outer` from the menu and click on the relevant trichomes. '+
                                  'These marks will appear in red and may be deleted as previously described.</p>'+
                                  '<p>It is important to mark marginal trichomes in order as they are used to '+
                                  'draw the leaf edge during analysis.  Marking trichomes out of order will '+
                                  'cause the leaf edge to appear jagged joining the trichomes in the order '+
                                  'they were clicked. If necessary, delete marked trichomes as previously described.</p>'+
                                  '<p>When you are done, click the [Next Step] button</p>'+
                                  '<button type="button" onClick="overlay();">OK</button></div>');
                    break;
                case "third_step":
                    e_overlay.html('<div><br/>'+
                                  '<p>Now you can mark laminal trichomes.</p>'+
                                  '<p>Choose `inner` from the menu and click on all '+
                                  'the laminal trichomes. Order is not important.</p>'+
                                  '<p>Alternatively, you may choose to use the auto '+
                                  'mark trichome function by choosing a sensitivity '+
                                  'level and clicking [Find Trichomes]. This will clear '+
                                  'any laminal trichomes you have chosen but will leave '+
                                  'marginal and leaf tip selections intact.</p>'+
                                  '<p>Once automatic marking has finished, you may '+
                                  'choose to add or remove trichome marks automatically '+
                                  'before saving your work.</p>'+
                                  '<button type="button" onClick="overlay();">OK</button></div>');
                    break;
                case "forth_step":
                    e_overlay.html('<div><p><b>Congratulations<b/><br/><br/>'+
                                  '<p>You have marked the trichomes in your first '+
                                  'leaf. Feel free to experiment using combinations '+
                                  'of automatic &amp; manual trichome marking at '+
                                  'different sensitivities to see what works best '+
                                  'for your workflow.</p><p>Be sure to save your work '+
                                  'by clicking the [save] button at the bottom of the page <p>'+
                                  '<p>Enjoy.</p>'+
                                  '<button type="button" onClick="overlay();">OK</button></div>');
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


<body> 
<div class="header">
            <div class="header" id="logo"></div>
            <div class="header" id="logo_text">
                <a class="header" href="#"><span>TRICHOME<span>NET</span></span></a>
                <br/>

            </div>

            <div class="linkblock">
                <table id="link_table">
                    <tr>
                        <?php include 'linktable.php'; ?>
                    </tr>
                </table>
            </div>
        </div>

        <!--<div style="height:100%; width: 100%; position: relative;">-->
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
        <div class="contents" style="margin-left: 2%;">
            <div id="contents_header">
                <b>Mark Trichomes</b>
            </div>
            <div id="main_contents" style="margin-left: 0px;">
                <div id="framed" style="margin-left: 0px; margin-right: 0px;">
                    <canvas id="myCanvas" height="<?php echo $height; ?>" width="<?php echo $width; ?>" style=" background:url(<?php echo $filepath; ?>);"></canvas>
                    <br/>
                    <?php 
                    if($first_leaf){
                        echo '<div id="next_button_div">
                                    <br/><br/>
                                    <button type="button" id="btn_stp2" onClick="walkthrough(2);">Next Step</button>
                                    <button type="button" id="btn_stp3" onClick="walkthrough(3);">Next Step</button>
                                    <button type="button" id="btn_stp4" onClick="walkthrough(4);">Next Step</button>
                                    <br/><br/>
                              </div>';
                    }
                    ?>
                    <div id="settings">
                    Automatic Marking Sensitivity:<br/>
                        -<input id="rng" type="range" min="0" max="255" value="150" step="5" style="width: <?php echo $width/2; ?>;"/>+
                        <input  id="txt" type="text" value="150" size="3" readonly/>
                        <button id="auto_mark">Auto Mark Trichomes</button>
                        <span style="vertical-align: text-top; font-size: .75em;">
                            <a href="./fijiinfo.html" id="fiji_info">
                                ?
                            </a>
                        </span>
                        <br/>
                        <div id="tip_div" style="width: 125px;"><input type="radio" id='tip' name="type" <?php if($has_tip['has']) echo 'disabled'; else echo 'checked'; ?>/><label id="lbltip" for="tip">Mark Leaf Tip</label></div>
                        <div id="outter_div" style="width: 125px;"><input type="radio" id="outter" name="type" <?php if($has_tip['has']) echo 'checked'; ?>><label id="lbloutter" for="outter">Marginal</label></div>
                        <div id="inner_div" style="width: 125px;"><input type="radio" id="inner" name="type"><label id="lblinner" for="inner">Laminal</label></div>
                        <div id="del_div" style="width: 125px;"><input type="radio" id='del' name="type"/><label id="lbldel" for="del">Delete</label></div>
                        <button id="btn_clear">Clear</button>
                        <button id="btn_save">Save</button>
                        <div id="csv"></div>
                        <em>If leaf image appears too large, you may zoom out your browser to fit better.</em>
                        <br/><br/><br/><br/><a href="./addLeafs.php">Return To Leaf List</a>
                    </div>
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