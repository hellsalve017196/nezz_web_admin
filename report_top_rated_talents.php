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

$pageTitle = "Top Rated Talents";

$list = "";

switch (strtoupper($get["a"])) {      
  case "GENERATE" : 
    switch ($post["criteria"]) {
      case "Event Type": 
        $database->query('SELECT Id, type_name AS Name FROM event_types;');
        break;
      case "Genre":
        $database->query('SELECT * FROM genre;');
        break;
      case "Skills": 
        $database->query('SELECT * FROM skills;');
        break;
    }
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
                    <h2><i class="fa fa-tasks"></i>&nbsp;<?php echo $pageTitle; ?>&nbsp;Report<?php echo ($post["criteria"] != "" ? " by ".$post["criteria"] : ""); ?></h2>
                  </div>
                  <div class="col-md-2" style="text-align: right;">
                    <?php if ($list != "") { ?>
                    <a class="btn btn-default" href="<?php echo $_SERVER['PHP_SELF']; ?>">Back</a>
                    <?php } ?>
                  </div>
                </div>
                <div class="row x_content">
                  <?php Messages::display(); ?>
                  <?php if ($list == "") { ?>
                  <form id="topratedform" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?a=generate'; ?>" class="form-horizontal form-label-left">                      
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="criteria">Criteria</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <select id="criteria" name="criteria" class="form-control col-md-7 col-xs-12" required="required">
                          <option></option>
                          <option value="Event Type">Event Type</option>
                          <option value="Genre">Genre</option>
                          <option value="Skills">Skills</option>                      
                        </select>                          
                      </div>
                    </div>
                    <!--
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="frequency">Frequency</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <select id="frequency" name="frequency" class="form-control col-md-7 col-xs-12">
                          <option></option>
                          <option value="weekly">Weekly</option>
                          <option value="monthly">Monthly</option>                          
                        </select>                          
                      </div>
                    </div>
                    -->
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <a class="btn btn-default" href="<?php echo $_SERVER['PHP_SELF']; ?>">Back</a>
                        <button type="submit" class="btn btn-success">Generate</button>                          
                      </div>
                    </div>
                  </form>
                  <?php } else { ?>                                    
                    <?php foreach ($list as $row) : ?>
                    <div class="col-md-4 col-sm-4 col-xs-12">
                      <div class="x_panel tile fixed_height_320">
                        <div class="x_title">
                          <h2><?php echo $row["Name"]; ?></h2>                          
                          <div class="clearfix"></div>
                        </div>
                        <div class="x_content">       
                          <?php 
                          $toplist = [];
                          switch ($post["criteria"]) {
                            case "Event Type" : 
                              $database->query("SELECT user_account.* FROM user_account 
                                LEFT JOIN event_invites ON user_account.userId = event_invites.user_id 
                                LEFT JOIN event ON event_invites.event_id = event.Id 
                                WHERE event.type_id = :type_id AND NOT ratingTalent IS NULL ORDER BY ratingTalent DESC;");
                              $database->bind(":type_id", $row["Id"]);
                              break;
                            case "Genre" : 
                              $database->query("SELECT user_account.* FROM user_account 
                                LEFT JOIN talent_genres ON user_account.userId = talent_genres.user_id 
                                WHERE genre_id = :genre_id AND NOT ratingTalent IS NULL ORDER BY ratingTalent DESC;");
                              $database->bind(":genre_id", $row["Id"]);
                              break;
                            case "Skills" : 
                              $database->query("SELECT user_account.* FROM user_account 
                                LEFT JOIN talent_skills ON user_account.userId = talent_skills.user_id 
                                WHERE skill_id = :skill_id AND NOT ratingTalent IS NULL ORDER BY ratingTalent DESC;");
                              $database->bind(":skill_id", $row["Id"]);                              
                              break;
                          }                                            
                          $toplist = $database->resultset();                                   
                          ?>
                          <table class="datatable-buttons table table-striped table-bordered">
                            <thead>
                              <tr>                                                          
                                <th>Name</th>
                                <th>Rating</th>                                
                              </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($toplist as $top) : ?>                   
                              <tr>
                                <td><?php echo $top["firstName"]." ".$top["lastName"]; ?></td>                                
                                <td><?php echo $top["ratingTalent"]; ?></td>
                              </tr>                        
                            <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>                      
                    <?php endforeach; ?>                    
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
    $(".datatable-buttons").DataTable({
      destroy: true,
      bFilter: false, 
      bPaginate: false, 
      bInfo: false, 
      dom: "Bfrtip",
      buttons: [
						{
						  extend: "copy",
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
					  ],
					  responsive: true
    });    
  }); 
  </script>
	</body>
</html>
