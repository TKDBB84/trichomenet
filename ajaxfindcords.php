<?php
$ini_settings = parse_ini_file('./settings.ini',true);
if(isset($ini_settings['Fiji'])){
    $FIJI_MACRO_PATH = $ini_settings['Fiji']['Fiji_Macro_Path'];
    $Output_Dir = $ini_settings['Fiji']['CSV_Output_Dir'];
    $Shell_Path = $ini_settings['Fiji']['Shell_Path_For_Macro'];
    $LAUNCH_MACRO = $ini_settings['Fiji']['Launch_Macro_Location'];
    $PIC_PATH = $ini_settings['Fiji']['Picture_Path'];
}else{
    die("YOU MUST SET YOUR DATABASE SETTINGS IN: ./settings.ini");
}


$output_f = 'tmp_'.uniqid();
$noise = $_GET['noise'];
$current_leaf = $PIC_PATH.'/'.$_GET['curr_file'].'.jpg';
$output_file = $Output_Dir.'/'.$output_f.'.csv';
$string = 'import ij.IJ;ip = IJ.getImage();while(null == ip){ip = IJ.getImage();}IJ.run(ip,"Find Maxima...","noise='.$noise.' output=List");IJ.saveAs("Results","'.$output_file.'");';
exec("echo '".$string."' > $FIJI_MACRO_PATH");
sleep(1);
$exec_string = $Shell_Path.' '.$LAUNCH_MACRO.' '.$current_leaf;
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
