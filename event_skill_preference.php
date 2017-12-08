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

$pageTitle = "Event Skill Preference";

$id = $get["id"];
$list = "";

switch (strtoupper($get["a"])) {
  case "SAVE" :    
    if ($id == 0) {
      if ($post["name"] != "") {
        $database->query('SELECT * FROM skills WHERE Name = :Name;');
        $database->bind(':Name', $post["name"]);
        $uniqueName = $database->single();
        if ($uniqueName) Messages::set($pageTitle." already in use");
      }
      else Messages::set($pageTitle." name is required");
    }    

    if (!Messages::hasError()) {
      if ($id == 0) {
        $database->query('INSERT INTO skills (Name) VALUES (:Name);');
        $database->bind(':Name', $post["name"]);
        $database->execute();        
      }
      else {
        $database->query('UPDATE skills SET Name = :Name WHERE Id = :Id;');
        $database->bind(':Name', $post["name"]);
        $database->bind(':Id', $id);
        $database->execute();
      }
      if ($database->rowCount()) header('Location: '.$_SERVER["PHP_SELF"].'?a=saveok');
      else Messages::set($pageTitle." save failed");
    }
    break;
  case "SAVEOK" :
    Messages::set($pageTitle." saved", "success");
    break;
  case "DELETE" :
    $database->query('DELETE FROM skills WHERE Id = :Id;');
    $database->bind(':Id', $id);
    $database->execute();

    if ($database->rowCount()) header('Location: '.$_SERVER["PHP_SELF"].'?a=deleteok');
    else Messages::set($pageTitle." delete failed");
    break;
  case "DELETEOK" :
    Messages::set($pageTitle." deleted", "success");
    break;  
}
if ($id > 0) {
  $database->query('SELECT * FROM skills WHERE Id = :Id;');
  $database->bind(':Id', $id);
  $list = $database->single();
}
else {
  $database->query('SELECT * FROM skills;');
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
                    <h2><i class="fa fa-calendar"></i>&nbsp;Manage <?php echo $pageTitle; ?></h2>                    
                  </div>
                  <div class="col-md-2" style="text-align: right;">
                    <?php if ($id == "") { ?>
                    <a class="btn btn-success" href="<?php echo $_SERVER['PHP_SELF'].'?id=0'; ?>">Add New</a>
                    <?php } ?>
                  </div>
                </div>
                <div class="row x_content">
                  <?php Messages::display(); ?>
                  <?php if ($id == "") { ?>
                  <table id="datatable" class="table table-striped table-bordered">
                      <thead>
                        <tr>                          
                          <th>Name</th>                          
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($list as $row) : ?>
                          <tr>                            
                            <td><a href="<?php echo $_SERVER['PHP_SELF'].'?id='.$row['Id']; ?>"><i class="fa fa-pencil"></i>&nbsp;<?php echo $row["Name"]; ?></a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                  </table>
                  <?php } else { ?>
                  <form id="eventskillform" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?a=save&id='.$id; ?>" class="form-horizontal form-label-left">
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Name <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="name" name="name" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $list == "" ? "" : $list["Name"]; ?>">
                      </div>
                    </div>                                            
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <a class="btn btn-default" href="<?php echo $_SERVER['PHP_SELF']; ?>">Back</a>
                        <button type="submit" class="btn btn-success">Save</button>
                        <?php if ($id > 0) { ?>
                        <button type="button" class="btn btn-danger" onclick="redirectOnConfirm('Delete this?', '<?php echo $_SERVER['PHP_SELF'].'?a=delete&id='.$id; ?>');">Delete</button>
                        <?php } ?>
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
	</body>
</html>
