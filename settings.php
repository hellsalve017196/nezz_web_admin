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

$post = filter_input_array(INPUT_POST);
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$pageTitle = "Setting";

$id = $get["id"];

if ($id > 0) {
  $item = "";
  $database->query('SELECT * FROM subway_settings WHERE id = :id;');
  $database->bind(':id', intval($id));
  $item = $database->single();
}
else {
  $list = "";
  $database->query("SELECT * FROM subway_settings;");  
  $list = $database->resultset();
}

switch (strtoupper($get["a"])) {
  case "SAVE" :    
    if ($id == 0) {
      if ($post["name"] != "") {
        $database->query('SELECT * FROM subway_settings WHERE setting_name = :setting_name;');
        $database->bind(':setting_name', $post["name"]);
        $uniqueName = $database->single();
        if ($uniqueName) Messages::set($pageTitle." name already in use");
      }
      else Messages::set($pageTitle." name is required");
    }    

    if (!Messages::hasError()) {
      if ($id == 0) {
        $database->query('INSERT INTO subway_settings (setting_name, setting_value) VALUES (:setting_name, :setting_value);');
        $database->bind(':setting_name', $post["name"]);
        $database->bind(':setting_value', $post["value"]);
        $database->execute();
      }
      else {
        $database->query('UPDATE subway_settings SET setting_name = :setting_name, setting_value = :setting_value WHERE id = :id;');
        $database->bind(':setting_name', $post["name"]);
        $database->bind(':setting_value', $post["value"]);
        $database->bind(':id', $id);
        $database->execute();
      }
      if ($database->rowCount()) header('Location: '.$_SERVER['PHP_SELF'].'?a=saveok');
      else Messages::set($pageTitle." save failed");
    }
    break;
  case "SAVEOK" :
    Messages::set($pageTitle." saved", "success");
    break;
  case "DELETE" :
    $database->query('DELETE FROM subway_settings WHERE id = :id;');
    $database->bind(':id', $id);
    $database->execute();

    if ($database->rowCount()) header('Location: '.$_SERVER['PHP_SELF'].'?a=deleteok');
    else Messages::set($pageTitle." delete failed");
    break;
  case "DELETEOK" :
    Messages::set($pageTitle." deleted", "success");
    break;  
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>
    <title>Nezz</title>
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
                    <h2><i class="fa fa-th"></i>&nbsp;Manage <?php echo $pageTitle; ?>s</h2>
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
                          <th>Value</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($list as $row) : ?>
                          <tr>                            
                            <td><a href="<?php echo $_SERVER['PHP_SELF'].'?id='.$row['id']; ?>"><i class="fa fa-pencil"></i>&nbsp;<?php echo strtoupper($row["setting_name"]); ?></a></td>
                            <td><?php echo $row["setting_value"]; ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                  </table>
                  <?php } else { ?>
                  <form id="settingform" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?a=save&id='.$id; ?>" class="form-horizontal form-label-left">
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Name <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="name" name="name" placeholder="Setting Name" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $item == "" ? "" : strtoupper($item["setting_name"]); ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="content">Value</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="value" name="value" placeholder="Setting Value" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $item == "" ? "0" : $item["setting_value"]; ?>">
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
