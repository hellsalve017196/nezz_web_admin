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

$pageTitle = "Talent Status";

$list = "";
$database->query("SELECT user_account.*, user_login.LockedOutDate FROM user_account 
    LEFT JOIN user_login ON user_account.userId = user_login.userid 
    WHERE userType = 'T'");       
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
                        <th>Is Blocked</th>                        
                      </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $row) : ?>
                      <tr>                                                  
                        <td><?php echo $row["ratingTalent"] == "" ? "NA" : $row["ratingTalent"]; ?></td>
                        <td><?php echo $row["firstName"].' '.$row["lastName"]; ?></td>
                        <td><?php echo ($row["LockedOutDate"] != "" && $row["LockedOutDate"] != "0001-01-01 00:00:00") ? ("<strong>YES"." since ".$row["LockedOutDate"]."</strong>") : "NO"; ?></td>
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
	</body>
</html>
