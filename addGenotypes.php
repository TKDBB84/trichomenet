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
            $(document).ready(function() {
                    $('#overlay').hide();
                    $.ajaxSetup({ cache: false });
                   
                            
                    $('#geno_table').on('click','.activate',function(e){
                       var genotype = $(e.target).val();
                       $('.activate').prop("disabled",true);
                       $.get("activateGenotype.php",{"genotype_id":genotype},
                        function(data){
                            if(data === '1'){
                                var old_row = $('#geno_table_body tr:first');
                                var new_row = $('#'+genotype);
                                if(old_row[0] !== new_row[0]){
                                    old_row.css("font-weight","normal");
                                    new_row.insertBefore(old_row);
                                    old_row.children('td').eq(0).html('<button type="button" class="activate" value="'+old_row.prop("id")+'" >Activate</button>');
                                    new_row.css("font-weight","bold");
                                    new_row.children('td').eq(0).html("ACTIVE");
                                }else{
                                    old_row.css("font-weight","bold");
                                    old_row.children('td').eq(0).html("ACTIVE");
                                }
                            }else{
                                alert('An Error Occured,<br/>has your session timed-out?');
                            }
                        $('.activate').prop("disabled",false);
                        },'text');
                    });
                            
                    $('.delete').on('click',function(e){
                        <?php 
                        if($user_id == 0)
                            echo 'alert(\'Guests Cannot Delete\');' ;
                        else
                            echo 'var conf = confirm("This Will Also Delete\nAll Leaves And Trichomes\nIn This Genotype\nDo You Wish To Proceed?");
                                  if(conf){
                                    geno_id = $(e.target).val();
                                    $.get("delLeaf.php",{"genotype_id":geno_id},
                                    function(data){
                                        if(data === "1"){
                                            $("#"+geno_id).remove();
                                        }else{
                                            alert("An Error Occured Deleting The Genotype");
                                        }
                                    },"text");
                                  }';
                       ?>
                    });
                    
                    <?php
                    if($user_id == 0){
                        echo '$("#new_geno").on("click",function(e){
                                alert("Guests Cannot Add Data");
                                e.stopImmediatePropagation();
                              });';
                    }
                    ?>
                            
                    <?php
                    if(isset($active_geno) && $active_geno !== -1)
                        echo '$("#activate_',$active_geno,'").trigger("click");';
                    ?>
                    
                });         
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
                    <b>1 - Define Categories</b>
                </div>
                <div id="main_contents">
                    <p>
                        To get started using TRICHOMENET, you must first define the experimental categories under which you will be saving leaf pictures.
                        These categories act as folders to organize your data, and can be any variable such as: genotypes, experimental treatments, developmental stages, etc...
                        <!--These can be different plant genotypes, but can also be used to define other differences such as treatments or plant ages.-->
                    </p>
                    <form action="./addGenotypes.php" method="POST">
                        <div id="framed">
                            <div style="float: right; margin-right: 50px;">
                                <p>
                                    <strong>Setting a category as active will make it available for editing and analysis in TRICHOMENET.<br/><br/>
                                        Only one category may be active at a time.</strong>
                                </p>
                            </div>
                            <div id="genotypes" style="padding-left: 20px;">
                                <table id="geno_table" border="1">
                                    <thead>
                                    <tr>
                                        <th colspan="3">Categories</th>
                                    </tr>
                                    </thead>
                                    <tbody id="geno_table_body">
                                    <?php
                                    if (isset($genotypes[0]) && $genotypes[0] === "No Genotypes") {
                                        //no genotypes
                                    } else {
                                        foreach ($genotypes as $id => $genotype)
                                            echo '<tr id="', $id, '">',
                                            '<td>','<button type="button" class="activate" id="activate_',$id,'" value="',$id,'">Activate</button>','</td>',
                                            '<td>', $genotype, '</td>',
                                            '<td><button type="button" class="delete" value="',$id,'">Delete</button>', '</td>',
                                            '</tr>',PHP_EOL;
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="2"><input type="text" name="new_genotype"/></td>
                                        <td><button type="Submit" id="new_geno" name="new_geno">Add</button></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        <!--bottom right, link when read to next step fro every page-->
                        </div>
                    </form>
                </div>
            <!--</div>-->
            <div id="push"></div>
        </div>
        <div class="footer">
            <img src="./pics/osu.png" width="100" height="100" style="float: right; margin-right: 50px; margin-top: 10px">
            <br/><br/><span>Email Us At: <a href="mailto:admin@trichomenet.com">admin@TrichomeNet.com</a></span>
        </div>
    </body>
   
<div id="overlay"></div>
</html>