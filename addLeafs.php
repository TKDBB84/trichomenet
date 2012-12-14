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
                '<th>Leaf</td>'.
                '<th>Leaf Details</th>'.
                '<th colspan="1"/>'.
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
            list($thumb_width, $thumb_height, $type, $attr) = getimagesize('./pics/'.$row['file_name'].'_thumb.jpg');
            
            $form_innerHTML .= '<tr id="'.$row['leaf_id'].'">'.
                    '<td style="height: '.$thumb_height.'px; width: '.$thumb_width.'px; " background="./pics/'.$row['file_name'].'_thumb.jpg" left top no-repeat>
                     <div style="position:relative";>
                        <p style="color: red; position:absolute; bottom:'.(0.125*$thumb_height).'px;">
                        '.$row['leaf_name'].'
                        </p>'.
                        '<button style="position:absolute; bottom:'.($thumb_height*-(.5)).'px; right:0; width: 100%;" type="button" name="" class="dwnld" value="'.$row['leaf_id'].'">Download Image</button></td>'.
                     '</div>'.
                    '<td align="center" ><a href="./findtricomes.php?leaf_id='.$row['leaf_id'].'">Mark Trichomes</a><br/><br/>
                        <b>Number Of Marked Trichomes:</b>
                        <br/>Marginal: '.$outer.'<br/>Laminal: '.$inner.'<br/>Auto: '.$auto.'<br/></td>'.
                    '<td><button type="button" name="del" class="del_leaf" value="'.$row['leaf_id'].'">Delete</button></tr>';
        }
    }
}
$form_innerHTML .= '<tr>'.
            '<td colspan="1">Name:<br/><input type="text" id="leafname" name="new_leaf_name" style="width:80%;"/></td>'.
            '<td colspan="1">Upload Another Leaf To This Category:<br/><input type="file" id="new_leaf_file" name="new_leaf_file"  /></td>'.
            '<td colspan="1"><button type="submit" id="add_new_leaf" name="add_new_leaf">Upload</button></td>'.
    '</tr>'.
 '</table>'.'<em>pictures MUST BE 5 MB or smaller</em>';

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
            <?php if(isset($genotypes[0]) && $genotypes[0] === "No Genotypes"){
                        echo 'overlay("no_genotypes");';
                  }elseif($active_geno === -1){
                        echo 'overlay("no_active_type");';
                  } 
                  
                  if($user_id == 0){
                      echo '$("#add_new_leaf").on("click",function(e){
                                alert(\'Guests Cannot Add Data\');
                                return false;
                            });';
                  }
            ?>
                          
                 $('.dwnld').on("click",function(e){
                    window.location = "./mkimg.php?leaf_id="+$(e.target).val();
                 });
                 
                 $('#new_leaf_file').on("change",function(e){
                    var in_val = $(e.target).val();
                    if($("#leafname").val() === ''){
                        var parts = in_val.split("/");
                        if(parts.length == 1){
                            parts = in_val.split("\\");
                        }
                        var piece_num = parts.length-1;
                        var name = parts[piece_num];
                        var name_parts = name.split(".");
                        var name_length = name_parts.length - 1;
                        name = '';
                        for(var i = 0 ; i < name_length ; i++){
                            name += name_parts[i];
                        }
                        $("#leafname").val(name);
                    }   
                });
                
                $('.del_leaf').on("click",function(e){
                    var leaf_id = $(e.target).val();
                    var conf = confirm('This Will Also Delete\nAll Trichomes\nFor This Leaf\nDo You Wish To Proceed?');
                    if(conf){
                        $.get("delLeaf.php",{"leaf_id":leaf_id},
                            function(data){
                                if(data === '1'){
                                    $('#'+leaf_id).remove();
                                }else{
                                    alert('An Error Occured Deleting The Leaf');
                                }
                            },'text');
                    }   
                })
                
                <?php
                    if($user_id == 0)
                        echo '$("#add_leaf_form").on("submit",function(e){
                                alert("Guests Cannot Add Data");
                                e.stopImmediatePropagation();
                            });';
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
                                          'You Must Add The Genotypes You Are Working With'+
                                          'Before You Can Use Any Other Pages!</p>'+
                                          '<button type="button" onClick="overlay();window.location = \'./addGenotypes.php\';">'+
                                          'Take Me To GenoType Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick="overlay();">Ignore</button></div>');
                            break;
                        case "no_active_type":
                            e_overlay.html = ('<div><p><b>It Appears You Have Not Activated A Genotype<b/><br/><br/>'+
                                          'You Must Activate A Genotype To Working With'+
                                          'Before You Can Use Any Other Pages!</p>'+
                                          '<button type="button" onClick="overlay();window.location = \'./addGenotypes.php\';">'+
                                          'Take Me To GenoType Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick="overlay();">Ignore</button></div>');
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
        <div class="contents">
            <div id="contents_header">
                2 - Upload Images/Mark Trichomes   
            </div>
            <div id="main_contents">
                <p>
                    Upload leaf images into their respective categories. You can choose to keep original file names or assign new names to each image.
                    We recommend a naming scheme which includes leaf number and replicate number.
                    After an image is uploaded, it can be used for trichome marking via the "Mark Trichomes" button. 
                    Multiple leaf images can be uploaded into a single category.
                </p>
                <p>
                    Images should be uploaded at a resolution of 1280 x 960 pixels or smaller.
                    Leaves should be horizontal, with the tip on the right hand side of the picture.
                    See <a href="./example.html" target="_blank" 
                              onClick="window.open('./example.html','','width=375, height=350, left=200, top=200, screenX=200, screenY=200');return false;">An Example Here</a>
                </p>
                <div id="framed">
                    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" id="add_leaf_form" enctype="multipart/form-data">
                        <?php if(isset($active_geno) && $active_geno != -1){ ?>
                        Leaves For Current Category: <a href="./addGenotypes.php"><?php echo $genotypes[$active_geno]; ?></a><br/><br/>
                        <div id="leafs" style="padding-left: 20px;"><?php echo $form_innerHTML; ?></div>
                        <?php }else{ echo 'No Genotype Active'; } ?>
                    </div>
                </form>
            </div>
            <div id="push"></div>
        </div>
        <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
            <br/><br/><span>Email Us At: <a href="mailto:admin@trichomenet.com">admin@TrichomeNet.com</a></span>
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