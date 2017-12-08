
<?php
// Include Config
require('../config.php');
require('../classes/Database.php');
require('../classes/Messages.php');

$database = new Database;

$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

$id = $get["id"];

switch (strtoupper($get["a"])) {
  case "SAVE" :
    if ($post["password"] != "" && $post["passwordconfirm"] != "") {
      if ($post["password"] != $post["passwordconfirm"]) Messages::set("Please confirm your password");
    }    
    if ($post["email"] == "") Messages::set("Email is required");
    else if ($post["firstname"] == "") Messages::set("First Name is required");
    else if ($post["lastname"] == "") Messages::set("Last Name is required");
    else if ($post["mobileNumber"] == "") Messages::set("Mobile Number is required");
    else if ($post["rate"] == "") Messages::set("Rate is required");
    else if ($post["location"] == "") Messages::set("Location is required");
    else if ($post["city"] == "") Messages::set("City is required");
    else if ($post["bio"] == "") Messages::set("Bio is required");
    else if ($post["profilePic"] == "") Messages::set("Profile Picture is required");
    else if ($post["talentName"] == "") Messages::set("Talent Name is required");
    else if ($post["profilePicTalent"] == "") Messages::set("Talent Profile Picture is required");  

    if ($id == 0) {
      if ($post["email"] != "") {
        $database->query('SELECT * FROM user_login WHERE userid = :userid;');
        $database->bind(':userid', $post["email"]);
        $database->execute();
        if ($database->rowCount() > 0) Messages::set("Email already in use");
      }
      else Messages::set("Email is required");
    }  

    if (!Messages::hasError()) {
      if ($id == 0) {        
        $database->query('INSERT INTO user_account (userId, firstName, lastName, userType, talentName, mobileNumber, birthday, gender, rate, location, cityStateId, bio, profilePic, profilePicTalent) VALUES (:userId, :firstName, :lastName, :userType, :talentName, :mobileNumber, :birthday, :gender, :rate, :location, :cityStateId, :bio, :profilePic, :profilePicTalent);');
        $database->bind(':userId', $post["email"]);
        $database->bind(':firstName', $post["firstname"]);
        $database->bind(':lastName', $post["lastname"]);
        $database->bind(':userType', $post["usertype"]);
        $database->bind(':talentName', $post["talentName"]);
        $database->bind(':mobileNumber', $post["mobileNumber"]);
        $database->bind(':birthday', $post["birthday"]);
        $database->bind(':gender', $post["gender"]);
        $database->bind(':rate', $post["rate"]);
        $database->bind(':location', $post["location"]);
        $database->bind(':cityStateId', $post["city"]);
        $database->bind(':bio', $post["bio"]);
        $database->bind(':profilePic', $post["profilePic"]);
        $database->bind(':profilePicTalent', $post["profilePicTalent"]);
        $database->execute();

        $database->query('INSERT INTO user_login (userid, Password) VALUES (:userid, :Password);');
        $database->bind(':userid', $post["email"]);
        $database->bind(':Password', $post["passwordconfirm"]);
        $database->execute();        
      }
      else {
        // change password
        if ($post["passwordconfirm"] != "")  {
          $database->query('UPDATE user_login SET Password = :Password WHERE Id = :Id;');
          $database->bind(':Password', $post["passwordconfirm"]);
          $database->bind(':Id', $id);
          $database->execute();
        }
        $database->query('SELECT * FROM user_login WHERE Id = :Id;');
        $database->bind(':Id', $id);
        $user = $database->single();        
        // account changes
        if ($database->rowCount() > 0) {
          $database->query('UPDATE user_account SET email = :email, firstName = :firstName, lastName = :lastName, userType = :userType, talentName = :talentName, mobileNumber = :mobileNumber, birthday = :birthday, gender = :gender, rate = :rate, location = :location, cityStateId = :cityStateId, bio = :bio, profilePic = :profilePic, profilePicTalent = :profilePicTalent WHERE userId = :userId;');
          $database->bind(':email', $post["email"]);
          $database->bind(':firstName', $post["firstname"]);
          $database->bind(':lastName', $post["lastname"]);
          $database->bind(':userType', $post["usertype"]);
          $database->bind(':talentName', $post["talentName"]);
          $database->bind(':mobileNumber', $post["mobileNumber"]);
          $database->bind(':birthday', $post["birthday"]);
          $database->bind(':gender', $post["gender"]);
          $database->bind(':rate', $post["rate"]);
          $database->bind(':location', $post["location"]);
          $database->bind(':cityStateId', $post["city"]);
          $database->bind(':bio', $post["bio"]);
          $database->bind(':profilePic', $post["profilePic"]);
          $database->bind(':profilePicTalent', $post["profilePicTalent"]);
          $database->bind(':userId', $user["userid"]);
          $database->execute();
        }
      }
      if ($database->rowCount()) Messages::set($pageTitle." saved", "success");
      else Messages::set($pageTitle." save failed");
    }
    break;  
}
print(json_encode(array(
  "status" => Messages::hasError() ? "error" : "success", 
  "data" => "",
  "recordCount" => "",
  "message" => Messages::text()
)));