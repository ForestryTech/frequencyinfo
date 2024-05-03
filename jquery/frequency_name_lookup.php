<?php
    // This script needs looked at. Currently Chrome will not display all of the data retrieved for the db unless rows are limited. 60 worked, but was slow, 30 did good.
    $config = parse_ini_file("../config/config.ini");
    $name_string = "";
    $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);
    $name = "";
    $name = strtoupper($_POST["content"]);

    $frequency_names = array();
    /*
        Had to limit the number of item returned for chrome. When returning all items browsher was crasing. Destop and mobile(Galaxy S8, Pixel 2XL) 40 seems to work.
        Changed to have the php script check if the userAgent string contains Chrome. Edge has the Chrome string in it. It does behave like Chrome, actually worse.
    */
    if(strpos($_SERVER['HTTP_USER_AGENT'], "Chrome") !== false) {
        $sql = "SELECT DISTINCT display FROM zone WHERE display LIKE '%{$name}%' ORDER BY display LIMIT 40";
    } else {
        $sql = "SELECT DISTINCT display FROM zone WHERE display LIKE '%{$name}%' ORDER BY display";
    }
    
    
    
    $query = $conn->prepare($sql);
    
    if(!$query->execute()) {
        // throw excetpion
        echo "<option value=''>NO DATA</option>";
        return;
    }

    $result = $query->get_result();
    while($row = $result->fetch_array()) {
        $name_string .= "<option value='" . $row['display'] . "'>" . $row['display'] . "</option>";
    }

    echo $name_string;
?>