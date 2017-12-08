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

$pageTitle = "User Logins";

$id = $get["id"];
$list = "";

$database->query('SELECT user_login.*, user_account.firstName, user_account.lastName, user_account.facebookuser FROM user_login 
  LEFT JOIN user_account ON user_login.userid = user_account.userId 
  WHERE role_name = "WEBADMIN" 
  '.($id > 0 ? " AND Id = :Id" : "").'
  ORDER BY LastLoggedInDate DESC;');
if ($id > 0) $database->bind(':Id', $id);
$list = $database->resultset();
if ($id > 0 && $database->rowCount() == 1) $list = $list[0];

switch (strtoupper($get["a"])) {
  case "SAVE" :
    if ($post["password"] != "" && $post["passwordconfirm"] != "") {
      if ($post["password"] != $post["passwordconfirm"]) Messages::set("Please confirm your password");
    }
    if ($id == 0) {
      if ($post["email"] != "") {
        $database->query('SELECT * FROM user_login WHERE userid = :userid;');
        $database->bind(':userid', $post["email"]);
        $uniqueEmail = $database->single();
        if ($uniqueEmail) Messages::set("Email already in use");
      }
      else Messages::set("Email is required");
    }

    if (!Messages::hasError()) {
      if ($id == 0) {
        $database->query('INSERT INTO user_account (userId, firstName, lastName, email) VALUES (:userId, :firstName, :lastName, :userId);');
        $database->bind(':userId', $post["email"]);
        $database->bind(':firstName', $post["firstname"]);
        $database->bind(':lastName', $post["lastname"]);
        $database->execute();
        
        $database->query('INSERT INTO user_login (userid, Password, role_name) VALUES (:userid, :Password, :role_name);');
        $database->bind(':userid', $post["email"]);
        $database->bind(':Password', $post["passwordconfirm"]);
        $database->bind(':role_name', $post["role"]);
        $database->execute();
      }
      else {
        $database->query('UPDATE user_login SET role_name = :role_name'.($post["passwordconfirm"] != "" ? ', Password = :Password' : '').' WHERE Id = :Id;');
        $database->bind(':role_name', $post["role"]);
        if ($post["passwordconfirm"] != "") $database->bind(':Password', $post["passwordconfirm"]);
        $database->bind(':Id', $id);
        $database->execute();

        $database->query('UPDATE user_account SET firstName = :firstName, lastName = :lastName WHERE userId = :userId');
        $database->bind(':firstName', $post["firstname"]);
        $database->bind(':lastName', $post["lastname"]);
        $database->bind(':userId', $list["userid"]);        
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
    $database->query('DELETE FROM user_login WHERE Id = :Id;');
    $database->bind(':Id', $id);
    $database->execute();    

    $database->query('DELETE FROM user_account WHERE userId = :userId;');
    $database->bind(':userId', $list["userid"]);
    $database->execute();

    if ($database->rowCount()) header('Location: '.$_SERVER["PHP_SELF"]."?a=deleteok");
    else Messages::set($pageTitle." delete failed");
    break;
  case "DELETEOK" :
    Messages::set($pageTitle." deleted", "success");
    break;
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
                    <h2><i class="fa fa-user"></i>&nbsp;<?php echo $pageTitle; ?>&nbsp;<small>manage logins, lock and unlock access, change password and user access role</small></h2>
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
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                      <thead>
                        <tr>                          
                          <th>Email</th>
                          <th>Name</th>
                          <th>Role Name</th>
                          <th>Date Registered</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($list as $row) : ?>
                          <tr>                            
                            <td><a href="<?php echo $_SERVER['PHP_SELF'].'?id='.$row['Id']; ?>"><i class="fa fa-pencil"></i>&nbsp;<?php echo $row["userid"]; ?></a></td>
                            <td><?php echo $row["firstName"].' '.$row["lastName"]; ?></td>
                            <td><?php echo $row["role_name"]; ?></td>
                            <td><time class="timeago" datetime="<?php echo $row['date_created']; ?>"><?php echo $row["date_created"]; ?></time></td>
                            <td>
                              <?php if ($list["LockedOutDate"] != "") { ?>
                                <a onclick="redirectOnConfirm('Unblock this account?', '<?php echo $_SERVER['PHP_SELF'].'?a=unlock&id='.$id; ?>');" href="#"><span class="fa fa-unlock"></span> Unblock&nbsp;account has been blocked since <?php echo $list["LockedOutDate"]; ?></a>
                              <?php } else { ?>
                                <a onclick="redirectOnConfirm('Block this account?', '<?php echo $_SERVER['PHP_SELF'].'?a=lock&id='.$id; ?>" href="#"><span class="fa fa-lock"></span> Block Account</a>
                              <?php } ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                  </table>
                  <?php } else { ?>
                  <form id="userform" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?a=save&id='.$id; ?>" class="form-horizontal form-label-left">
                      <div class="form-group">
                        <?php
                        $input = "Email <span class='required'>*</span>";
                        if (intval($list["facebookuser"]) == 1) {
                          $input = "Facebook User";
                        }
                        ?>
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email"><?php echo $input; ?></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">                                                  
                          <input type="email" id="email" name="email" required="required" class="form-control col-md-7 col-xs-12" <?php echo ($id != 0 ? "readonly='readonly'" : ""); ?> value="<?php echo $list == "" ? $post["email"] : $list["userid"]; ?>">                          
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password">Password
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="password" id="password" name="password" class="form-control col-md-7 col-xs-12" <?php echo ($id == 0 ? "required='required'" : ""); ?>>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="passwordconfirm">Confirm Password
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="password" id="passwordconfirm" name="passwordconfirm" class="form-control col-md-7 col-xs-12" <?php echo ($id == 0 ? "required='required'" : ""); ?>>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="firstname">First Name</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="firstname" name="firstname" class="form-control col-md-7 col-xs-12" value="<?php echo ($id == 0 ? '' : $list['firstName']); ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="lastname">Last Name</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="lastname" name="lastname" class="form-control col-md-7 col-xs-12" value="<?php echo ($id == 0 ? '' : $list['lastName']); ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="passwordconfirm">Role
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select id="role" name="role" class="form-control col-md-7 col-xs-12" required="required">
                            <option value="WEBADMIN"<?php echo ($list["role_name"] == "WEBADMIN" ? " selected" : ""); ?>>WEB ADMIN</option>
                          </select>                          
                        </div>
                      </div>
                      <?php if (($id > 0) && ($id != session_userid())) { ?>
                      <div class="form-group">
                        <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Last Logged In</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="lastloggedin" class="form-control has-feedback-left col-md-7 col-xs-12" type="text" name="lastloggedin" value="<?php echo $list["LastLoggedInDate"]; ?>" readonly="readonly">
                          <span class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <div id="block" class="btn-group" data-toggle="buttons">
                            <?php if ($list["LockedOutDate"] != "") { ?>
                              <button class="btn btn-info" onclick="redirectOnConfirm('Unblock this account?', '<?php echo $_SERVER['PHP_SELF'].'?a=unlock&id='.$id; ?>');">Unblock</button>&nbsp;account has been blocked since <?php echo $list["LockedOutDate"]; ?>
                            <?php } else { ?>
                              <button class="btn btn-danger" onclick="redirectOnConfirm('Block this account?', '<?php echo $_SERVER['PHP_SELF'].'?a=lock&id='.$id; ?>');">Block Account</button>
                            <?php } ?>
                          </div>
                        </div>
                      </div>
                      <?php } ?>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <a class="btn btn-default" href="<?php echo $_SERVER['PHP_SELF']; ?>">Back</a>
                          <button type="submit" class="btn btn-success">Save</button>
                          <?php if (($id > 0) && ($id != session_userid())) { ?>
                          <button type="button" class="btn btn-danger" onclick="redirectOnConfirm('Delete this account?', '<?php echo $_SERVER['PHP_SELF'].'?a=delete&id='.$id; ?>');">Delete</button>
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
