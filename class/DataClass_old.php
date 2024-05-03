<?php
    
    require 'Frequency.php';
    require 'ZoneInfo.php';
    require '/home/frequenc/config/DatabaseHelper.php';

    class DataClass {

        const CONNECTION_ERROR = 100;
        const QUERY_ERROR = 101;
        const NO_DATA = 200;
        
        private $conn;
        public $error;
        public $data;
        public $freq;
        public $zoneDisplay;
        public $frequencyId;
        public $Success;
        
        

        public function __construct() {
            $error = array();
        }
        
        public function FindFrquencyName($frequencyName) {
            $name = $frequencyName;
            if(!$this->connectToDatabase()) {
                $this->error[1] = "Could not connect to Database.";
                $this->error[0] = $this::CONNECTION_ERROR;
                $this->Success = false;
                return null;
            }
            $query = $this->conn->prepare("SELECT * FROM zone WHERE display=?");
            $query->bind_param("s", $name);
            if(!$query->execute()) {
                $this->error[0] = $this::QUERY_ERROR;
                $this->error[0] = "There was an error in the query";
                $this->Success = false;
                return false;
            }
            $result = $query->get_result();
            $rowCount = $result->num_rows;
            if($rowCount === 0) {
                $this->error[0] = $this::NO_DATA;
                $this->error[1] = "Frequency not found.";
                $this->Success = false;
                return false;
            }
            $row = $result->fetch_array();
            $this->Success = true;
            return $this->FindFrequency($row['frequency'],"id");
        }

        public function GetZone($id) {
            set_error_handler("errorHandler");
            $zoneLines = array();
            $zoneId = "";
            if(!$this->connectToDatabase()) {
                $this->error[1] = "Could not connect to Database.";
                $this->error[0] = $this::CONNECTION_ERROR;
                $this->Success = false;
                return $zoneLines; 
            }

            $query = $this->conn->prepare("SELECT zone.ch, zone.display, frequencies.rx, frequencies.rxctcss, frequencies.tx, frequencies.txctcss, zone.notes FROM zone INNER JOIN frequencies ON zone.frequency=frequencies.id WHERE zone.zoneNumber=? ORDER BY zone.ch");
            $query->bind_param("i", $zoneId);
            $zoneId = $id;

            if(!$query->execute()) {
                $this->error[0] = $this::QUERY_ERROR;
                $this->error[0] = "There was an error in the query";
                $this->Success = false;
                return $zoneLines;
            }
            $result = $query->get_result();
            $rowCount = $result->num_rows;
            if($rowCount === 0) {
                $this->error[0] = $this::NO_DATA;
                $this->error[1] = "Frequency not found.";
                $this->Success = false;
                return $zoneLines;
            }
            while($row = $result->fetch_array()) {
                $zoneLine = new ZoneLine();
                $zoneLine->Ch = $row['ch'];
                $zoneLine->Display = $row['display'];
                $zoneLine->Rx = $row['rx'];
                $zoneLine->Rxctcss = $row['rxctcss'];
                $zoneLine->Tx = $row['tx'];
                $zoneLine->Txctcss = $row['txctcss'];
                $zoneLine->Notes = $row['notes'];
                array_push($zoneLines, $zoneLine);
            }
            $this->closeDatabaseConnection();
            $this->Success = true;
            return $zoneLines;
        }
        
        public function FindFrequency($frequencyToFind, $rxtx) {
            set_error_handler("errorHandler");
            $frequencyFound = array();
            $frequency = "";
            if(!$this->connectToDatabase()) {
                $this->error[1] = "Could not connect to Database.";
                $this->error[0] = $this::CONNECTION_ERROR;
                $this->Success = false;
                return $frequencyFound;
            }
            if($rxtx === "rx") {
                $query = $this->conn->prepare("SELECT * FROM frequencies WHERE rx=?");
                $query->bind_param("s", $frequency);
            } elseif($rxtx === "tx") {
                $query = $this->conn->prepare("SELECT * FROM frequencies WHERE tx=?");
                $query->bind_param("s", $frequency);
            } else {
                $query = $this->conn->prepare("SELECT * FROM frequencies WHERE id=?");
                $query->bind_param("i", $frequency);
            }
            
            $frequency = $frequencyToFind;
            if(!$query->execute()) {
                $this->error[0] = $this::QUERY_ERROR;
                $this->error[0] = "There was an error in the query";
                $this->Success = false;
                return $frequencyFound;
            }
            $result = $query->get_result();
            $rowCount = $result->num_rows;
            if($rowCount === 0) {
                $this->error[0] = $this::NO_DATA;
                $this->error[1] = "Frequency not found.";
                $this->Success = false;
                return $frequencyFound;
            }
            while($row = $result->fetch_array()) {
                $freq = new Frequency();
                $freq->Rx = $row['rx'];
                $freq->Tx = $row['tx'];
                $freq->RxCtcss = $row['rxctcss'];
                $freq->TxCtcss = $row['txctcss'];
                $freq->zones = $this->getFrequencyDetails($row['id']);
                array_push($frequencyFound, $freq);
            }
            $this->closeDatabaseConnection();
            $this->Success = true;
            return $frequencyFound;
        }
        
        public function GetZoneList() {
            set_error_handler("errorHandler");
            $zoneList = array();
            if(!$this->connectToDatabase()) {
                $this->error[1] = "Could not connect to Database.";
                $this->error[0] = $this::CONNECTION_ERROR;
                $this->Success = false;
                return $zoneList;
            }

            $query = $this->conn->prepare("SELECT DISTINCT CONCAT(zoneNumber, ' - ', zoneName) AS zoneID FROM zone ORDER BY zoneNumber");
            if(!$query->execute()) {
                $this->error[0] = $this::QUERY_ERROR;
                $this->error[0] = "There was an error in the query";
                $this->Success = false;
                return $zoneList;
            }   
            
            $result = $query->get_result();
            $rowCount = $result->num_rows;
            if($rowCount === 0) {
                $this->error[0] = $this::NO_DATA;
                $this->error[1] = "No zones found.";
                $this->Success = false;
                return $zoneList;
            }

            while($row = $result->fetch_array()) {
                array_push($zoneList, $row['zoneID']);
            }
            
            return $zoneList;
        }


        private function getFrequencyDetails($frequencyId) {
            set_error_handler("errorHandler");
            $id = "";
            $zone = array();
            $query = $this->conn->prepare("SELECT * FROM zone WHERE frequency=?");
            
            $query->bind_param("i", $id);
            $id = $frequencyId;
            if(!$query->execute()) {
                return $zone = array();
            }
            $result = $query->get_result();
            while($row = $result->fetch_array()) {
                $freqZone = new ZoneInfo();
                $freqZone->Ch = $row['ch'];
                $freqZone->Display = $row['display'];
                $freqZone->ZoneNumber = $row['zoneNumber'];
                $freqZone->ZoneName = $row['zoneName'];
                $freqZone->Notes = $row['notes'];
                array_push($zone, $freqZone);
            }
            return $zone;
        }



        private function connectToDatabase() {
            $host = DatabaseHelper::HOST;
            $user = DatabaseHelper::USER;
            $password = DatabaseHelper::PASSWORD;
            $database = DatabaseHelper::DATABASE;
            $this->conn = new mysqli($host, $user, $password, $database);
            //$this->error[0] .= " Made past connection. ";
            if($this->conn->connect_error) {
                //echo "<br>Not Connected...<br>" . $this->conn->connect_error;
                $this->error[0] .= "Connection failed: " . $this->conn->connect_error;
                return false;
            } else {
                //$this->error[0] .= "Connection made";
                //echo "<br>Connected...<br>";
                return true;
            }
        }

        private function closeDatabaseConnection() {
            //$query->close();
            $this->conn->close();
        }

        private function disconnectFromDatabase() {
            $this->conn->close();
        }

        public function errorHandler($errno, $errstr) {
            throw new StatusException("There was an error." . $errno . " : " . $errstr);
        }
    }

    class StatusException extends Exception
    {
        // Redefine the exception so message isn't optional
        public function __construct($message, $code = 0, Exception $previous = null) {
            // some code
        
            // make sure everything is assigned properly
            parent::__construct($message, $code, $previous);
        }

        // custom string representation of object
        public function __toString() {
            return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
        }

        public function customFunction() {
            echo "A custom function for this type of exception\n";
        }
    }

    class ZoneLine {
        public $Ch;
        public $Display;
        public $Rx;
        public $Rxctcss;
        public $Tx;
        public $Txctcss;
        public $Notes;

        public function OutHTML() {
            $notes = trim($this->Notes, "\r\n");
            $outHtml = "<tr>";
            $outHtml .= "<td>" . $this->Ch . "</td>";
            $outHtml .= "<td>" . $this->Display . "</td>";
            $outHtml .= "<td>" . $this->Rx . "</td>";
            $outHtml .= "<td>" . $this->Rxctcss . "</td>";
            $outHtml .= "<td>" . $this->Tx . "</td>";
            $outHtml .= "<td>" . $this->Txctcss . "</td>";
            $outHtml .= "<td>" . ($notes === "NONE" ? "" : $notes) . "</td>";
            $outHtml .= "</tr>";
            return $outHtml;
        }
    }

?>