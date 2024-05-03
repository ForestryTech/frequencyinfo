<?php
    
    require 'class/FrequencySearch.php';
    require 'class/Zone.php';
    require 'class/LoadHtml.php';
    set_error_handler("errorHandler");

    $message = "";        // used for displaying the results of a search. Outputted in a table surrounded by a div
    $caption = "";        // Caption for the form
    $hidden = "";         // used for hidden input on html form. Will have id=mode and name=mode and value will be the mode of the form, rx, tx, name, zone
    $rxtxHidden = "";     // used to add a class of hidden to a div that holds the input box, adding class removes from page  *
    $nameHidden = "";     // used to add a class of hidden to a div that holds the data list, adding class removes from page  ** Only one of these will be displayed at a time 
    $zoneHidden = "";     // used to add a class of hidden to a div that holds the select box, adding class removes from page *
    $mainContainer = "";  // used to set the class of the main div container. Narrow when rx, tx, name is the mode, wider when zone mode. Zone table are wider due to more columns

    if(array_key_exists("mode", $_POST)) {
      /* 
        When page loads, check if any POST data with key 'mode'. 'mode' determines what way the form was submitted.
        There are four ways: 1 by frequency rx, 2 by frequency tx, by frequency name, and zone display
        The default way the page is set up is search by frequency rx. 
        This switch will happen after a form has been submitted. Different mode will call different classes
        to search the database for its value. Each class has a method OutHTML() that will output html formatted tables.
        This will go into the $message variable and will get added to the page later.
      */
      switch($_POST['mode']) {
        case "rx":
          $searchResults = new FrequencySearch($_POST['frequency'], $_POST['mode']);
          $searchResults->LookUpFrequency();
          $message = $searchResults->OutHTML();
          break;
        case "tx":
          $searchResults = new FrequencySearch($_POST['frequency'], $_POST['mode']);
          $searchResults->LookUpFrequency();
          $message = $searchResults->OutHTML();
          break;
        case "name":
          $searchResults = new FrequencySearch($_POST['frequency_name'], $_POST['mode']);
          $searchResults->LookUpFrequency();
          $message = $searchResults->OutHTML();
          break;
        case "zone":
          $zone = new ZoneView($_POST['zoneSelect']);
          $zone->GetZone();
          $message = $zone->OutHTML();
          break;

      }
      
    }
    $getZoneList = new LoadHtml();  // This class is used to get the zones names and numbers, used for viewing zones
    $getZoneList->GetZoneList();
    $list = $getZoneList->OutSelectZone();  // Output will be a dropdown box, which is stored in $list variable

    /*
      This will set the page mode, Rx, Tx, Name, Zone
      If the array key 'mode' exists, it will be passed to the function else the default of rx will be used
    */
    SetPageMode(array_key_exists("mode", $_POST) ? $_POST["mode"] : "rx");

    
    function errorHandler($errno, $errstr) {
      global $message;
      $message = "<br>Error: " . $errno . " : " . $errstr;
    }

    function exceptionHandler($ex) {
      global $message;
      $message = "An exception occures: " . $ex;
    }



    function SetPageMode($mode) {
      global $hidden;        // used for hidden input on html form. Will have id=mode and name=mode and value will be the mode of the form, rx, tx, name, zone
      global $rxtxHidden;    // used to add a class of hidden to a div that holds the input box, adding class removes from page  *
      global $nameHidden;    // used to add a class of hidden to a div that holds the data list, adding class removes from page  ** Only one of these will be displayed at a time 
      global $zoneHidden;    // used to add a class of hidden to a div that holds the select box, adding class removes from page *
      global $caption;       // Caption for the form
      global $mainContainer; // used to set the class of the main div container. Narrow when rx, tx, name is the mode, wider when zone mode. Zone table are wider due to more columns
      /*
        switch to set the variables for the appropriate page mode. All variables will be echoed to the page as it renders.
      */
      switch ($mode) {
        case "tx":
          $caption = "Enter the TX Frequency";
          $mainContainer = 'class="container freq-container"';
          $hidden = "<input type='hidden' id='mode' name='mode' value='tx'>";
          $rxtxHidden = "";
          $nameHidden = 'class="hidden"';
          $zoneHidden = 'class="hidden"';
          break;
        case "name":
          $caption = "Enter the Frequency name";
          $mainContainer = 'class="container freq-container"';
          $hidden = "<input type='hidden' id='mode' name='mode' value='name'>";
          $rxtxHidden = 'class="hidden"';
          $nameHidden = "";
          $zoneHidden = 'class="hidden"';
          break;
        case "zone":
          $caption = "Select a Zone";
          $mainContainer = 'class="container zone-container"';
          $hidden = "<input type='hidden' id='mode' name='mode' value='zone'>";
          $rxtxHidden = 'class="hidden"';
          $nameHidden = 'class="hidden"';
          $zoneHidden = "";
          break;
        default:
          $caption = "Enter the RX Frequency";
          $mainContainer = 'class="container freq-container"';
          $hidden = "<input type='hidden' id='mode' name='mode' value='rx'>";
          $rxtxHidden = "";
          $nameHidden = 'class="hidden"';
          $zoneHidden = 'class="hidden"';
          break;
      }
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

      <title>Frequency Finder</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
      
      <style type="text/css">


        body {
            background: none;
        }
        .container {
            text-align: center;
            margin-top: 100px;
            padding: 0px;
        }

        .freq-container {
          width: 330px;
        }

        .zone-container {
          width: 700px;
        }

        .zone-select {
          width: 150px;
        }

        .rxtx_head {
            width: 10%;
            display: inline-block;
            font-size: 1.2rem;
        }

        .rxtx {
            width: 39%;
            display: inline-block;
            text-align: left;
            font-size: 1.2rem;
            font-weight: bold;
            
        }

        .frequency {
          padding-bottom: 20px;
        }
        .frequency_table {
          border: solid thin;
          border-collapse: collapse;
          width: 100%;
        }

        .frequency_table caption {
          padding-bottom: 0.3em;
        }

        .frequency_table th, .frequency_table td {
          border: solid thin;
          padding: 0.3rem;
        }

        .frequency_table td {
          white-space: nowrap;
        }

        .frequency_table th {
          font-weight: bold;
        }

        .frequency_table td {
          border-style: none solid;
          vertical-align: top;
        }

        .frequency_table th {
          padding: 0.3em;
          vertical-align: middle;
          text-align: center;
        }

        .hidden {
          display: none;
        }

        .upper {
          text-transform: uppercase;
        }

      </style>
      
  </head>
  <body>
  <ul class="nav justify-content-center">
    <li class="nav-item  rxtxname" id="rx">
      <a class="nav-link active">Find by RX</a>
    </li>
    <li class="nav-item rxtxname" id="tx">
      <a class="nav-link">Find by TX</a>
    </li>
    <li class="nav-item rxtxname" id="name">
      <a class="nav-link">Find by Name</a>
    </li>
    <li class="nav-item rxtxname" id="zone">
      <a class="nav-link">View Zone</a>
    </li>
  </ul>
    <div <?php echo $mainContainer; ?> id="main">
        <h1>Locate Frequency</h1>
        <form method="post" autocomplete="off">
          <!-- Div for label and text box for rx/tx lookup -->
            <div class="form-group" id="formDiv">
                <label id="formLabel"><?php echo $caption; ?></label>
                <div <?php echo $rxtxHidden; ?> id="byRxTx">
                  <input type="text" class="form-control" name="frequency" placeholder="eg. 170.00000, 168.20000..." id="frequencySearch" />
                </div>
                <div <?php echo $nameHidden; ?> id="byName">
                  <input list="frequency_name" name="frequency_name" class="form-control upper" placeholder="eg. NIFC T2, CDF CMD 1, R5 T5..." id="name_search" />
                  <datalist id="frequency_name">

                  </datalist>
                </div>
                <div <?php echo $zoneHidden; ?> id="viewZone">
                  <?php echo $list; ?>
                </div>
            </div>
            <!-- <input type="hidden" id="mode" name="mode" value="rx"> -->
            <?php echo $hidden; ?>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <div class="justify-content-center" id="output">
        <?php echo $message; ?>
        </div>  
    </div>
    
    <!-- jQuery first, then Bootstrap JS. -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>



    <script>
      /*
        jquery method to search the database for frequency names as user types in letters.
        Needed since frequency names are not very consistent. ie TAC 2 is NIFC T2, 34 NIFC T2, T2
        this will give the user a better shot at finding the right frequency. Does not work well on chrome mobile or desktop
        function fires each time user enters a letter or number. Out put is formated to <datalist> <option value>value</option>
      */
      $("#name_search").bind('input propertychange', function() {

        $("#frequency_name").html("");
        $.ajax({
          method: "POST",
          url: "jquery/frequency_name_lookup.php",
          data: {content: $("#name_search").val()}
        })
        .done(function(msg) {
          //console.log(msg);
          $("#frequency_name").empty();
          $("#frequency_name").html(msg);
        }); 
        
      });
      /*
        function to see what was clicked and then set the page to the mode that was clicked
      */
      $(".rxtxname").click(function() {
        var calledBy = $(this).attr("id");
        
        switch(calledBy) {
          case "rx":
            // hide div for name, show rxtx
            $("#formLabel").text("Enter the RX Frequency");
            $("#mode").attr("value", "rx");
            showHideDiv("byRxTx");
            break;
          case "tx":
            // hide div for name, show rxtx
            $("#formLabel").text("Enter the TX Frequency");
            $("#mode").attr("value", "tx");
            showHideDiv("byRxTx");
            break;
          case "name":
            // hide div for rxtx, show name
            $("#formLabel").text("Enter the Frequency Name");
            $("#mode").attr("value", "name");
            showHideDiv("byName");
            break;
          case "zone":
            $("#formLabel").text("Select a Zone");
            $("#mode").attr("value", "zone");
            showHideDiv("viewZone");
            break;
        }
      });
      /*
        Function to show and hide the appropriate divs for the page mode.
      */
      function showHideDiv(divToShow) {
        $("#output").html("");
        switch(divToShow) {
          case "byRxTx":
            if($("#byRxTx").hasClass("hidden")) $("#byRxTx").removeClass("hidden");
            if(!$("#byName").hasClass("hidden")) $("#byName").addClass("hidden");
            if(!$("#viewZone").hasClass("hidden")) $("#viewZone").addClass("hidden");
            if($("#main").hasClass("zone-container")) $("#main").removeClass("zone-container");
            if(!$("#main").hasClass("freq-container")) $("#main").addClass("freq-container");
            break;
          case "byName":
            if(!$("#byRxTx").hasClass("hidden")) $("#byRxTx").addClass("hidden");
            if($("#byName").hasClass("hidden")) $("#byName").removeClass("hidden");
            if(!$("#viewZone").hasClass("hidden")) $("#viewZone").addClass("hidden");
            if($("#main").hasClass("zone-container")) $("#main").removeClass("zone-container");
            if(!$("#main").hasClass("freq-container")) $("#main").addClass("freq-container");
            break;
          case "viewZone":
            if(!$("#byRxTx").hasClass("hidden")) $("#byRxTx").addClass("hidden");
            if(!$("#byName").hasClass("hidden")) $("#byName").addClass("hidden");
            if($("#viewZone").hasClass("hidden")) $("#viewZone").removeClass("hidden");
            if(!$("#main").hasClass("zone-container")) $("#main").addClass("zone-container");
            if($("#main").hasClass("freq-container")) $("#main").removeClass("freq-container");
            break;

        }
      }
    
    </script>
  </body>
</html>