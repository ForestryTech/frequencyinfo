<?php
    require 'DataClass.php';

    class FrequencySearch {
        public $SearchValue;
        public $ModeValue;
        public $Data;
        public $SearchSuccess;
        public $FrequencyFound;
        public $Errors;

        public function __construct($searchValue, $modeValue) {
            $this->ModeValue = $modeValue;
            $this->SearchValue = $this->fixSearchValue($searchValue, $modeValue);
            $this->Data = new DataClass();
        }

        public function LookUpFrequency() {
            if($this->ModeValue === "name") {
                // look up by name
                $this->FrequencyFound = $this->Data->FindFrquencyName(strtoupper($this->SearchValue));
            } else {
                // look up by tx or rx
                $this->FrequencyFound = $this->Data->FindFrequency($this->SearchValue, $this->ModeValue);
            }

            if($this->Data->Success) {
                $this->SearchSuccess = true;
            } else {
                $this->SearchSuccess = false;
            }
        }

        public function OutHTML() {
            $result = "";
            if($this->SearchSuccess) {
                $result = "<hr>";
                $result .= "<div class'found-frequency'>\n";
                foreach($this->FrequencyFound as $f) {
                    $result .= $f->OutHTML();
                }
            } else {
                switch($this->Data->error[0]) {
                    case $this->Data::CONNECTION_ERROR:
                        $result = "There was an error connecting to the Database.";
                        break;
                    case $this->Data::QUERY_ERROR:
                        $result = "There was an error in the query.";
                        break;
                    case $this->Data::NO_DATA:
                        $result = $this->SearchValue . " was not found.";
                        break;
                }
            }

            return $result;
        }

        private function fixSearchValue($value, $mode) {
            if($mode === "name") {
                $value = strtoupper($value);
            } else {
                $len = strlen($value);
                if($len < 9) {
                // Search value needs to be 9 chars 171.47500
                    for($i = $len; $i < 9; $i++) {
                        $value .= "0";
                    }
                
                }
            }
            return $value;
        }

    }


?>