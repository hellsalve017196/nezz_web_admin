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

$pageTitle = "Event";

$id = $get["id"];
$uid = $get["u"];
$list = "";

switch (strtoupper($get["a"])) {  
  case "SAVE" : 
    if ($id == 0) {
      Messages::set("Please choose an event");
    }      

    if (!Messages::hasError()) {
      $database->query('UPDATE event SET delete_reason = :delete_reason WHERE Id = :Id;');
      $database->bind(':delete_reason', $post["reason"]);
      $database->bind(':Id', $id);
      $database->execute();
      
      if ($database->rowCount()) header('Location: '.$_SERVER["PHP_SELF"].'?a=saveok');
      else Messages::set($pageTitle." save failed");
    }
    break;
  case "SAVEOK" : 
    Messages::set($pageTitle." saved", "success");
    break;  
}
if ($id > 0) {
  $database->query("SELECT event.*, event_types.type_name, user_account.firstName, user_account.lastName, user_account.email FROM event 
    LEFT JOIN event_types ON event.type_id = event_types.id 
    LEFT JOIN event_planner ON event_planner.event_id = event.id 
    LEFT JOIN user_account ON user_account.userId = event_planner.user_id
    WHERE event.Id = :Id".($uid != "" ? " AND user_account.userId = :user_account;" : ""));
  $database->bind(':Id', $id);
  if ($uid != "") $database->bind(':user_account', $uid);
  $list = $database->single();
}
else {
  $database->query("SELECT event.*, event_types.type_name, user_account.firstName, user_account.lastName, user_account.email FROM event 
    LEFT JOIN event_types ON event.type_id = event_types.id 
    LEFT JOIN event_planner ON event_planner.event_id = event.id 
    LEFT JOIN user_account ON user_account.userId = event_planner.user_id"
    .($uid != "" ? " WHERE user_account.userId = :user_account;" : ""));
  if ($uid != "") $database->bind(':user_account', $uid);
  $list = $database->resultset();
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
                    <h2><i class="fa fa-calendar"></i>&nbsp;<?php echo $pageTitle; ?>s List&nbsp;<small>displays list of events, block event and mark as spam</small></h2>                    
                  </div>                  
                </div>
                <div class="row x_content">
                  <?php Messages::display(); ?>
                  <?php if ($id == "") { ?>
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>                          
                        <th>Event</th>
                        <th>Planner</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Location</th>                          
                        <th>Status</th>
                        <th>Is Deleted?</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($list as $row) : ?>
                      <tr>                            
                        <td><a href="<?php echo $_SERVER['PHP_SELF'].'?id='.$row['Id']; ?>"><i class="fa fa-pencil"></i>&nbsp;<?php echo $row["Name"]; ?></a></td>
                        <?php
                        $displayName = trim($row["firstName"]." ".$row["lastName"]);
                        if ($displayName == "") $displayName = $row["email"];
                        if ($displayName == "") $displayName = $uid;
                        ?>
                        <td><?php echo $displayName; //.'<br/><a href="mailto:'.$row["email"].'">'.$row["email"].'</a>'; ?></td>
                        <td><?php echo $row["type_name"]; ?></td>
                        <td><?php echo date('Y-m-d h:m', strtotime($row["dateStarted"])); ?></td>
                        <td><?php echo date('Y-m-d h:m', strtotime($row["dateEnd"])); ?></td>
                        <td><?php echo ucwords($row["location"]); ?></td>
                        <td><?php echo $row["status"] == 1 ? "DONE" : "NA"; ?></td>
                        <td><?php echo $row["delete_reason"] == "" ? "NO" : "YES <span class='fa fa-info' data-toggle='tooltip' title='".$row["delete_reason"]."'></span>"; ?></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>    
                  <?php } else { ?>
                  <form id="eventform" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?a=save&id='.$id; ?>" class="form-horizontal form-label-left">
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Name <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="name" name="name" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $list == "" ? "" : $list["Name"]; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="reason">Delete Reason <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <textarea style="width: 100%;" rows="5" id="reason" name="reason" class="form-control col-md-7 col-xs-12"><?php echo $list == "" ? "" : $list["delete_reason"]; ?></textarea>
                      </div>
                    </div>                                            
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <a class="btn btn-default" href="<?php echo $_SERVER['PHP_SELF']; ?>">Back</a>
                        <button type="submit" class="btn btn-success">Save</button>                        
                      </div>
                    </div>
                  </form>
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
      $('[data-toggle="tooltip"]').tooltip();
      $("#datatable-buttons").DataTable({
        destroy: true,        
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
