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

$pageTitle = "Home";

$list = "";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>
    <title>NEZZ | <?php echo strtoupper($pageTitle); ?></title>
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
              <h3>Quick Links</h3>
              <ul>
                <li><a href="user_profile.php?type=P">Planner List</a></li>
                <!--<li><a href="make_payment.php">Make Payments</a></li>
                <li><a href="report_sales.php">Sales Report</a></li>                
                <li><a href="event_location_freq.php">Location Frequency</a></li>-->
              </ul>
            </div>
          </div>
          <br />                   
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            <i class="glyphicon glyphicon-cog"></i> Subway Talent Administration. &copy;2017 All Rights Reserved. Privacy and Terms.
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>
	<?php if(!@include('templates/footer.php')) throw new Exception("Failed to include 'footer'"); ?>  
	</body>
</html>