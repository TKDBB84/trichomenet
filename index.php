<?php
include_once 'header.php';
//if(!empty($_FILES)) {var_dump($_FILES);die();}
include_once 'connection2.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);


if(isset($_POST)){
    if(isset($_POST['add_new_leaf'])){
        $uploaddir = '/home/eglabdb/html5test/pics/';
        if($_FILES['new_leaf_file']['type']  == 'image/jpeg'){
            $filename = strtolower(basename($_FILES['new_leaf_file']['name'])); 
            $exts = split("[/\\.]", $filename) ; 
            $n = count($exts)-1; 
            $exts = $exts[$n];
            $filename ="img_".md5(uniqid(rand(), true));
            $uploadfile = $uploaddir . $filename;
            if (move_uploaded_file($_FILES['new_leaf_file']['tmp_name'], $uploadfile.".$exts")) {
                echo "File was successfully uploaded<br/>";
                $dest = $uploadfile."_thumb.$exts";
                make_thumb($uploadfile.".$exts",$dest,200);
                $genotype_id = $_POST['genotype_id'];
                if(empty($_POST['new_leaf_name']))
                    $leaf_name = 'New Leaf';
                else
                    $leaf_name = $_POST['new_leaf_name'];
                
                $stmt_add_leaf = $pdo_dbh->prepare("INSERT INTO leafs (`fk_genotype_id`,`leaf_name`,`file_name`) VALUES (:genotype_id,:leaf_name,:filename);");
                $stmt_add_leaf->execute(array(':genotype_id'=>$genotype_id,
                                              ':leaf_name' => $leaf_name,
                                              ':filename' => $filename));
            } else {
                echo "Error Uploading File<br/>";
                echo 'Here is some more debugging info:<br/>';
                var_dump($_FILES);
                echo '<br/>';
            }
        }else{
            echo "Only JPG & JPEG files are currently supported";
        }
    }
}




$genotypes = array();

$result = $pdo_dbh->query('SELECT genotype_id,genotype FROM genotypes')->fetchAll(PDO::FETCH_ASSOC);
if(count($result) > 0){
    foreach($result as $row){
        $genotypes[$row['genotype_id']] = $row['genotype'];
    }
}else{
    $genotypes[0] = "No Genotypes";
}
reset($genotypes);
$first_key = key($genotypes);
reset($genotypes);

?>

<script type="text/javascript">
    function getLeafs(id){
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
                document.getElementById('leafs').innerHTML=xmlhttp.responseText;
            }
        }
        var sendstr = "?genotype_id="+genotype_id;
        xmlhttp.open("GET","leafsbygenotype.php"+sendstr,true);
        xmlhttp.send();
    }
</script>

View All Leafs In Genotype:
<body onload="getLeafs(<?php echo $first_key; ?>);">
<select id="geno_select" onChange="getLeafs()">
<?php foreach($genotypes as $id => $genotype)
        echo '<option value="',$id,'">',$genotype,'</option>';
?>
</select>
<br/><br/><br/>
<form action="./index.php" method="post" enctype="multipart/form-data">
<header><b>Leafs For: </b></header>     
<div id="leafs" style="padding-left: 20px;"></div>
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