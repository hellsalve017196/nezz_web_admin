<?php
// Start Session
session_start();
date_default_timezone_set('UTC');

// Include Config
require('config.php');

// Include if secured page
include('templates/secure.php');

require('classes/Database.php');
require('classes/Messages.php');

$database = new Database;

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$pageTitle = "Location Frequency";

function DECtoDMS($dec)
{

// Converts decimal longitude / latitude to DMS
// ( Degrees / minutes / seconds ) 

// This is the piece of code which may appear to 
// be inefficient, but to avoid issues with floating
// point math we extract the integer part and the float
// part by using a string function.

    $vars = explode(".",$dec);
    $deg = $vars[0];
    $tempma = "0.".$vars[1];

    $tempma = $tempma * 3600;
    $min = floor($tempma / 60);
    $sec = number_format($tempma - ($min*60), 2);

    return array("deg"=>$deg,"min"=>$min,"sec"=>$sec);
} 

function DECtoDMSText($dec) {
  $arr = DECtoDMS($dec);
  if (is_array($arr)) {
    return $arr["deg"]."&deg; ".$arr["min"]."' ".$arr["sec"]."\"";
  }
  return "";
}

$list = "";

switch (strtoupper($get["a"])) {  
  default:
    $database->query("SELECT location, COUNT(Id) AS locationCnt, longitude, latitude FROM event GROUP BY longitude, latitude ORDER BY locationCnt DESC;");
    $list = $database->resultset();
    break;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>    
    <title>Subway Talent | <?php echo strtoupper($pageTitle); ?></title>
    <style>      
      #default-map { 
        height: 400px; 
        width: 100%;  
        border: solid 1px;
      }
    </style>    
  </head>
	<body class="nav-md">
    <script src="http://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_API_MAPS; ?>"></script>
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
					<?php if(!@include('templates/sidebar.php')) throw new Exception("Failed to include 'sidebar'"); ?>
        </div>
        <?php if(!@include('templates/topbar.php')) throw new Exception("Failed to include 'topbar'"); ?>
        <div class="right_col" role="main">
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="row x_title">
                  <div class="col-md-10">                    
                    <h2><i class="fa fa-map-marker"></i>&nbsp;<?php echo $pageTitle; ?>&nbsp;<small>display locations of most frequent events</small></h2>                    
                  </div>                  
                </div>
                <div class="row x_content">
                  <?php Messages::display(); ?>
                  <div id="default-map"></div><br/>
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>                          
                        <th>Location</th>
                        <th>Frequency</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      $i = 1;
                      foreach($list as $row) : 
                      ?>
                        <tr>                            
                          <td><a href="javascript:(0);" onclick="$('#row-<?php echo $i; ?>').toggle();"><?php echo ucwords($row["location"]); ?>&nbsp;<span class="fa fa-plus-square"></span></a></td>
                          <td><?php echo $row["locationCnt"]; ?></td>                            
                          <td><?php echo DECtoDMSText($row["latitude"]); ?></td>                            
                          <td><?php echo DECtoDMSText($row["longitude"]); ?></td>                            
                        </tr>
                        <tr>
                          <td colspan="4" id="row-<?php echo $i; ?>" style="display: none;">
                            <table class="table table-striped table-bordered">
                              <thead>
                                <tr>                          
                                  <th>Event</th>
                                  <th>Type</th>
                                  <th>Date</th>
                                  <th>Description</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php
                                $databaseEvent = new Database;
                                $databaseEvent->query("SELECT event.*, event_types.type_name FROM event 
                                  LEFT JOIN event_types ON event_types.id = event.type_id
                                  WHERE longitude = :longitude AND latitude = :latitude ORDER BY dateEnd LIMIT 5;");
                                $databaseEvent->bind(":longitude", $row["longitude"]);
                                $databaseEvent->bind(":latitude", $row["latitude"]);
                                $events = $databaseEvent->resultset();

                                foreach($events as $event) : 
                                ?>
                                <tr>
                                  <td><?php echo $event["Name"].": <strong>".$event["title"]."</strong>"; ?></td>
                                  <td><?php echo $event["type_name"]; ?></td>
                                  <td><?php echo date('Y-m-d h:m', strtotime($event["dateStarted"]))." - ".date('Y-m-d h:m', strtotime($event["dateEnd"])); ?></td>
                                  <td><?php echo $event["description"]; ?></td>
                                </tr>                                
                                <?php
                                endforeach;
                                ?>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      <?php 
                      $i++;
                      endforeach; 
                      ?>
                    </tbody>
                  </table>                  
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>
          <br />                  
        </div>
        <footer>
          <div class="pull-right">
            <i class="glyphicon glyphicon-cog"></i> Subway Talent Administration. &copy;2017 All Rights Reserved. Privacy and Terms.
          </div>
          <div class="clearfix"></div>
        </footer>
      </div>
    </div>
	<?php if(!@include('templates/footer.php')) throw new Exception("Failed to include 'footer'"); ?>
  <script type="text/javascript">
  $(document).ready(function() {
    $("#datatable-buttons").DataTable({
      destroy: true,
      "order": [[ 1, "desc" ]], 
      dom: "Bfrtip",
      buttons: [
						{
						  extend: "copy",
						  className: "btn-sm"
						},
						{
						  extend: "csv",
						  className: "btn-sm"
						},
						{
						  extend: "excel",
						  className: "btn-sm"
						},
						{
						  extend: "pdfHtml5",
						  className: "btn-sm"
						},
						{
						  extend: "print",
						  className: "btn-sm"
						},
					  ],
					  responsive: true
    });
    initialize();
  });    
  function attainLocation() {
    var tableLocs = [];
    $("#datatable-buttons").find('tbody tr').each(function(i) {
      var $tds = $(this).find('td');

      Loc = $tds.eq(0).text();
      if (Loc != "") {        
        Lat = $tds.eq(2).text();
        Lon = $tds.eq(3).text();

        var obj = {
          'loc': Loc,
          'lat': Lat,
          'lon': Lon
        };
        tableLocs.push(obj);
      }
    });    
    // console.log(tableLocs);
    return tableLocs;  
  }
  function initialize() {
    var locations = attainLocation(); 
    var myOptions = {
        center: new google.maps.LatLng(53.3242381, -6.3857877),
        zoom: 2,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("default-map"), myOptions);		   
		setMarkers(map, locations);
  }
  function setMarkers(map, locations) {
    var marker, i
    for (i = 0; i < locations.length; i++) {
        var loc = locations[i].loc;
        var lat = locations[i].lat;
        var long = locations[i].lon;

        latlngset = new google.maps.LatLng(lat, long);
        var marker = new google.maps.Marker({
            map: map,
            title: loc,
            position: latlngset
        });
        map.setCenter(marker.getPosition());

        var content = "Location: " + loc;
        var infowindow = new google.maps.InfoWindow();

        google.maps.event.addListener(marker, 'click', (function(marker, content, infowindow) {
            return function() {
                infowindow.setContent(content);
                infowindow.open(map, marker);
            };
        })(marker, content, infowindow));
    }
  }
  </script>  
	</body>
</html>
