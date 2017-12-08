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

$pageTitle = "User";

$id = $get["id"];
$list = "";

switch (strtoupper($get["a"])) {  
  case "UNLOCK" :
    $database->query('UPDATE user_login SET LockedOutDate = NULL WHERE Id = :Id;');
    $database->bind(':Id', $id);
    $database->execute();

    if ($database->rowCount()) header('Location: '.$_SERVER["PHP_SELF"]."?a=unlockok");
    else Messages::set($pageTitle." unlock failed");
    break;
  case "UNLOCKOK" :
    Messages::set($pageTitle." unlocked", "success");
    break;
  case "LOCK" :
    $database->query('UPDATE user_login SET LockedOutDate = NOW() WHERE Id = :Id;');
    $database->bind(':Id', $id);
    $database->execute();

    if ($database->rowCount()) header('Location: '.$_SERVER["PHP_SELF"]."?a=lockok");
    else Messages::set($pageTitle." lock failed");    
    break;
  case "LOCKOK" :
    Messages::set($pageTitle." locked", "success");
    break;
}  
$database->query('SELECT user_login.*, user_account.firstName, user_account.lastName, user_account.facebookuser, user_account.userType FROM user_login 
  LEFT JOIN user_account ON user_login.userid = user_account.userId 
  WHERE user_login.role_name = "USER"
  '.($id > 0 ? " AND Id = :Id " : "").'
  ORDER BY LastLoggedInDate DESC;');
if ($id > 0) $database->bind(':Id', $id);
$list = $database->resultset();
if ($id > 0 && $database->rowCount() == 1) $list = $list[0];
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
                    <h2><i class="fa fa-music"></i>&nbsp;Block <?php echo $pageTitle; ?>s</h2>
                  </div>                  
                </div>
                <div class="row x_content">
                  <?php Messages::display(); ?>                  
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                      <thead>
                        <tr>                                                    
                          <th>Name</th>
                          <th>Email</th>
                          <th>Type</th>
                          <th>Last Login</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($list as $row) : ?>
                          <tr>                                                        
                            <td><?php echo $row["firstName"].' '.$row["lastName"]; ?></td>
                            <td><?php echo $row["userid"]; ?></td>
                            <td><?php echo $row["userType"] == 'T' ? "Talent" : "Planner"; ?></td>
                            <td><time class="timeago" datetime="<?php echo $row['LastLoggedInDate']; ?>"><?php echo $row["LastLoggedInDate"]; ?></time></td>
                            <td>
                              <?php if ($row["LockedOutDate"] != "" && $row["LockedOutDate"] != "0001-01-01 00:00:00") { ?>
                                <a onclick="return confirm('Unblock this account?');" href="<?php echo $_SERVER['PHP_SELF'].'?a=unlock&id='.$row['Id']; ?>"><span class="fa fa-unlock"></span> Unblock Account</a><br/>Account has been blocked <br/><time class="timeago" datetime="<?php echo $row['LockedOutDate']; ?>"><?php echo $row['LockedOutDate']; ?></time>
                              <?php } else { ?>
                                <a onclick="return confirm('Block this account?');" href="<?php echo $_SERVER['PHP_SELF'].'?a=lock&id='.$row['Id']; ?>"><span class="fa fa-lock"></span> Block Account</a>
                              <?php } ?>
                            </td>
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
