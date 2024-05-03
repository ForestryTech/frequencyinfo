<?php
    
    class Frequency {
        public $Rx;
        public $Tx;
        public $RxCtcss;
        public $TxCtcss;
        public $zones;
        public $zoneCount;

        public function __construct() {
            $this->zoneCount = 1;
            $this->zones = array();
        }

        public function AddZone($zoneInfo) {
            array_push($zones, $zoneInfo);
        }

        public function OutHTML()
        {
            $outhtml = "<div class='frequency'><div class='rxtx_container'><span class='rxtx_head'>RX:</span><span class='rxtx'>" . $this->Rx . "</span><span class='rxtx_head'>TX:</span><span class='rxtx'>" . $this->Tx . "</span></div>";
            $outhtml .= "<div class='frequency_table_div'>\n";
            $outhtml .= "<table class='frequency_table'>\n<tr>\n<th><span class='table_headers'>Zone Number</span></th>\n";
            $outhtml .= "<th><span class='table_headers'>Zone Name</span></th>\n";
            $outhtml .= "<th><span class='table_headers'>Channel</span></th>\n";
            $outhtml .= "<th><span class='table_headers'>Display</span></th>\n";
            $outhtml .= "</tr>\n";
            foreach($this->zones as $z) {

                $outhtml .= "<tr>\n";
                $outhtml .= "<td><span class='zone_number'>" . $z->ZoneNumber . "</span></td>\n";
                $outhtml .= "<td><span class='zone_name'>" . $z->ZoneName . "</span></td>\n";
                $outhtml .= "<td><span class='zone_channel'>" . $z->Ch . "</span></td>\n";
                $outhtml .= "<td><span class='zone_display'>" . $z->Display . "</span></td>\n";
                $outhtml .= "</tr>";
            }
            $outhtml .= "</table>\n</div></div><hr>\n"; 
            return $outhtml;
        }
        
    }
?>