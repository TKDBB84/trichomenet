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




if (isset($_POST) && $user_id != 0) {
    if (isset($_POST['new_geno'])) {
        $genotype_name = $_POST['new_genotype'];
        $stmt_new_genotype = $pdo_dbh->prepare("INSERT INTO `genotypes` (`genotype`,`owner_id`) VALUES (:genotype_name,:user_id)");
        $stmt_new_genotype->bindValue(':genotype_name', $genotype_name, PDO::PARAM_STR);
        $stmt_new_genotype->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_new_genotype->execute();
    }
}

$genotypes = array();
$stmt_get_genotypes = $pdo_dbh->prepare('SELECT genotype_id,genotype FROM genotypes WHERE `owner_id` = :user_id');
$stmt_get_genotypes->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt_get_genotypes->execute();
$results = $stmt_get_genotypes->fetchAll(PDO::FETCH_ASSOC);

if (count($results) > 0) {
    foreach ($results as $row) {
        $genotypes[$row['genotype_id']] = $row['genotype'];
    }
} else {
    $genotypes[0] = "No Genotypes";
}

if (isset($_POST['genotype_id'])) {
    $first_key = $_POST['genotype_id'];
} else {
    reset($genotypes);
    $first_key = key($genotypes);
    reset($genotypes);
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
            function delGenoType(geno_id){
                var conf = confirm('This Will Also Delete\nAll Leaves And Trichomes\nIn This Genotype\nDo You Wish To Proceed?');
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
                                var row = document.getElementById(geno_id);
                                row.parentNode.removeChild(row);
                            }else{
                                alert('An Error Occured'+xmlhttp.responseText);
                            }
                        }
                    }
                    var sendstr = "?genotype_id="+geno_id;
                    xmlhttp.open("GET","delLeaf.php"+sendstr,true);
                    xmlhttp.send();
                }
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
    <body>
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
                    <b>Genotypes</b>
                </div>
                <div id="main_contents">
                    <p>
                        To get started using TRICHOMENET, you must first define the experimental categories under which you will be saving leaf pictures. These can be different plant genotypes, but can also be used to define other differences such as treatments or plant ages.
                    </p>
                    <form action="./addGenotypes.php" method="POST">
                        <div id="framed">
                        <div id="genotypes" style="padding-left: 20px;">
                            <table border="1">
                                <tr>
                                    <th>Genotype</th>
                                    <th/>
                                </tr>
                                <?php
                                if (isset($genotypes[0]) && $genotypes[0] === "No Genotypes") {
                                    //no genotypes
                                } else {
                                    foreach ($genotypes as $id => $genotype)
                                        echo '<tr id="', $id, '">',
                                        '<td>', $genotype, '</td>',
                                        '<td><button type="button" value="', $id,
                                        '" onClick=',
                                        ($user_id == 0) ?
                                                '"alert(\'Guests Cannot Delete\');"' :
                                                '"delGenoType(', $id, ');"',
                                        '>Delete</button>', '</td>',
                                        '</tr>';
                                }
                                ?>
                                <tr>
                                    <td><input type="text" name="new_genotype"/></td>
                                    <td><button type="Submit" name="new_geno"<?php
                                if ($user_id == 0)
                                    echo ' onClick="alert(\'Guests Cannot Add Data\');return false;"';
                                ?>>Add</button></td>
                                </tr>
                            </table>
                        </div>
                        </div>
                    </form>
                </div>
            <!--</div>-->
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
           <button type="button">Take Me To GenoType Page</button>&nbsp;&nbsp;&nbsp;<button type="button" onclick='overlay()'>Ignore</button>
     </div></div>
</html>