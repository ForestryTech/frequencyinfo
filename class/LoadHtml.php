<?php
    

    class LoadHtml {
        public $ZoneList;
        private $data;

        public function __construct() {
            $this->ZoneList = array();
            $this->data = new DataClass();
        }

        public function GetZoneList() {
            $this->ZoneList = $this->data->GetZoneList();
        }

        public function OutSelectZone() {
            $out = "<div class='zone-select-div'>";
            $out .= "<select class='custom-select zone-select' name='zoneSelect' id='zoneSelect'>";
            foreach($this->ZoneList as $z) {
                $out .= "<option value='" . $z . "'>" . $z . "</option>";
            }
            $out .= "</select></div>";
            return $out;
        }
    }
?>
