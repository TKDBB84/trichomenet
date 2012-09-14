<?php
if(!isset($_SESSION)) session_start();
include_once 'header.php';
include_once 'connection.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

$user_id = $_SESSION['user_id'];
if(isset($_POST) && $user_id != 0){
    if(isset($_POST['new_geno'])){
        $genotype_name = $_POST['new_genotype'];
        $stmt_new_genotype = $pdo_dbh->prepare("INSERT INTO `genotypes` (`genotype`,`owner_id`) VALUES (:genotype_name,:user_id)");
        $stmt_new_genotype->bindValue(':genotype_name', $genotype_name, PDO::PARAM_STR);
        $stmt_new_genotype->bindValue(':user_id',$user_id,PDO::PARAM_INT);
        $stmt_new_genotype->execute();
    }
}
?>


<?php

$genotypes = array();
$stmt_get_genotypes = $pdo_dbh->prepare('SELECT genotype_id,genotype FROM genotypes WHERE `owner_id` = :user_id');
$stmt_get_genotypes->bindValue(':user_id',$user_id,PDO::PARAM_INT);
$stmt_get_genotypes->execute();
$results = $stmt_get_genotypes->fetchAll(PDO::FETCH_ASSOC);

if(count($results) > 0){
    foreach($results as $row){
        $genotypes[$row['genotype_id']] = $row['genotype'];
    }
}else{
    $genotypes[0] = "No Genotypes";
}

if(isset($_POST['genotype_id'])){
    $first_key = $_POST['genotype_id'];
}else{
    reset($genotypes);
    $first_key = key($genotypes);
    reset($genotypes);
}

?>
<head>
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
                        alert('An Error Occured Please Refresh and Try Again\nYour Session May Have Timed-out');
                    }
                }
            }
            var sendstr = "?genotype_id="+geno_id;
            xmlhttp.open("GET","delLeaf.php"+sendstr,true);
            xmlhttp.send();
        }
    }
    </script>
</head>
<body>
<header><b>Genotypes</b></header>
<form action="./addGenotypes.php" method="POST">
    <div id="genotypes" style="padding-left: 20px;">
    <table border="1">
        <tr>
            <th colspan="2">Genotype</th>
            <th/>
        </tr>
    <?php if(isset($genotypes[0]) && $genotypes[0] === "No Genotypes"){
                //no genotypes
            }else{
                foreach($genotypes as $id => $genotype)
                    echo '<tr id="',$id,'">',
                            '<td colspan="2">',$genotype,'</td>',
                            '<td><button type="button" value="',$id,
                            '" onClick=',
                                ($user_id == 0)?
                                    '"alert(\'Guests Cannot Delete\');"'
                                    :
                                    '"delGenoType(',$id,');"',
                            '>Delete</button>','</td>',
                        '</tr>';
            }
    ?>
        <tr>
            <td><input type="text" name="new_genotype"/></td>
            <td><button type="Submit" name="new_geno"<?php
            if($user_id == 0) echo ' onClick="alert(\'Guests Cannot Add Data\');return false;"';
                                                      ?>>Add</button></td>
        </tr>
    </table>
    </div>
</form>
</body>


<?php

function make_thumb($src,$dest,$desired_width)
{
        
	/* read the source image */
	$source_image = imagecreatefromjpeg($src);
	$width = imagesx($source_image);
	$height = imagesy($source_image);
	
	/* find the "desired height" of this thumbnail, relative to the desired width  */
	$desired_height = floor($height*($desired_width/$width));
	
	/* create a new, "virtual" image */
	$virtual_image = imagecreatetruecolor($desired_width,$desired_height);
	
	/* copy source image at a resized size */
	imagecopyresized($virtual_image,$source_image,0,0,0,0,$desired_width,$desired_height,$width,$height);
	
	/* create the physical thumbnail image to its destination */
	imagejpeg($virtual_image,$dest);
}

?>