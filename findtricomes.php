<?php if(!isset($_SESSION)) session_start();
include_once 'connection.php';

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
        <title>TrichomeNet</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"> </script> 
<script type="text/javascript">
    
    $("body").on({
    // When ajaxStart is fired, add 'loading' to body class
    ajaxStart: function() { 
        $(this).addClass("loading"); 
    },
    // When ajaxStop is fired, rmeove 'loading' from body class
    ajaxStop: function() { 
        $(this).removeClass("loading"); 
    }    
    });

    function loadPage(){
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
    }
    
    function getAutoCords(noise){
        var confm = false;
        if(sessionStorage.length > 1)
            confm = confirm("This Will Clear All Points,\nDo You Want To Contiune?");
        else confm = true;
        if(confm){
            noise = (noise * -1) + 255;
            if(noise == 0) noise = 1;
            var canvas = document.getElementById("myCanvas");
            var context = canvas.getContext("2d");
            context.clearRect(0, 0, canvas.width, canvas.height);
            sessionStorage.clear();
            sessionStorage.setItem('option','outter');
            $.get("ajaxfindcords.php", { noise: noise, curr_file: "<?php echo $file_name; ?>" } ,
                function(responseText){
                    var points_returned=responseText;
                    var e = document.getElementById('shapes');
                    var found_points = jQuery.parseJSON(points_returned);
                    for(i=0; i<found_points.length; i++) {
                        var point = found_points[i];
                        addPoint(point.x,point.y,'auto');
                    }
                    document.getElementById('tip').disabled = false;
                }
            );
        }
    }
    
    function printValue(sliderID, textbox) {
            var x = document.getElementById(textbox);
            var y = document.getElementById(sliderID);
            x.value = y.value;
        }
    
  
    
    function draw(event){
        var inner = document.getElementById("inner");
        var outter = document.getElementById("outter");
        var tip = document.getElementById("tip");
        var chkbx = document.getElementById("del");
        var type;
        if(inner.checked){
            type = 'inner';
        }else if(outter.checked){
            type = 'outter';
        }else if(tip.checked){
            type = 'tip';
            tip.checked = false;
            outter.checked = true;
        }else if(!chkbx.checked){
            alert("Please Choose Inner Or Outer");
            return;
        }
        var x;
        var y;
        var c=document.getElementById("myCanvas");
        var ctx=c.getContext("2d");
        var rect = c.getBoundingClientRect();
        var x = event.clientX - rect.left;
        var y = event.clientY - rect.top;
        /*if (event.pageX || event.pageY) {
            x = event.pageX;
            y = event.pageY;
        }else{ 
            x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft; 
            y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop; 
        } 
        x -= c.offsetLeft;
        y -= c.offsetTop;*/
        
        var removed = -1;
        var len = (sessionStorage.length-1) / 3;
        if(chkbx.checked){
            
            for(var i=0;i<len;i++){
                var namX = 'X'+i.toString();
                var namY = 'Y'+i.toString();
                var typeName = 'type'+i.toString();
                var xCord = sessionStorage.getItem(namX);
                var yCord = sessionStorage.getItem(namY);
                var typeName = sessionStorage.getItem(typeName);
                if( (+x >= +(+xCord - 5) &&  +x <= +(+xCord + 5)) && (+y >= +(+yCord - 5) &&  +y <= +(+yCord + 5) )){
                    if(typeName == 'tip'){
                        document.getElementById('tip').disabled = false;
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
                clearmything(true);
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
                clearmything(true);
                for(var i=0;i<len-1;i++){
                    addPoint(allX[i],allY[i],allTypes[i]);

                }
            }
            addPoint(x,y,type);
        }
    }

    function clearmything(clrsession){
        var confm = true;
        if(clrsession == false){
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
            document.getElementById('tip').disabled = false;
        }
    }
        
    function saveIt(){
        var max = (sessionStorage.length - 1) / 3;
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
        }
        var phpX = allX.toString();
        var phpY = allY.toString();
        var phpType = allType.toString();
        sendData(phpX,phpY,phpType);
        
    }
    

    function sendData(Xdata,Ydata,Typedata){
        document.getElementById("csv").innerHTML = '';
        var xmlhttp;
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                    document.getElementById("csv").innerHTML = xmlhttp.responseText;
            }
        }
        var X_data=encodeURIComponent(Xdata);
        var Y_data=encodeURIComponent(Ydata);
        var Type_data=encodeURIComponent(Typedata);
        var leaf_id=encodeURIComponent("<?php echo $leaf_id; ?>");
        var parameters="Xdata="+X_data+"&Ydata="+Y_data+"&Typedata="+Type_data+"&leaf_id="+leaf_id;
        xmlhttp.open("POST", "saveLocations.php", true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(parameters);

    }
    
    function unSelectRadio(){
        del = document.getElementById("del");
        if(del.checked){
            outter = document.getElementById("outter");
            inner = document.getElementById("inner");
            outter.checked = false;
            inner.checked = false;
        }else{
            var which = sessionStorage.getItem('option');
            document.getElementById(which).checked = true;
        }
    }
    
    function unCheckDelete(){
        document.getElementById("del").checked = false;
        outter = document.getElementById("outter");
        inner = document.getElementById("inner");
        if(inner.checked)
            sessionStorage.setItem('option','inner');
        else
            sessionStorage.setItem('option','outter');
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
            document.getElementById('tip').disabled = true;
        }else{
            alert("An Error Has Occured");
            return;
        }
        ctx.lineWidth   = 2;
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
</script>
</head>


<body onload="loadPage();"> 
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
        <div class="contents" style="margin-left: 2%;">
            <div id="contents_header">
                <b>Mark Trichomes</b>
            </div>
            <div id="main_contents" style="margin-left: 0px;">
                <div id="framed" style="margin-left: 0px; margin-right: 0px;">
                    <canvas id="myCanvas" onmousedown="draw(event);" height="<?php echo $height; ?>" width="<?php echo $width; ?>" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px; background:url(<?php echo $filepath; ?>);"></canvas>
                    <br/>
                    Set Sensitivity:<br/>
                    <input id="rng" type="range" min="0" max="255" value="100" step="5" style="width: <?php echo $width/2; ?>;" onChange="printValue('rng','txt');"/>
                    <input  id="txt" type="text" value="100" size="3" readonly/>
                    <button onClick="getAutoCords(document.getElementById('txt').value)">Find Tricomes</button>
                    <br/>
                    <input type="radio" id='tip' name="type" onclick='unCheckDelete();' <?php if($has_tip['has']) echo 'disabled'; else echo 'checked'; ?>/>Mark Leaf Tip<br/>
                    <input type="radio" id="outter" name="type" onclick='unCheckDelete();' <?php if($has_tip['has']) echo 'checked'; ?>>Outer<br/>
                    <input type="radio" id="inner" name="type" onclick='unCheckDelete();'>Inner<br/>
                    <input type="checkbox" id='del' onclick='unSelectRadio();'/>Delete<br/>
                    <button onclick="clearmything(false);">Clear</button>
                    <button onclick="saveIt();">Save</button>
                    <div id="csv"></div>
                </div>
            </div>
            <div id="push"></div>
        </div>
        <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
            <br/><br/><span>Email Us At: <a href="admin@trichomenet.com">admin@TrichomeNet.com</a></span>
        </div>
    </body>
    <div class="modal">
</html>        