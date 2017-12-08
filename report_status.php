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

$type = $get["type"];
$typeText = $type ==  "T" ? "Talent" : "Planner";

$pageTitle = $typeText." Status";

$list = "";
$database->query("SELECT user_account.*, NULLIF(user_login.LockedOutDate, '0001-01-01 00:00:00') AS LockedOutDateNull FROM user_account 
    LEFT JOIN user_login ON user_account.userId = user_login.userid 
    WHERE userType = :userType 
    ORDER BY user_login.date_created DESC;");
$database->bind(":userType", $type);
$list = $database->resultset();

switch (strtoupper($get["a"])) {      
  default:  
    break;
} 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>
    <title>Subway Talent | <?php echo strtoupper($pageTitle); ?></title>
  </head>
	<body class="nav-md">
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
                    <h2><i class="fa fa-tasks"></i>&nbsp;<?php echo $pageTitle; ?>&nbsp;Report</h2>
                  </div>                  
                </div>
                <div class="row x_content">
                  <?php Messages::display(); ?>                  
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>            
                        <th>Rating</th>                        
                        <th>Name</th>
                        <th>Last Active Event</th>                         
                        <th>Is Blocked</th>                        
                      </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $row) : ?>
                      <tr>                                                  
                        <td><?php echo $row["rating"] == "" ? "0" : $row["rating"]; ?></td>
                        <td><?php echo $row["firstName"].' '.$row["lastName"]; ?></td>
                        <td>
                          <?php
                          $databaseEvent = new Database;
                          $databaseEvent->query("SELECT event.Name, event.dateStarted FROM event_planner 
                            LEFT JOIN event ON event_planner.event_id = event.Id 
                            WHERE user_id = :user_id                             
                            ORDER BY event_planner.id DESC 
                            LIMIT 1;");
                          $databaseEvent->bind(":user_id", $row["userId"]);
                          $lastEvent = $databaseEvent->single();
                          if ($lastEvent) {
                            echo $lastEvent["Name"]." ".$lastEvent["dateStarted"];
                          }
                          else echo "None";
                          ?>                          
                        </td>
                        <td><?php echo ($row["LockedOutDateNull"] != "") ? ("<time class='timeago' datetime='".$row['LockedOutDateNull']."'>".$row['LockedOutDateNull']."</time>") : "NO"; ?></td>
                      </tr>
                    <?php endforeach; ?>
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
        "order": [[ 0, "desc" ]],     
        dom: "Blfrtip", 
        lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, 'All'] ],         
        buttons: [
              {
                extend: "copy",
                className: "btn-sm"
              },
              {
                extend: "csv",
                className: "btn-sm"
              },
              ],
              responsive: true
      });      
    });
  </script>
	</body>
</html>
