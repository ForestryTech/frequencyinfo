<?php
    
    
    class ZoneView {
        public $ZoneName;
        public $ZoneNumber;
        private $zoneLines;
        public $Data;

        public function __construct($zoneString) {
            $values = $this->getZoneName($zoneString);
            $this->ZoneName = $values[1];
            $this->ZoneNumber = $values[0];
            $this->zoneLines = array();
            $this->Data = new DataClass();
        }

        private function getZoneName($zoneString) {
            $val = explode(" - ", $zoneString);
            return $val;
        }

        private function addZoneLine($zoneLine) {
            $this->zoneLines = array_push($zoneLine);
        }

        public function GetZone() {
            $this->zoneLines = $this->Data->GetZone($this->ZoneNumber);
        }
        
        public function OutHTML() {
            $outHtml = "<div class='frequency'><div class='rxtx_container'><span class='rxtx_head'></span><span class='rxtx'>Zone:</span><span class='rxtx_head'></span><span class='rxtx'>" . $this->ZoneNumber . "-" . $this->ZoneName . "</span></div>";
            
            $outHtml .= "<div class='frequency_table_div'>\n";
            $outHtml .= "<table class='frequency_table'>";
            $outHtml .= "<tr>";
            $outHtml .= "<th><span class='table_headers'>Ch</span></th>";
            $outHtml .= "<th><span class='table_headers'>Display</span></th>";
            $outHtml .= "<th><span class='table_headers'>RX</span></th>";
            $outHtml .= "<th><span class='table_headers'>RX CTCSS</span></th>";
            $outHtml .= "<th><span class='table_headers'>TX</span></th>";
            $outHtml .= "<th><span class='table_headers'>TX CTCSS</span></th>";
            $outHtml .= "<th><span class='table_headers'>Notes</span></th></tr>";
            foreach($this->zoneLines as $z) {
                $outHtml .= $z->OutHTML();
            }

            $outHtml .= "</table>\n</div></div><hr>\n"; 
            return $outHtml;
        }
    }

    
?>