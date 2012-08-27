<?php

/*
 * To change this template, choose Tools | Templates
 xvfb-run fiji ./html5test/leaf.jpg -run "my test"
 * 

 *
 */
$output_f = 'tmp_'.uniqid();
$noise = $_GET['noise'];
$current_leaf = '/home/eglabdb/html5test/pics/'.$_GET['curr_file'].'.jpg';
$output_file = '/tmp/'.$output_f.'.csv';
$string = 'import ij.IJ;ip = IJ.getImage();while(null == ip){ip = IJ.getImage();}IJ.run(ip,"Find Maxima...","noise='.$noise.' output=List");IJ.saveAs("Results","'.$output_file.'");';
exec("echo '".$string."' > /usr/lib/fiji/plugins/my_test.bsh");
sleep(1);
$exec_string = '/bin/sh /var/www/tricomeproject/upload/runthis.sh '.$current_leaf;
$pid = exec($exec_string); 
while(!file_exists($output_file)){
    sleep(1);
}
exec("kill $pid");
$Cords = array();
$handle = @fopen($output_file, "r");
if ($handle) {
    $i = 0;
    $buffer = fgets($handle);
    while (($buffer = fgets($handle)) !== false) {
        $Cords[$i] = array();
        $temp = explode(",",$buffer);
        $Cords[$i]['x'] = $temp[1];
        $Cords[$i++]['y'] = substr($temp[2],0,-1);
        
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}
echo json_encode($Cords);
exec("rm $output_file");
?>
