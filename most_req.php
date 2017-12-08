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
$pageTitle = "Most Requested ".$typeText;

$list = "";

switch (strtoupper($get["type"])) {  
  case "T":
    $database->query("SELECT user_account.*, COUNT(status_id) AS reqCount FROM user_account 
      LEFT JOIN event_invites ON user_account.userId = event_invites.user_id 
      WHERE user_account.userType = 'T' 
      GROUP BY event_invites.user_id
      ORDER BY reqCount DESC;");
    $list = $database->resultset();
    break;
  case "P":
    $database->query("SELECT user_account.*, COUNT(event_planner.id) AS reqCount FROM user_account 
      LEFT JOIN event_planner ON user_account.userId = event_planner.user_id 
      WHERE user_account.userType = 'P' 
      GROUP BY event_planner.user_id
      ORDER BY reqCount DESC;");
    $list = $database->resultset();
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
                    <h2><i class="fa fa-music"></i>&nbsp;<?php echo $pageTitle; ?>&nbsp;<small>top requested <?php echo strtolower($typeText); ?></small></h2>
                  </div>                  
                </div>
                <div class="row x_content">
                  <?php Messages::display(); ?>
                  <?php if ($id == "") { ?>
                  <table id="datatable" class="table table-striped table-bordered">
                      <thead>
                        <tr>                                                    
                          <th>Request Count</th>                                          
                          <th>Name</th>                                                                                 
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($list as $row) : ?>
                          <tr>                                                        
                            <td><?php echo $row["reqCount"]; ?></td>
                            <td><a href="event_list.php?u=<?php echo $row['userId']; ?>"><?php echo $row["firstName"].' '.$row["lastName"]; ?></a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>                  
                  <?php } ?>
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
    $("#datatable").DataTable({
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
