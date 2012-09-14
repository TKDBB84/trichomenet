<?php
if(!isset($_SESSION)) session_start();
include_once 'header.php';
include_once 'connection.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);
$user_id = $_SESSION['user_id'];

$has_tip = array();
$leaf_id = $_GET['leaf_id'];
$stmt_get_file_and_tip_by_leafid = $pdo_dbh->prepare("SELECT file_name,tip_x,tip_y FROM `leafs` WHERE leaf_id = :leaf_id");
$stmt_get_file_and_tip_by_leafid->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);
$stmt_get_file_and_tip_by_leafid->execute();
$row = $stmt_get_file_and_tip_by_leafid->fetch(PDO::FETCH_ASSOC);
if(is_null($row['tip_x']) || is_null($row['tip_y'])){
    $has_tip['has'] = false;
}else{
    $has_tip['has'] = true;
    $has_tip['x'] = $row['tip_x'];
    $has_tip['y'] = $row['tip_y'];
}

$filepath = './pics/'.$row['file_name'].'.jpg';
list($width, $height, $type, $attr) = getimagesize($filepath);
$stmt_get_file_and_tip_by_leafid->closeCursor();
//'outter' -> 1 ,    'inner' -> 2 
$stmt_get_cords_by_leafid = $pdo_dbh->prepare("Select `xCord`,`yCord`,`cord_type` FROM `cords` WHERE `fk_leaf_id` = :leaf_id");
$stmt_get_cords_by_leafid->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);
$stmt_get_cords_by_leafid->execute();
$xCordsOutter = array();
$yCordsOutter = array();
$cordType = array();
while($row = $stmt_get_cords_by_leafid->fetch(PDO::FETCH_ASSOC)){
    $xCordsOutter[] = $row['xCord'];
    $yCordsOutter[] = $row['yCord'];
    $cordType[] = $row['cord_type'];
}
$stmt_get_cords_by_leafid->closeCursor();

?>

<script LANGUAGE="JavaScript"> 
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
    
    
    function addPoint(x,y,type){
        var c=document.getElementById("myCanvas");
        var ctx=c.getContext("2d");
        if(type === 'inner'){
            ctx.strokeStyle = '#00f';
        }else if( type === 'outter'){
            ctx.strokeStyle = '#f00';
        }else if( type === 'tip'){
            ctx.strokeStyle = '#0f0';
            document.getElementById('tip').disabled = true;
        }else{
            alert("An Error Has Occured");
            return;
        }
        ctx.lineWidth   = 2;
        ctx.beginPath();
        // arc(x,y,r,startAngle,EndAngle,clockwise)
        ctx.arc(x,y,5,0,Math.PI*2,true);
        ctx.closePath();
        ctx.stroke();
        addRow('datatable',x,y,type);
        var i = (sessionStorage.length - 1 )/ 3;
        var key = "X"+i;
        sessionStorage.setItem(key, x);
        var key = "Y"+i;
        sessionStorage.setItem(key, y);
        var key = "type"+i;
        sessionStorage.setItem(key, type);
    }
    
    function draw(){
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
            alert("Please Choose Inner Or Outter");
            return;
        }
        var x;
        var y;
        var c=document.getElementById("myCanvas");
        var ctx=c.getContext("2d");
        if (event.pageX || event.pageY) {
            x = event.pageX;
            y = event.pageY;
        }else{ 
            x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft; 
            y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop; 
        } 
        x -= c.offsetLeft;
        y -= c.offsetTop;
        
        
        
        if(chkbx.checked){
            var len = (sessionStorage.length-1) / 3;
            var removed = -1;
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
            addPoint(x,y,type);
        }
    }
    
    function addRow(tableID,x,y,type) {
        var table = document.getElementById(tableID);
        var rowCount = table.rows.length;
        var row = table.insertRow(rowCount);
        var cell1 = row.insertCell(0);
        cell1.innerHTML = x.toString();
        var cell2 = row.insertCell(1);
        cell2.innerHTML = y.toString();
        var cell3 = row.insertCell(2);
        cell3.innerHTML = type.toString();
    }
    
    function clearmything(clrsession){
        var confm = true;
        if(clrsession == false){
            confm = confirm("Are You Sure You\nWant To Clear?")}
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
            var table = document.getElementById('datatable');
            table.innerHTML = "<tr><td>X cord</td><td>Y cord</td><td>Type</td></tr>";
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
    
</script>
<!DOCTYPE HTML>
<html lang="en">
    <head>
  <style type="text/css" media="screen">
    canvas, img { display:block;  border:1px solid black; }
    canvas { background:url(<?php echo $filepath; ?>) }
  </style>
</head>
<body onload="loadPage();"> 
    Please Mark (click) All Trichomes:
    
    
<canvas id="myCanvas" width="<?php echo $width; ?>" height="<?php echo $height; ?>" onmousedown="draw();"></canvas>
<img>

<input type="radio" id='tip' name="type" onclick='unCheckDelete();' <?php if($has_tip['has']) echo 'disabled'; else echo 'checked'; ?>/>Mark Leaf Tip<br/>
<input type="radio" id="outter" name="type" onclick='unCheckDelete();' <?php if($has_tip['has']) echo 'checked'; ?>>Outer<br/>
<input type="radio" id="inner" name="type" onclick='unCheckDelete();'>Inner<br/>
<input type="checkbox" id='del' onclick='unSelectRadio();'/>Delete<br/>
<button onclick="clearmything(false);">Clear</button>
<button onclick="saveIt();" <?php if($user_id == 0) echo 'disabled="disabled"';?>>Save</button>

</body>
<div id="csv"></div>
</html>    