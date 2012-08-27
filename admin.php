<?php
include_once 'header.php';
include_once 'connection2.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

if(isset($_POST)){
    if(isset($_POST['new_geno'])){
        $genotype_name = $_POST['new_genotype'];
        $stmt_new_genotype = $pdo_dbh->prepare("INSERT INTO `genotypes` (`genotype`) VALUES (:genotype_name)");
        $stmt_new_genotype->bindValue(':genotype_name', $genotype_name);
        $stmt_new_genotype->execute();
    }
}
?>


<?php

$genotypes = array();
$results = $pdo_dbh->query("SELECT genotype_id,genotype FROM genotypes")->fetchAll(PDO::FETCH_ASSOC);

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
<script type="text/javascript">
    function getShapes(id){
        if(typeof id === 'undefined'){
            var genoselect = document.getElementById('geno_select');
            var genotype_id = genoselect.options[genoselect.options.selectedIndex].value;
        }else{
            var genotype_id = id;
        }
        if(genotype_id == -1 || genotype_id == '-1') return;
        var xmlhttp;
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                document.getElementById('shapes').innerHTML=xmlhttp.responseText;
            }
        }
        var sendstr = "?genotype_id="+genotype_id;
        xmlhttp.open("GET","shapebygenotype.php"+sendstr,true);
        xmlhttp.send();
    }
</script>
<body onload="">
    
Select A Genotype: 
<select id="geno_select" onChange="getShapes()">
<?php foreach($genotypes as $id => $genotype)
        echo '<option value="',$id,'"',($id==$first_key)?'selected':'','>',$genotype,'</option>';
?>
</select>
<br/><br/><br/>
<!--<header><b>Shapes For: </b></header>
<form action="./admin.php" enctype="multipart/form-data" method="POST">
<div id="shapes" style="padding-left: 20px;"></div>
</form>
<br/><br/>-->
<header><b>Genotypes</b></header>
<form action="./admin.php" method="POST">
    <div id="genotypes" style="padding-left: 20px;">
    <table border="1">
        <tr>
            <td>Genotype ID</td>
            <td>Genotype</td>
            <td/>
        </tr>
    <?php if(isset($genotypes[0]) && $genotypes[0] === "No Genotypes"){
                //no genotypes
            }else{
                foreach($genotypes as $id => $genotype)
                    echo '<tr>',
                            '<td>',$id,'</td>',
                            '<td>',$genotype,'</td>',
                            '<td/>',
                        '</tr>';
            }
    ?>
        <tr>
            <td/>
            <td><input type="text" name="new_genotype"/></td>
            <td><button type="Submit" name="new_geno">Add</button></td>
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