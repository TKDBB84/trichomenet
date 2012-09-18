<?php
    //$this_sites_salt = "ojX&xUV>1,%oA5*;W*cmfeazSZPUnRIsmv}^U%;K5eiPy";
    $ini_settings = parse_ini_file('./settings.ini',true);
    
    if(isset($ini_settings['Database'])){
        $DBAddress = $ini_settings['Database']['Address'];
        $DBUsername = $ini_settings['Database']['Username'];
        $DBPassword = $ini_settings['Database']['Password'];
        $DBName = $ini_settings['Database']['Database'];
        $DB_type = $ini_settings['Database']['DB_Type'];
    }else{
        die("YOU MUST SET YOUR DATABASE SETTINGS IN: ./settings.ini");
    }
    
    switch($DB_type){
        case 'mysql':
            $pdo_dbh = new PDO("mysql:host=$DBAddress;dbname=$DBName;",$DBUsername,$DBPassword);
            break;
        case 'postegress':
            $pdo_dbh = new PDO("pgsql:dbname=$DBName;user=$DBUsername;password=$DBPassword;host=$DBAddress");
            break;
        case 'sqlite':
            $pdo_dbh = new PDO("sqlite:$DBName");
            break;
        default:
            die("Database Type Not Supported");
            break;
    }
?>
