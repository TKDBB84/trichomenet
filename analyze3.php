<?php
include_once 'connection2.php';
include_once 'header.php';
$pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);

$genotypes = array();


$result = $pdo_dbh->query("SELECT genotype_id,genotype FROM genotypes")->fetchAll(PDO::FETCH_ASSOC);
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
    function getGenotype(id){
        var genotype_id = id;
        if(genotype_id == -1 || genotype_id == '-1') return;
        var xmlhttp;
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                document.getElementById('main').innerHTML=xmlhttp.responseText;
            }
        }
        var sendstr = "?genotype_id="+genotype_id;
        xmlhttp.open("GET","analyzebygenotype3.php"+sendstr,true);
        xmlhttp.send();
    }
    
    function updateShapeImg(leaf_id,div_id){
        if(leaf_id == -1 || leaf_id == '-1') return;
        var xmlhttp;
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                document.getElementById(div_id).innerHTML=xmlhttp.responseText;
            }
        }
        var sendstr = "?leaf_id="+leaf_id;
        xmlhttp.open("GET","getLeafThumbImage.php"+sendstr,true);
        xmlhttp.send();
    }
    
    //getCordsByLeaf
    function addCordsByLeaf(leaf_id,height,width){
        if(leaf_id == -1 || leaf_id == '-1') return;
        resize(height,width);
        var xmlhttp;
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                var result = xmlhttp.responseText;
                if(result == '0' || result == 0){
                    alert("ERROR PROCESSING DATA");
                    return;
                }
                var split = result.split('~');
                var xCords = split[0].split(',');
                var yCords = split[1].split(',');
                var Types  = split[2].split(',');
                addAllPoints(xCords,yCords,Types);
            }
        }
        var sendstr = "?leaf_id="+leaf_id;
        xmlhttp.open("GET","getCordsByLeaf.php"+sendstr,true);
        xmlhttp.send();
    }
    
    function getLeafDetails(list){
        var selected = new Array();
        for (var i = 0; i < list.options.length; i++)
            if (list.options[ i ].selected)
                selected.push(list.options[ i ].value);
        if(selected.length == 0) return;
        if(selected.length == 1)
            leaf_id_list = selected[0];
        else
            leaf_id_list = selected.join();
        var xmlhttp;
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                document.getElementById('details').innerHTML=xmlhttp.responseText;
            }
        }
        var sendstr = "?leaf_id="+leaf_id_list;
        xmlhttp.open("GET","leafDetails.php"+sendstr,true);
        xmlhttp.send();
    }
    
    function moveLeaf(leaf_id){
          if( leaf_id === -1 || leaf_id === "-1") return false;
          var remove_from = document.getElementById('fl');  
          var i;
          var newOptions = new Array();
          //var elOptNew = document.createElement('option');
          for (i = remove_from.length - 1; i>=0; i--) {
            if (remove_from.options[i].selected) {
                newOptions.push(document.createElement('option'));
                var arr_index = newOptions.length - 1;
                newOptions[arr_index].text = remove_from.options[i].text;
                newOptions[arr_index].value = remove_from.options[i].value;
                remove_from.remove(i);
            }
          }
        var add_to = document.getElementById('selected');
        for(var i = 0 ; i < newOptions.length ; i++){
            try{
                add_to.add(newOptions[i], null); 
            }catch(ex){
                add_to.add(newOptions[i]);
            }
        }
        sortSelect(add_to);
        return false;
    }
    
    function moveBack(){
          var remove_from = document.getElementById('selected');  
          var i;
          var newOptions = new Array();
          //var elOptNew = document.createElement('option');
          for (i = remove_from.length - 1; i>=0; i--) {
            if (remove_from.options[i].selected) {
                newOptions.push(document.createElement('option'));
                var arr_index = newOptions.length - 1;
                newOptions[arr_index].text = remove_from.options[i].text;
                newOptions[arr_index].value = remove_from.options[i].value;
                remove_from.remove(i);
            }
          }
        var add_to = document.getElementById('fl');
        for(var i = 0 ; i < newOptions.length ; i++){
            try{
                add_to.add(newOptions[i], null); 
            }catch(ex){
                add_to.add(newOptions[i]);
            }
        }
        sortSelect(add_to);
        return false;
    }
    
    function sortSelect(selElem) {
        var tmpAry = new Array();
        for (var i=0;i<selElem.options.length;i++) {
            tmpAry[i] = new Array();
            tmpAry[i][0] = selElem.options[i].text;
            tmpAry[i][1] = selElem.options[i].value;
        }
        tmpAry.sort();
        while (selElem.options.length > 0) {
            selElem.options[0] = null;
        }
        for (var i=0;i<tmpAry.length;i++) {
            var op = new Option(tmpAry[i][0], tmpAry[i][1]);
            selElem.options[i] = op;
        }
        return;
    }
    
    function loop_select() {
        var select_box = document.getElementById('selected');
        if(select_box.options.length < 2){
            alert('You Must Select At Least 2 Leafs');
            return false;
        }
        for(i=0;i<=select_box.options.length-1;i++)
            select_box.options[i].selected = true;
        var select_box = document.getElementById('fl');
        for(i=0;i<=select_box.options.length-1;i++)
            select_box.options[i].selected = false;
        return true;
    }
  
</script>




View Genotype:
<body onload="getGenotype(<?php echo $first_key; ?>);">
<select id="geno_select" onChange="getGenotype(this.value)">
<?php foreach($genotypes as $id => $genotype)
        echo '<option value="',$id,'">',$genotype,'</option>';
?>
</select>
    <div id="main">
        
    </div>
</body>