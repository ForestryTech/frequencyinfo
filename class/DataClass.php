<?php
    
    require 'Frequency.php';
    require 'ZoneInfo.php';
    //require 'DatabaseHelper.php';
    /*
        This class does all of the database connections and queries
        All members are public except for the conn variable which is the mysqli connection
        In the constructor only the error array is initialized
        The error array is used when there is an error in the database connection,
        a query error or the query did not return any data. The first element in the error array
        is assigned a constant which is the type of error, the second has a message about the error
        The Success variable can be tested to check if the query returned any data.
    */
    class DataClass {

        const CONNECTION_ERROR = 100;
        const QUERY_ERROR = 101;
        const NO_DATA = 200;
        
        private $conn;
        public $error;
        //public $data;
        //public $freq;
        //public $zoneDisplay;
        //public $frequencyId;
        public $Success;
        
        

        public function __construct() {
            $error = array();
        }
        
        /*
            This function finds a frequency by its display name. The display name may appear in the database
            many times, so only the first row is needed to look up the frequency rx and tx. Example NIFC T2 may appear as 
            NIFC T2 or 45 NIFC T2 or 12 NIFC T2 depending on the zone. The numbers usually represent the channel in the zone, but
            not always. They do all point to the same frequency which is an rx 168.20000 and tx 168.20000. Once the name is matched to a 
            frequency, the function will call the FindFrequency function with the first parameter of the freqeuncy id and the second 'id'
            telling the function how to look up the frequency

            This function test if the database connection was a success
            Then if the query was a success
            then if the query returned any data

        */
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
            $this->closeDatabaseConnection();
            return $this->FindFrequency($row['frequency'],"id");
        }
        /*
            The function GetZone gets all of the frequencies and the zone data. Each line is a instance of the class ZoneInfo. After the
            class is created and values are assigned the instance is added to an array. This array is returned from this function.
            The query gets fields from zone: channel, display, and notes. From frequencies: rx, tx, rxctcss(tones), txctcss
            Joins the tables on zone.frequency(frequency id) and frequencies.id for a zone id:

            This function test if the database connection was a success
            Then if the query was a success
            then if the query returned any data
        */
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
        /*
            FindFrequency finds a frequency by quering the database using the rx, tx or id fields
            The function uses $rxtx to determine which way the database will be queried.
            When searching by rx or tx, there may be many matches since some frequencies will have
            a common rx but a different tx, such as FS BDF and FS SNF, both use 171.47500 as rx but have
            different tx. When searching by id there should only be one result. Frequencies can
            be found in multiple zones(NIFC T2 is found in most zones.). After the results are found, the function
            loops through the results and creates an instance of the class Frequency, stores its values then calls the
            function GetFreqencyDetails, which gets all of the zones the frequency is found in and stores this data in an
            array, then this is added to an array and retured.
        */
        public function FindFrequency($frequencyToFind, $rxtx) {
            set_error_handler("errorHandler");
            $frequencyFound = array();
            $frequency = "";
            $freq = "";
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
        /*
            This function queries the database and returns a list of the different zones in the database
            Returns an array of ZoneNumber - ZoneName
        */
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

        /*
            getFrequencyDetails queries the database and finds when frequencies are in zones
            After getting the results, the function loops through the results and creates an instance of
            the class ZoneInfo, assigns values then adds it to an array, which is returned to the calling function.
            The only function which should call this one is FindFrquency
        */
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


        // Database connection, 
        private function connectToDatabase() {

            $config = parse_ini_file("config/config.ini");
            //echo $config['host'] . " - " . $config['username'] . "<hr>";
            $this->conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);

            if($this->conn->connect_error) {
                $this->error[0] .= "Connection failed: " . $this->conn->connect_error;
                return false;
            } else {
                return true;
            }
        }

        private function closeDatabaseConnection() {
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
    /*
        ZoneLine is a class that is used to store the data for when user wants to view a zone.
        ZoneLine is designed for each line or row, when viewing a zone
        OutHTML outputs a row of a table in html. Starts with a <tr> and ends with a </tr>
    */
    class ZoneLine {
        public $Ch;
        public $Display;
        public $Rx;
        public $Rxctcss;
        public $Tx;
        public $Txctcss;
        public $Notes;

        public function OutHTML() {
            $notes = trim($this->Notes, "\r\n");    // needed since some of the notes fields end with \r\n
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