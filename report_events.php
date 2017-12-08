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

$pageTitle = "Events Report";

$list = "";

switch (strtoupper($get["a"])) {    
  case "GENERATE" :     
    $daterange = explode(" - ", $post["daterange"]);
    $from = date("Y-m-d", strtotime($daterange[0]));
    $to = date("Y-m-d", strtotime($daterange[1]));    
    
    switch ($post["groupby"]) {
      case "P": 
        $database->query("SELECT event_planner.user_id, event.Name, user_account.firstName, user_account.lastName, 
          CASE
            WHEN event.status = 2 THEN 'Cancelled'
            WHEN event.status = 1 THEN 'Success'
            END AS eventStatus, event.* FROM event_planner 
          LEFT JOIN event ON event_planner.event_id = event.Id 
          LEFT JOIN planner_payments ON event_planner.planner_payment_id = planner_payments.id
          LEFT JOIN user_account ON event_planner.user_id = user_account.userId
          WHERE dateStarted >= :dateStarted AND dateStarted <= :dateEnd".($post["status"] != "" ? " AND event.status = :eventStatus;" : "")
          ." ORDER BY event_planner.user_id");
          if ($post["status"] != "") $database->bind(":eventStatus", $post["status"]);
        break;
      case "T": 
        $database->query("SELECT event_invites.user_id, event.Name, user_account.firstName, user_account.lastName, 
          CASE
            WHEN event.status = 2 THEN 'Cancelled'
            WHEN event.status = 1 THEN 'Success'
            END AS eventStatus, event_invites.user_rate AS cost, event.* FROM event_invites 
          LEFT JOIN event ON event_invites.event_id = event.Id           
          LEFT JOIN user_account ON event_invites.user_id = user_account.userId
          WHERE dateStarted >= :dateStarted AND dateStarted <= :dateEnd".($post["status"] != "" ? " AND event.status = :eventStatus;" : "")
          ." ORDER BY event_invites.user_id");
          if ($post["status"] != "") $database->bind(":eventStatus", $post["status"]);
        break;
      default:
        $database->query("SELECT event.Name, '' AS firstName, '' AS lastName, 
          CASE
            WHEN event.status = 2 THEN 'Cancelled'
            WHEN event.status = 1 THEN 'Success'
            END AS eventStatus, event.* FROM event                               
          WHERE dateStarted >= :dateStarted AND dateStarted <= :dateEnd".($post["status"] != "" ? " AND event.status = :eventStatus;" : ""));        
          if ($post["status"] != "") $database->bind(":eventStatus", $post["status"]);
        break;
    }    
    $database->bind(":dateStarted", $from);
    $database->bind(":dateEnd", $to);
    $list = $database->resultset();
    // $database->debug();
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
                    <h2><i class="fa fa-tasks"></i>&nbsp;<?php echo $pageTitle; ?>&nbsp;<small>Display events by date range, grouped by talent or planner with status criteria</small></h2>                                        
                  </div>                  
                </div>
                <div class="row x_content">
                  <?php Messages::display(); ?>
                  <form id="reporteventform" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?a=generate'; ?>" class="form-horizontal form-label-left">
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="daterange">Date Range <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="daterange" name="daterange" required="required" class="form-control col-md-7 col-xs-12" />                        
                      </div>
                    </div>                                            
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="groupby">Group By</label>
                      <div class="col-md-3 col-sm-3 col-xs-12">
                        <select id="groupby" name="groupby" class="form-control col-md-3 col-xs-12">
                          <option></option>
                          <option value="P"<?php echo ($post["groupby"] == "P" ? " selected" : ""); ?>>Planner</option>
                          <option value="T"<?php echo ($post["groupby"] == "T" ? " selected" : ""); ?>>Talent</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="status">Status</label>
                      <div class="col-md-3 col-sm-3 col-xs-12">
                        <select id="status" name="status" class="form-control col-md-3 col-xs-12">
                          <option></option>
                          <option value="1"<?php echo ($post["status"] == "1" ? " selected" : ""); ?>>Success</option>
                          <option value="2"<?php echo ($post["status"] == "2" ? " selected" : ""); ?>>Cancelled</option>
                        </select>
                      </div>
                    </div>
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <a class="btn btn-default" href="<?php echo $_SERVER['PHP_SELF']; ?>">Reset</a>
                        <button type="submit" class="btn btn-success">Generate</button>                        
                      </div>
                    </div>
                  </form>
                  <?php if ($list != "") { ?>
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                      <thead>
                        <tr>            
                          <?php if ($post["groupby"] != "") { ?><th><?php echo ($post["groupby"] == "P" ? "Planner" : "Talent"); ?></th><?php } ?>              
                          <th>Event</th>                          
                          <th>Status</th>
                          <th>Cost</th>                          
                          <th>Income</th>                          
                        </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($list as $row) : ?>
                        <tr>                          
                        <?php if ($post["groupby"] != "") { ?><td><?php echo $row["firstName"]." ".$row["lastName"]; ?></td><?php } ?>                          
                          <td><?php echo $row["Name"]; ?></td>                          
                          <td><?php echo $row["eventStatus"]; ?></td>
                          <td><?php echo $row["cost"] == "" ? 0 : $row["cost"]; ?></td>                          
                          <td><?php echo $row["income"] == "" ? 0 : $row["income"]; ?></td>
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
    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
        $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $('#daterange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);    
  });
  </script>
	</body>
</html>
