<?php
if (!isset($_SESSION))    session_start();
include_once 'connection.php';
include_once 'chkcookie.php';
if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
}else if(validCookie($_COOKIE, $pdo_dbh)){
    doLogin($_COOKIE,$pdo_dbh);
    $user_id = $_SESSION['user_id'];
}else{
    $_SESSION['error'] = true;
    $_SESSION['error_text'] = '<br/>You Do Not Appear To Be Logged In<br/>
                               Or Your Sessison Has Expired';
    header('Location: ./login.php');
    die();
}

$active_geno = -1;
if(!isset($_SESSION['active_geno'])){
    $stmt_get_last_genotype = $pdo_dbh->prepare('SELECT last_active_genotype FROM `users` WHERE `user_id` = :user_id');
    $stmt_get_last_genotype->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_get_last_genotype->execute();
    $result = $stmt_get_last_genotype->fetch(PDO::FETCH_ASSOC);
    if($result['last_active_genotype'] != null)
        $active_geno = $result['last_active_genotype'];
    $_SESSION['active_geno'] = $active_geno;
}else{
    $active_geno = $_SESSION['active_geno'];
}

/*include_once 'connection.php';
$user_id = $_SESSION['user_id'];
$po_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;", $DBUsername, $DBPassword);*/

if (isset($_POST) && $user_id != 0) {
    if (isset($_POST['add_new_leaf'])) {
        $ini_settings = parse_ini_file('./settings.ini', true);
        $uploaddir = $ini_settings['Fiji']['Picture_Path'].'/';
        if ($_FILES['new_leaf_file']['type'] == 'image/jpeg') {
            $filename = strtolower(basename($_FILES['new_leaf_file']['name']));
            $exts = explode(".", $filename);
            //die(var_dump($exts));
            $n = count($exts) - 1;
            $exts = $exts[$n];
            $filename = "img_" . md5(uniqid(rand(), true));
            $uploadfile = $uploaddir . $filename;
            $full_name = $uploadfile . '.' . $exts;
            if (move_uploaded_file($_FILES['new_leaf_file']['tmp_name'], $full_name)) {
                chmod($full_name, 0644);
                $dest = $uploadfile . '_thumb.' . $exts;
                make_thumb($full_name, $dest, 200);
                chmod($dest, 0644);
                $genotype_id = $_POST['genotype_id'];
                if (empty($_POST['new_leaf_name']))
                    $leaf_name = 'New Leaf';
                else
                    $leaf_name = $_POST['new_leaf_name'];

                $stmt_add_leaf = $pdo_dbh->prepare("INSERT INTO leafs (`fk_genotype_id`,`leaf_name`,`file_name`,`owner_id`) VALUES (:genotype_id,:leaf_name,:filename,:user_id);");
                $stmt_add_leaf->execute(array(':genotype_id' => $genotype_id,
                    ':leaf_name' => $leaf_name,
                    ':filename' => $filename,
                    ':user_id' => $_SESSION['user_id']));
            } else {
                echo "Error Uploading File<br/>";
                echo 'Here is some more debugging info:<br/>';
                var_dump($_FILES);
                echo '<br/>';
            }
        } else {
            echo "Only JPG & JPEG files are currently supported<br/>";
        }
    }
}




$genotypes = array();

$stmt_get_genotypes = $pdo_dbh->prepare('SELECT genotype_id,genotype FROM genotypes WHERE `owner_id` = :user_id');
$stmt_get_genotypes->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt_get_genotypes->execute();
$result = $stmt_get_genotypes->fetchAll(PDO::FETCH_ASSOC);

if (count($result) > 0) {
    foreach ($result as $row) {
        $genotypes[$row['genotype_id']] = $row['genotype'];
    }
} else {
    $genotypes[0] = "No Genotypes";
}


$form_innerHTML = '';
if($active_geno !== -1){
    
    $stmt_get_leafs_by_genotype = $pdo_dbh->prepare('SELECT leaf_id,leaf_name,file_name FROM leafs WHERE fk_genotype_id = :genotype AND owner_id = :user_id');
    $stmt_get_leafs_by_genotype->bindValue(':genotype', $active_geno,PDO::PARAM_INT);
    $stmt_get_leafs_by_genotype->bindValue(':user_id', $user_id,PDO::PARAM_INT);

    $stmt_get_num_tricomes = $pdo_dbh->prepare('Select COUNT(xCord) as cnt FROM cords WHERE fk_leaf_id = :leaf_id AND cord_type = :type');
    $stmt_get_num_tricomes->bindParam(':leaf_id', $leaf_id, PDO::PARAM_INT);

    $stmt_get_leafs_by_genotype->execute();
    $result = $stmt_get_leafs_by_genotype->fetchAll(PDO::FETCH_ASSOC);
    $form_innerHTML .= '<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />';
    $form_innerHTML .= '<input type="hidden" name="genotype_id" value="'.$active_geno.'"/>';
    $form_innerHTML .= '<table border="1">'.
            '<tr>'.
                '<th>Leaf Name</td>'.
                '<th>Image</td>'.
                '<th>Number of<br/>Marked Tricombs</th>'.
                '<th colspan="3"/>'.
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

            $form_innerHTML .= '<tr id="'.$row['leaf_id'].'">'.
                    '<td>'.$row['leaf_name'].'</td>'.
                    '<td align="center"><img src="./pics/'.$row['file_name'].'_thumb.jpg"/></td>'.
                    '<td> Marginal: '.$outer.'<br/>Laminal: '.$inner.'<br/>Auto: '.$auto.'<br/></td>'.
                    '<td><a href="./findtricomes.php?leaf_id='.$row['leaf_id'].'">Mark<br/>Trichomes</a></td>'.
                    '<td><button type="button" name="dwnld" onClick="fetchImg('.$row['leaf_id'].');">Download<br/>Image</button></td>'.
                    '<td><button type="button" name="del" onClick=';
            if($user_id == 0)
                $form_innerHTML .= '"alert(\'Guests Cannot Delete\');"';
            else
                $form_innerHTML .= '"delLeaf('.$row['leaf_id'].');"';
            $form_innerHTML .=  '>Delete</button></tr>';

        }
    }
}
$form_innerHTML .= '<tr>'.
            '<td colspan="2">Name: <input type="text" id="leafname" name="new_leaf_name" style="width:80%;"/></td>'.
            '<td colspan="2"><input type="file" name="new_leaf_file" onChange="setName(this.value);" /></td>'.
            '<td colspan="2"><button type="submit" name="add_new_leaf"';
            if($user_id == 0)
                $form_innerHTML .= ' onClick="alert(\'Guests Cannot Add Data\');return false;"';
            $form_innerHTML .= '>Add New</button></td>'.
    '</tr>'.
 '</table>'.'<em>pictures MUST BE 5 MB or smaller</em>';

?>
<!DOCTYPE html>
<html>
    <head>
        <LINK href="./css/trichomenet.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css" media="screen"></style>
        <title>TrichomeNet</title>
        <script type="text/javascript">
            <?php if(isset($genotypes[0]) && $genotypes[0] === "No Genotypes"){
                        echo 'document.addEventListener("DOMContentLoaded", function()
                            {',
                            'overlay("no_genotypes");',
                            '}, false);';
                  }elseif($active_geno === -1){
                      echo 'document.addEventListener("DOMContentLoaded", function()
                            {',
                            'overlay("no_active_type");',
                            '}, false);';
                  } ?>
            
            function setName(val){
                var parts = val.split("/");
                if(parts.length == 1){
                    parts = val.split("\\");
                }
                var piece_num = parts.length-1;
                var name = parts[piece_num];
                var name_parts = name.split(".");
                var name_length = name_parts.length - 1;
                name = '';
                for(var i = 0 ; i < name_length ; i++){
                    name += name_parts[i];
                }
                var txt = document.getElementById('leafname');
                if(txt.value === ''){
                    txt.value = name;
                }
                return;
            }
            
            function splitPath(str) {
                var rawParts = str.split("/"), parts = [];
                for (var i = 0, len = rawParts.length, part; i < len; ++i) {
                    part = "";
                    while (rawParts[i].slice(-1) == "\\") {
                    part += rawParts[i++].slice(0, -1) + "/";
                    }
                parts.push(part + rawParts[i]);
                }
                return parts;
            }
    
            function delLeaf(leaf_id){
                var conf = confirm('This Will Also Delete\nAll Trichomes\nFor This Leaf\nDo You Wish To Proceed?');
                if(conf){
                    var xmlhttp;
                    if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
                        xmlhttp=new XMLHttpRequest();
                    }else{// code for IE6, IE5
                        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    xmlhttp.onreadystatechange=function(){
                        if (xmlhttp.readyState==4 && xmlhttp.status==200){
                            if(xmlhttp.responseText === '1'){
                                var row = document.getElementById(leaf_id);
                                row.parentNode.removeChild(row);
                            }else{
                                alert('An Error Occured Please Refresh and Try Again\nYour Session May Have Timed-out');
                            }
                        }
                    }
                    var sendstr = "?leaf_id="+leaf_id;
                    xmlhttp.open("GET","delLeaf.php"+sendstr,true);
                    xmlhttp.send();
                }
            }
            
            function fetchImg(leaf_id){
                    var xmlhttp;
                    if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
                        xmlhttp=new XMLHttpRequest();
                    }else{// code for IE6, IE5
                        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    xmlhttp.onreadystatechange=function(){
                        if (xmlhttp.readyState==4 && xmlhttp.status==200){
                                document.getElementById('file_return').innerHTML=xmlhttp.responseText;
                        }
                    }
                var sendstr = "?leaf_id="+leaf_id;
                xmlhttp.open("GET","./fetchImg.php"+sendstr,true);
                xmlhttp.send();
            }
            
            function overlay(arg){
                var e = document.getElementById("overlay");
                if(e.style.visibility == "visible"){
                    e.style.visibility = "hidden";
                    document.body.style.overflow = 'auto';
                }else{
                    switch(arg){
                        case "no_genotypes":
                            e.innerHTML = '<div><p><b>It Appears You Have No Genotypes<b/><br/><br/>'+
                                          'You Must Add The Genotypes You Are Working With'+
                                          'Before You Can Use Any Other Pages!</p>'+
                                          '<button type="button" onClick="overlay();window.location = \'./addGenotypes.php\';">'+
                                          'Take Me To GenoType Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick="overlay();">Ignore</button></div>';
                            break;
                        case "no_active_type":
                            e.innerHTML = '<div><p><b>It Appears You Have Not Activated A Genotype<b/><br/><br/>'+
                                          'You Must Activate A Genotype To Working With'+
                                          'Before You Can Use Any Other Pages!</p>'+
                                          '<button type="button" onClick="overlay();window.location = \'./addGenotypes.php\';">'+
                                          'Take Me To GenoType Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick="overlay();">Ignore</button></div>';
                            break;
                    }
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
        <div class="contents">
            <div id="contents_header">
                <b>View All Leaves In Genotype:<br/>
                        
            </div>
            <div id="main_contents">
                <p>
                    Upload leaf images into their respective categories. You can choose to keep original file names or assign new names to each image. After an image is uploaded, it can be used for trichome detection via the "Detect Trichomes" button.
                </p>
                <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" 
                            <?php echo ($user_id == 0) ? 'onSubmit="return false;"' : ''; ?>>
                    <div id="framed">                    
                        <b>Leaves For Current <?php echo $genotypes[$active_geno]; ?> Genotype: </b>
                        <div id="leafs" style="padding-left: 20px;"><?php echo $form_innerHTML; ?></div>
                    </div>
                </form>
            </div>
            <div id="push"></div>
        </div>
        <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
            <br/><br/><span>Email Us At: <a href="admin@trichomenet.com">admin@TrichomeNet.com</a></span>
        </div>
    </body>
    <div id="file_return"></div>
    
    <div id="overlay">
    </div>
</html>


<?php

function make_thumb($src, $dest, $desired_width) {

    /* read the source image */
    $source_image = imagecreatefromjpeg($src);
    $width = imagesx($source_image);
    $height = imagesy($source_image);

    /* find the "desired height" of this thumbnail, relative to the desired width  */
    $desired_height = floor($height * ($desired_width / $width));

    /* create a new, "virtual" image */
    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

    /* copy source image at a resized size */
    imagecopyresized($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

    /* create the physical thumbnail image to its destination */
    imagejpeg($virtual_image, $dest);
}
?>