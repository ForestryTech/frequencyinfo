<?php
    $lines = file("freq.txt");
    $config = parse_ini_file("/home/frequenc/config/config.ini");
    $i = 0;
    /*
        This php script will insert frequencies into a mysql database. It needs a file called freq.txt. This file
        was created from an Excel workbook that has the State load. Each worksheet represents a zone or group on a bendex king radio.
        There are two tables: frequencies which has the rx, tx, rxctcss, txctcss and an id, next table is zone which has a frequencyId field 
        to match the frequency in the frequencies table, channel, display, Zone name and zone number. In the freq.txt file each line has the frequecy info and which zones
        the frequecy is found in. Frequencies can be found in multiple zones. The line in the txt files is:
        rx, tx, rxctcss, txctcss, zones seperated by a ,
        zones are seperated by a *
        each zone info is seperated by a  :
    */
    $conn = new mysqli($config['host'], $config['adduser'], $config['addpassword'], $config['dbname']);
    if($conn->connect_error) {
        echo "Connection failed..";
        die;
    }
    for($i = 0; $i <= count($lines) - 1; $i++) {
    //for($i = 0; $i <= count($lines) - 1; $i++) {
        $freq = explode(",", $lines[$i]);
        echo "RX: " . $freq[0] . "   TX: " . $freq[1] . "<br>";
        $zones = explode("*", $freq[4]);
        $zoneNames = getZones($freq[4]);
        $freqId = insertFrequency($freq, $zoneNames, $conn);
        echo "Frequency ID: " . $freqId . "<br>";
        
        for($x = 0; $x <= count($zones) - 1; $x++) {
            //echo "------- " . $zones[$x] . "<br>";
            insertZoneInfo($zones[$x], $freqId, $conn);
        } 
        echo "<hr>";
    }
    $conn->close();


    function getZones($zones) {
        $zoneNumbers = "";
        $zonesArray = explode("*", $zones);
        $zoneArray = explode(":", $zonesArray[0]);
        $zoneNumbers = $zoneArray[0];
        for($i = 1; $i <= count($zonesArray) - 1; $i++) {
            $zoneArray = explode(":", $zonesArray[$i]);
            $zoneNumbers .= "*";
            $zoneNumbers .= $zoneArray[0];
        }

        return $zoneNumbers;
    }

    function insertFrequency($line, $zone, $conn) {
        $query = $conn->prepare("INSERT INTO frequencies (rx, rxctcss, tx, txctcss, zones) VALUES(?, ?, ?, ?, ?)");
        $query->bind_param("sssss", $line[0], $line[2], $line[1], $line[3], $zone);
        
        
        if(!$query->execute()) {
            echo "<h3>Could not add frequency. " . $line[0] . " - " . $line[1] . "</h3><br>";
        }
        return $conn->insert_id;
    }

    function insertZoneInfo($line, $frequncyId, $conn) {
        $fields = explode(":", $line);
        $zoneInfo = explode("-", $fields[0]);
        $query = $conn->prepare("INSERT INTO zone (frequency, ch, display, zoneNumber, zoneName, notes) VALUES(?, ?, ?, ?, ?, ?)");
        $query->bind_param("iisiss", $frequncyId, $fields[1], $fields[2], $zoneInfo[0], $zoneInfo[1], $fields[3]);

        if(!$query->execute()) {
            echo "<h3>Could not add zone info. " . $line . "</h3><br>";
        } else {
            echo "Added: " . $line . " " . $conn->insert_id . " Freq ID: " . $frequncyId . "<br>";
        }
    }
?>