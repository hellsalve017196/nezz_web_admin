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

$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

if(isset($get["id"]))
{
    $id = $get["id"];
}

if(isset($get["type"]))
{
    $type = $get["type"];
}


$list = "";

$typeText = $type ==  "T" ? "Talent" : "Planner";
$pageTitle = "User ".$typeText." Profiles";

$database->query("SELECT * FROM (
  SELECT user_login.Id, NULLIF(user_login.LockedOutDate, '0001-01-01 00:00:00') AS LockedOutDateNull, user_login.LockedOutDate, user_login.date_created, NULLIF(user_account.birthday, '0001-01-01 00:00:00') AS birthdayNull, user_account.*, 
  (SELECT url FROM user_external_media WHERE user_id = user_account.userId AND user_external_media.type = 'Y' ORDER BY id DESC LIMIT 1) AS videoSample, 
    (SELECT url FROM user_external_media WHERE user_id = user_account.userId AND user_external_media.type = 'S' ORDER BY id DESC LIMIT 1) AS audioSample, 
    (SELECT IF(COUNT(id) = 0, NULL, COUNT(id)) FROM talent_genres WHERE user_id = user_account.userId LIMIT 1) AS genreCount, 
    (SELECT IF(COUNT(id) = 0, NULL, COUNT(id)) FROM talent_skills WHERE user_id = user_account.userId LIMIT 1) AS skillCount, 
    (SELECT COUNT(id) FROM event_planner WHERE user_id = user_account.userId LIMIT 1) AS eventCount 
    FROM user_login 
  LEFT JOIN user_account ON user_login.userid = user_account.userId
  WHERE user_login.role_name = 'USER'".(isset($id) ? ($id > 0 ? " AND Id = :Id" : "") : "").
    " ORDER BY date_created DESC) AS profile
  WHERE ".($type == "P" ? "eventCount > 0" : "eventCount = 0").";");

if(isset($id)) { if ($id > 0) { $database->bind(':Id', $id); } }

$list = $database->resultset();
if(isset($id)) { if ($id > 0 && $database->rowCount() == 1) {$list = $list[0];} }
if(isset($get["a"])) {
    switch (strtoupper($get["a"])) {
        case "DELETE" :
            $database->query('SELECT * FROM user_login WHERE Id = :Id;');
            $database->bind(':Id', $id);
            $user = $database->single();

            $database->query('DELETE FROM user_login WHERE Id = :Id;');
            $database->bind(':Id', $user["Id"]);
            $database->execute();

            $database->query('DELETE FROM user_account WHERE userId = :userId;');
            $database->bind(':userId', $user["userid"]);
            $database->execute();

            if ($database->rowCount()) if ($database->rowCount()) header('Location: ' . $_SERVER["PHP_SELF"] . "?type=" . $type . "&a=deleteok");
            else Messages::set($pageTitle . " delete failed");
            break;
        case "DELETEOK" :
            Messages::set($pageTitle . " deleted", "success");
            break;
        case "UNLOCK" :
            $database->query('UPDATE user_login SET LockedOutDate = NULL WHERE Id = :Id;');
            $database->bind(':Id', $id);
            $database->execute();

            if ($database->rowCount()) header('Location: ' . $_SERVER["PHP_SELF"] . "?type=" . $type . "&a=unlockok");
            else Messages::set($pageTitle . " unlock failed");
            break;
        case "UNLOCKOK" :
            Messages::set($pageTitle . " unlocked", "success");
            break;
        case "LOCK" :
            $database->query('UPDATE user_login SET LockedOutDate = NOW() WHERE Id = :Id;');
            $database->bind(':Id', $id);
            $database->execute();

            if ($database->rowCount()) header('Location: ' . $_SERVER["PHP_SELF"] . "?type=" . $type . "&a=lockok");
            else Messages::set($pageTitle . " lock failed");
            break;
        case "LOCKOK" :
            Messages::set($pageTitle . " locked", "success");
            break;
    }
}
// 13 items
/*
Basic (First Name, Last Name, City, State, Genre, & Skills). 
Gallery (1 Video link, 1 Audio sample).
About (Profile Image, Username, & Bio). 
*/
$checkArray = array("firstName", "lastName", "email", "mobileNumber", "rate","featured", "location", "cityStateId", "bio", "profilePic", "talentName", "profilePicTalent", "videoSample", "audioSample", "genreCount", "skillCount");

function RenderProfileCompleteness ($checkArray, $items, $size = "progress_sm") {
    $tooltip = "";
    $hasValue = 0;
    for ($i = 0; $i < count($checkArray); $i++) {
        if (array_key_exists($checkArray[$i], $items)) {
            if (!empty($items[$checkArray[$i]])) $hasValue++;
            else {
                if ($tooltip != "") $tooltip .= ", ";
                $tooltip .= $checkArray[$i];
            }
        }
    }
    if ($tooltip != "") $tooltip = "Missing details: ".$tooltip;

    $progressVal = round((float)($hasValue/count($checkArray)) * 100);

    $progressColor = "";
    if ($progressVal <= 33) {
        $progressColor = "progress-bar-danger";
    }
    else if ($progressVal > 33 && $progressVal <= 66) {
        $progressColor = "progress-bar-warning";
    }
    else {
        $progressColor = "progress-bar-success";
    }
    $progress = '';
    $progress .= '<div class="progress '.$size.'" style="width: 100%;">';
    $progress .= '<div class="progress-bar '.$progressColor.'" role="progressbar" data-toggle="tooltip" title="'.$tooltip.'" data-transitiongoal="'.$progressVal.'"></div>';
    $progress .= '</div>';

    return $progress;
}
function CheckProfileCompleteness($checkArray, $items) {

    $hasValue = 0;
    for ($i = 0; $i < count($checkArray); $i++) {
        if (array_key_exists($checkArray[$i], $items)) {
            if (!empty($items[$checkArray[$i]])) $hasValue++;
        }
    }

    return round((float)($hasValue/count($checkArray)) * 100);
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
                                <h2><i class="fa fa-music"></i>&nbsp;<?php echo $pageTitle; ?>&nbsp;<small>manage profiles</small></h2>
                            </div>
                            <div class="col-md-2" style="text-align: right;">
                                <?php if (isset($id) == "") { ?>
                                    <a class="btn btn-success" href="<?php echo $_SERVER['PHP_SELF'].'?type='.$type.'&id=0'; ?>">Add New</a>
                                <?php } else { ?>
                                    <a class="btn btn-default" href="<?php echo $_SERVER['PHP_SELF'].'?type='.$type; ?>">Back</a>
                                <?php } ?>

                                <?php

                                if($_GET['type'] == 'T')
                                {
                                    ?>
                                    <p><input type="checkbox" id="featured_filter"> Featured </p>
                                    <?
                                }

                                ?>


                            </div>
                        </div>
                        <div class="row x_content">
                            <?php Messages::display(); ?>
                            <?php if (isset($id) == "") { ?>
                                <table id="datatable-buttons" class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Profile<br/>Completeness</th>
                                        <th>Name</th>
                                        <?php

                                        if ($type == "T")
                                        {
                                            echo "<th>Stage Name</th>";
                                        }

                                        ?>

                                        <?php

                                        if ($type == "T" or $type == "P")
                                        {
                                            echo "<th>Phone</th>";
                                        }

                                        ?>

                                        <th>Email</th>
                                        <th>Rating</th>
                                        <?php
                                        if ($type == "P") {
                                            echo "<th>Events</th>";
                                        }
                                        ?>
                                        <th>Date Registered</th>

                                        <?php

                                        if($type == "T")
                                        {
                                            ?>

                                            <th>Featured</th>

                                            <?php
                                        }

                                        ?>

                                        <?php

                                        if($type == "T")
                                        {
                                            ?>

                                            <th>Hide</th>

                                            <?php
                                        }

                                        ?>

                                        <th>Action</th>
                                    </tr>
                                    </thead>



                                    <tbody>
                                    <?php foreach($list as $row) : ?>
                                        <tr>
                                            <td>
                                                <?php echo RenderProfileCompleteness($checkArray, $row); ?>
                                            </td>
                                            <?php
                                            $displayName = trim($row["firstName"]." ".$row["lastName"]);
                                            if ($displayName == "") $displayName = $row["email"];
                                            if ($displayName == "") $displayName = $row["userId"];
                                            ?>
                                            <td><?php echo $displayName; ?></td>
                                            <?php
                                            if ($type == "T") {
                                                echo "<td>".$row["talentName"]."</td>";
                                            }
                                            ?>

                                            <?php
                                            if ($type == "T") {
                                                echo "<td>".$row["mobileNumber"]."</td>";
                                            }
                                            ?>

                                            <td><a href="<?php echo $_SERVER['PHP_SELF']."?type=".$type."&id=".$row['Id']; ?>"><i class="fa fa-pencil"></i>&nbsp;<?php echo ($row["email"] == "" ? "NA" : $row["email"]); ?></a></td>
                                            <td><?php echo ($row["rating"] == "" ? "NA" : $row["rating"]); ?></td>
                                            <?php
                                            if ($type == "P") {
                                                echo "<td><a href='event_list.php?u=".$row["userId"]."'>".$row["eventCount"]."</a></td>";
                                            }
                                            ?>





                                            <td><time class="timeago" datetime="<?php echo $row['date_created']; ?>"><?php echo $row["date_created"]; ?></time></td>


                                            <?php

                                            if($type == "T")
                                            {
                                                $name = $row["featured"] == 1 ? "Featured" : "Not Featured";
                                                $color = $row["featured"] == 1 ? "success" : "danger";

                                                ?>

                                                <td><button  class='btn btn-<?php echo $color;?> featured' id="<? echo $row["userId"].",".$row["featured"]; ?>">  <? echo $name;?>  </button></td>

                                                <?

                                            }

                                            ?>



                                            <?php

                                            if($type == "T")
                                            {

                                                ?>

                                                <td><button  class='btn btn-warning' id="<? echo $row['userId'] ?>" onclick="hide('<? echo $row['userId'] ?>')">  Hide </button></td>

                                                <?

                                            }

                                            ?>



                                            <td>
                                                <?php if ($row["LockedOutDate"] != "" && $row["LockedOutDate"] != "0001-01-01 00:00:00") { ?>
                                                    <a onclick="return confirm('Unblock this account?');" href="<?php echo $_SERVER['PHP_SELF'].'?a=unlock&id='.$row['Id']; ?>"><span class="fa fa-unlock"></span> Unblock Account</a><br/>blocked <time class="timeago" datetime="<?php echo $row['LockedOutDate']; ?>"><?php echo $row['LockedOutDate']; ?></time>
                                                <?php } else { ?>
                                                    <a onclick="return confirm('Block this account?');" href="<?php echo $_SERVER['PHP_SELF'].'?a=lock&id='.$row['Id']; ?>"><span class="fa fa-lock"></span> Block Account</a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php } else { ?>
                                <form id="userform" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?a=save&type='.$type.'&id='.$id; ?>" class="form-horizontal form-label-left">
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Profile Completeness</label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <?php echo RenderProfileCompleteness($checkArray, $list, "progress_md"); ?>
                                        </div>
                                    </div>
                                    <?php if ($id != "" && $list["facebookuser"] == 1) { ?>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="facebookid">Facebook ID</label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="facebookid" name="facebookid" class="form-control col-md-7 col-xs-12" <?php echo ($id != 0 ? "readonly='readonly'" : ""); ?> value="<?php echo $list["userId"]; ?>">
                                            </div>
                                        </div>
                                    <?php } ?>


                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">UserId <span class="required">*</span></label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <input type="email" required="required" disabled class="form-control col-md-7 col-xs-12" value="<?php echo $list == "" ? "" : $list["userId"]; ?>">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email <span class="required">*</span></label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <input type="email" id="email" name="email" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $list == "" ? "" : $list["email"]; ?>">
                                        </div>
                                    </div>
                                    <?php if ($id != "" && $list["facebookuser"] == 0) { ?>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password">Password</label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="password" id="password" name="password" class="form-control col-md-7 col-xs-12" <?php echo ($id == 0 ? "required='required'" : ""); ?>>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="passwordconfirm">Confirm Password</label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="password" id="passwordconfirm" name="passwordconfirm" class="form-control col-md-7 col-xs-12" <?php echo ($id == 0 ? "required='required'" : ""); ?>>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="firstname">First Name <span class="required">*</span></label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <input type="text" id="firstname" name="firstname" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo ($id == 0 ? '' : $list['firstName']); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="lastname">Last Name <span class="required">*</span></label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <input type="text" id="lastname" name="lastname" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo ($id == 0 ? '' : $list['lastName']); ?>">
                                        </div>
                                    </div>
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="usertype">User Type</label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <select id="usertype" name="usertype" required="required" class="form-control col-md-7 col-xs-12">-->
<!--                                                <option value="T"--><?php //echo ($list["userType"] == "T" ? " selected" : ""); ?><!--Talent</option>-->
<!--                                                <option value="P"--><?php //echo ($list["userType"] == "P" ? " selected" : ""); ?><!--Planner</option>-->
<!--                                            </select>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="talentName">Talent Name <span class="required">*</span></label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <input type="text" id="talentName" name="talentName" required="required" class="form-control col-md-7 col-xs-12" value="--><?php //echo $list['talentName']; ?><!--">-->
<!--                                        </div>-->
<!--                                    </div>-->
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="mobileNumber">Mobile Number <span class="required">*</span></label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <input type="text" id="mobileNumber" name="mobileNumber" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $list['mobileNumber']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="birthdate">Birthdate</label>
                                        <div class="col-md-6 col-sm-6 col-xs-12 has-feedback">
                                            <input type="text" id="birthdate" name="birthdate" class="form-control col-md-7 col-xs-12 picker_2 has-feedback-left" value="<?php echo ($list['birthdate'] === '' || $list['birthdate'] == '0001-01-01 00:00:00') ? "" : $list['birthdate']; ?>" />
                                            <span class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="gender">Gender</label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <select id="gender" name="gender" class="form-control col-md-7 col-xs-12">
                                                <option></option>
                                                <option value="M"<?php echo ($list["gender"] == "M" ? " selected" : ""); ?>>Male</option>
                                                <option value="F"<?php echo ($list["gender"] == "F" ? " selected" : ""); ?>>Female</option>
                                                <option value="N"<?php echo ($list["gender"] == "N" ? " selected" : ""); ?>>NA</option>
                                            </select>
                                        </div>
                                    </div>
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="rate">Rate <span class="required">*</span></label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12 has-feedback">-->
<!--                                            <input type="text" id="rate" name="rate" required="required" class="form-control col-md-7 col-xs-12 has-feedback-left" value="--><?php //echo ($list['rate'] === '') ? "" : $list['rate']; ?><!--" />-->
<!--                                            <span class="fa fa-dollar form-control-feedback left" aria-hidden="true"></span>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="location">Location <span class="required">*</span></label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <textarea rows="3" id="location" name="location" class="form-control col-md-7 col-xs-12" placeholder="Street Address">--><?php //echo $list['location']; ?><!--</textarea>-->
<!--                                        </div>-->
<!--                                    </div>-->



                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="state">State</label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <?php
                                            $state_id = "";
                                            if ($list["cityStateId"] != "") {
                                                $database->query("SELECT state_id FROM us_cities WHERE us_cities_id = :us_cities_id LIMIT 1;");
                                                $database->bind(":us_cities_id", $list["cityStateId"]);
                                                $state_id = $database->single();
                                                if (is_array(($state_id))) $state_id = $state_id["state_id"];
                                            }
                                            ?>
                                            <select id="state" name="state" class="form-control col-md-7 col-xs-12">
                                                <option></option>
                                                <?php
                                                $database->query("SELECT DISTINCT state_id, state_name FROM us_cities ORDER BY state_name;");
                                                $states = $database->resultset();
                                                foreach ($states as $state) :
                                                    ?>
                                                    <option value="<?php echo $state['state_id']; ?>"<?php echo ($state_id == $state['state_id'] ? " selected='selected'" : ""); ?>><?php echo $state["state_name"]; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="city">City <span class="required">*</span></label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <select id="city" name="city" class="form-control col-md-7 col-xs-12" required="required">
                                                <option></option>
                                                <?php
                                                if ($state_id != "") {
                                                    $database->query("SELECT us_cities_id, city FROM us_cities WHERE state_id = :state_id ORDER BY city;");
                                                    $database->bind(":state_id", $state_id);
                                                    $cities = $database->resultset();
                                                    foreach ($cities as $city) :
                                                        ?>
                                                        <option value="<?php echo $city['us_cities_id']; ?>"<?php echo ($list["cityStateId"] == $city['us_cities_id'] ? " selected='selected'" : ""); ?>><?php echo $city["city"]; ?></option>
                                                        <?php
                                                    endforeach;
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bio">Bio <span class="required">*</span></label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <textarea rows="3" id="bio" name="bio" required="required" class="form-control col-md-7 col-xs-12">--><?php //echo $list['bio']; ?><!--</textarea>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ratings">Rating</label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <div class="form-control">-->
<!--                                                --><?php
//                                                $rating = $list['rating'];
//                                                if ($rating == "") $rating = 0;
//                                                ?>
<!--                                                <a>--><?php //echo $list['rating']; ?><!--</a>&nbsp;-->
<!--                                                --><?php
//                                                for ($i = 1; $i <= 5; $i++) {
//                                                    echo "<a href='#''><span class='glyphicon glyphicon-star".($i > floor($list['rating']) ? "-empty" : "")."'></span></a>";
//                                                }
//                                                ?>
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="profilePic">Profile Pic <span class="required">*</span></label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <input type="file" id="profilePic" name="profilePic" class="form-control col-md-7 col-xs-12" value="<?php echo $list['profilePic']; ?>">
                                            <?php
                                            if ($list['profilePic'] != "") {
                                                echo "<img src='".$list['profilePic']."' class='img-responsive' alt='profile Pic' />";
                                            }
                                            ?>
                                        </div>
                                    </div>
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="profilePicTalent">Profile Talent <span class="required">*</span></label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <input type="text" id="profilePicTalent" name="profilePicTalent" class="form-control col-md-7 col-xs-12" value="--><?php //echo $list['profilePicTalent']; ?><!--">-->
<!--                                            --><?php
//                                            if ($list['profilePicTalent'] != "") {
//                                                echo "<img src='".$list['profilePicTalent']."' class='img-responsive' alt='profile Talent' />";
//                                            }
//                                            ?>
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="genre">Genre <span class="required">*</span></label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <div class="form-control">-->
<!--                                                --><?php
//                                                echo $list["genreCount"];
//                                                $database->query("SELECT genre.Name FROM talent_genres
//                          LEFT JOIN genre ON genre.Id = talent_genres.genre_id
//                          WHERE user_id = :user_id;");
//                                                $database->bind(":user_id", $list["userId"]);
//                                                $genres = $database->resultset();
//
//                                                $first = true;
//                                                foreach ($genres as $genre) {
//                                                    if (!$first) echo ", ";
//                                                    else {
//                                                        $first = false;
//                                                        echo " - ";
//                                                    }
//                                                    echo $genre["Name"];
//                                                }
//                                                ?>
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="genre">Skills <span class="required">*</span></label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <div class="form-control">-->
<!--                                                --><?php
//                                                echo $list["skillCount"];
//                                                $database->query("SELECT skills.Name FROM talent_skills
//                          LEFT JOIN skills ON skills.Id = talent_skills.skill_id
//                          WHERE user_id = :user_id;");
//                                                $database->bind(":user_id", $list["userId"]);
//                                                $skills = $database->resultset();
//
//                                                $first = true;
//                                                foreach ($skills as $skill) {
//                                                    if (!$first) echo ", ";
//                                                    else {
//                                                        $first = false;
//                                                        echo " - ";
//                                                    }
//                                                    echo $skill["Name"];
//                                                }
//                                                ?>
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="genre">Audio Sample <span class="required">*</span></label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <div class="form-control">-->
<!--                                                --><?php //if ($list['audioSample'] != "") { ?>
<!--                                                    <a href="--><?php //echo $list['audioSample']; ?><!--" target="_blank">Click link to view Sample</a>-->
<!--                                                --><?php //} else { echo "None yet"; } ?>
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="form-group">-->
<!--                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="genre">Video Sample <span class="required">*</span></label>-->
<!--                                        <div class="col-md-6 col-sm-6 col-xs-12">-->
<!--                                            <div class="form-control">-->
<!--                                                --><?php //if ($list['videoSample'] != "") { ?>
<!--                                                    <a href="--><?php //echo $list['videoSample']; ?><!--" target="_blank">Click link to view Sample</a>-->
<!--                                                --><?php //} else { echo "None yet"; } ?>
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
                                    <div class="ln_solid"></div>
                                    <div class="form-group">
                                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                            <a class="btn btn-default" href="<?php echo $_SERVER['PHP_SELF']."?type=".$type; ?>">Back</a>
                                            <button type="submit" class="btn btn-success">Save</button>
                                            <?php if ($id > 0) { ?>
                                                <input type="hidden" id="uid" name="uid" value="<?php echo $id; ?>" />
                                                <input type="hidden" id="utype" name="utype" value="<?php echo $type; ?>" />
                                                <button type="button" class="btn btn-danger" onclick="redirectOnConfirm('Delete this account?', '<?php echo $_SERVER['PHP_SELF']."?a=delete&type=".$type."&id=".$id; ?>');">Delete</button>
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
<script type="text/javascript">
    $(document).ready(function() {
        $('#birthdate').daterangepicker({
            singleDatePicker: true,
            singleClasses: "picker_2"
        }, function(start, end, label) {
            console.log(start.toISOString(), end.toISOString(), label);
        });
        $('[data-toggle="tooltip"]').tooltip();

        table = $("#datatable-buttons").DataTable({
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
                }
            ],
            responsive: true
        });

    });
    $('#state').change(function() {
        $('#city').prop('disabled', 'disabled');
        $.ajax({
            url: "services/us_cities.php?state_id=" + $("#state").val(),
            method: "GET",
            dataType: "json",
            success: function(data) {
                $('#city').empty();
                $(data).each(function()
                {
                    $('#city').append('<option value=' + this.us_cities_id + '>' + this.city + '</option>');
                });
                $('#city').prop('disabled', false);
            },
            error: function(data) {
                console.log(data);
            }
        });
    });
    $("#userform").submit(function(event) {
        $.post({
            url: "services/user_profile.php?a=save&type=" + $("#utype").val() + "&id=" + $("#uid").val(),
            data: $('form#userform').serialize(),
            dataType: "json",
            success: function (data) {
                window.location = window.location;
            }
        });
    });
</script>



<!-- adding featured list -->
<script>

    var hide = function(user_id)
    {
        req = new XMLHttpRequest();

        req.onreadystatechange = function() {

            if(req.status == 200 && req.readyState == 4)
            {
                if(req.responseText == '1')
                {
                    window.location = "<?php echo constant("ROOT_URL"); ?>"+"user_profile.php?type=T";
                }
                else {
                    alert("isse with the server");
                }


            }

        };

        req.open("post","<?php echo constant("ROOT_URL"); ?>"+"new_features.php",false);
        req.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        req.send("user_id="+user_id);
    };


    $(document).ready(

        function () {
            console.log("ready to operate");

            var featured_filter = false;

            $.fn.dataTable.ext.search.push(
                function( settings, data, dataIndex ) {

                    if(featured_filter)
                    {
                        if($.trim(data[7]) == "Featured")
                        {
                            return true;
                        }
                        else {
                            return false;
                        }
                    }
                    else {
                        return true;
                    }

                }
            );


            $("#featured_filter").on('click',function () {

                featured_filter = !featured_filter;
                table.draw();

            });



            $(".featured").on("click",function() {

                alert("works");

                data = $(this).attr("id").split(",");
                user_id = data["0"];
                featured = data["1"];

                var req = new XMLHttpRequest();

                switch(featured)
                {
                    case '0':
                        featured = '1';
                        break;
                    case '1':
                        featured = '0';
                        break;
                    default:
                        break;
                }

                current = $(this);

                req.onreadystatechange = function()
                {
                    if(req.status == 200 && req.readyState == 4)
                    {
                        $(current).attr("id",user_id+","+featured);

                        if(featured == '1')
                        {
                            $(current).attr('class','btn btn-success featured');
                            $(current).html("Featured");
                        }
                        else {
                            $(current).attr('class','btn btn-danger featured');
                            $(current).html("Not Featured");
                        }

                    }
                };

                req.open("post","<?php echo constant("ROOT_URL"); ?>"+"new_features.php",false);
                req.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                req.send("user_id="+user_id+"&featured="+featured);



            });



        }

    );


</script>















</body>
</html>