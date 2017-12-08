<?php
// Start Session
session_start();
date_default_timezone_set('UTC');

// Include Config
require('config.php');

require('classes/Database.php');
require('classes/Messages.php');

$database = new Database;

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$pageTitle = "login";

switch (strtoupper($get["a"])) {
  case "LOGIN" :
    $email = $post["uEmail"];
    $password = $post["uPass"];


    if ($email == "") Messages::set("Email is required");
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) Messages::set("Invliad email format");
    else if ($password == "") Messages::set("Password is required");

    // if ($email != ADMIN_EMAIL && $password != ADMIN_PASSWORD) Messages::set("User needs to be an administrator to access");

    if (!Messages::hasError()) {
    	$database->query('SELECT * FROM user_login WHERE userid = :userid AND Password = :password;');
    	$database->bind(':userid', $email);
    	$database->bind(':password', $password);
    	$rowLogin = $database->single();


      if ($rowLogin) {

        if ($rowLogin["LockedOutDate"] != "") Messages::set("User is locked out");
        if ($rowLogin["role_name"] != "WEBADMIN") Messages::set("Access denied");

        if (!Messages::hasError()) {
          $database->query('UPDATE user_login SET LastLoggedInDate = CURRENT_TIMESTAMP WHERE Id = :Id;');
    	    $database->bind(':Id', $rowLogin['Id']);
    	    $database->execute();

          // update log in
          $database->query('SELECT * FROM user_account WHERE userId = :userId;');
        	$database->bind(':userId', $email);
        	$rowAccount = $database->single();

          // set sessions          
    			$_SESSION['user_data'] = array(
            "id"	=> $rowLogin['Id'],
            "email"	=> $rowLogin['userid'],
            "firstName" => $rowAccount["firstName"] == "" ? "FNAME" : $rowAccount["firstName"],
            "lastName" => $rowAccount["lastName"] == "" ? "LNAME" : $rowAccount["lastName"],
            "profilePic" => $rowAccount["profilePic"] == "" ? "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mm&f=y" : $rowAccount["profilePic"], 
            "expire" => time() + 3600
          );
          header('Location: '.ROOT_URL.'index.php');
        }
      }
      else Messages::set("Invalid user credential", "error");
    }
    break;
  case "LOGOUT" :
    session_unset();
    session_destroy();

    Messages::set("User logged out", "success");
    break;
  case "SESSION" :
    Messages::set("Login to access the system");
    break;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>
    <title>Subway Talent | <?php echo strtoupper($pageTitle); ?></title>
  </head>
  <body class="<?php echo $pageTitle; ?>">
    <div>
      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
            <form method="post" action="<?php echo $_SERVER['PHP_SELF'].'?a=login'; ?>">
              <h1>Login Form</h1>
              <?php Messages::display(); ?>
              <div>
                <input type="email" name="uEmail" class="form-control" placeholder="Email" required="required" />
              </div>
              <div>
                <input type="password" name="uPass" class="form-control" placeholder="Password" required="required" />
              </div>
              <div>
                <input type="submit" class="btn btn-info submit" value="Log In" />
              </div>
              <div class="clearfix"></div>
              <div class="separator">
                <div class="clearfix"></div>
                <br />
                <div>
                  <h1><i class="glyphicon glyphicon-cog"></i> Subway Talent Administration</h1>
                  <p>©2017 All Rights Reserved. Privacy and Terms.</p>
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </body>
  <?php if(!@include('templates/footer.php')) throw new Exception("Failed to include 'footer'"); ?>
</html>
