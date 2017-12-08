<?php

header("Access-Control-Allow-Origin:*");

require_once "DB.php";

$Database = DB::getInstance();

if(isset($_POST['user_id']) and isset($_POST['featured']))
{
    $user_id = $_POST["user_id"];
    $featured = $_POST["featured"];

    if($user_id != '' and $featured != '')
    {
        $Database->query("UPDATE user_account SET user_account.featured = '$featured' WHERE user_account.userId = '$user_id' ");

        echo $featured;
    }


}

else if(isset($_POST['user_id']))
{
    $user_id = $_POST['user_id'];

    if($user_id != '')
    {
        try {

           // echo "INSERT INTO hidden_talent_login (SELECT Id,userid,LastLoggedInDate,Password,LockedOutDate,role_name,date_created FROM user_login WHERE user_login.userid = '$user_id'); INSERT INTO hidden_talent (SELECT user_account.userId,user_account.firstName,user_account.lastName,user_account.email,user_account.birthday,user_account.facebookuser,user_account.userType,user_account.timezone,user_account.rate,user_account.location,user_account.bio,user_account.rating,user_account.profilePic,user_account.mobileNumber,user_account.gender,user_account.talentName,user_account.profilePicTalent,user_account.allow_notif,user_account.allow_email,user_account.cityStateId,user_account.ratingTalent,user_account.stripeAccount,user_account.paypalAccount,user_account.featured FROM user_account WHERE userId = '$user_id');DELETE FROM user_login WHERE user_login.userid = '$user_id';DELETE FROM user_account WHERE user_account.userId = '$user_id'";

            $Database->query("INSERT INTO hidden_talent_login (SELECT Id,userid,LastLoggedInDate,Password,LockedOutDate,role_name,date_created FROM user_login WHERE user_login.userid = '$user_id')");

            $Database->reseting();

            $Database->query("INSERT INTO hidden_talent (SELECT user_account.userId,user_account.firstName,user_account.lastName,user_account.email,user_account.birthday,user_account.facebookuser,user_account.userType,user_account.timezone,user_account.rate,user_account.location,user_account.bio,user_account.rating,user_account.profilePic,user_account.mobileNumber,user_account.gender,user_account.talentName,user_account.profilePicTalent,user_account.allow_notif,user_account.allow_email,user_account.cityStateId,user_account.ratingTalent,user_account.stripeAccount,user_account.paypalAccount,user_account.featured FROM user_account WHERE userId = '$user_id')");

            $Database->reseting();

            $Database->query("DELETE FROM user_login WHERE user_login.userid = '$user_id'");

            $Database->reseting();

            $Database->query("DELETE FROM user_account WHERE user_account.userId = '$user_id'");

            $Database->reseting();



            echo "1";

        }
        catch (Exception $e) {
            echo "0";
        }

    }
}

else if(isset($_POST['title']) && isset($_POST['imageurl']) && isset($_POST['contenturl']) && isset($_POST['sourcee']))
{
    $title = $_POST["title"];
    $imageurl = $_POST["imageurl"];
    $contenturl = $_POST["contenturl"];
    $sourcee = $_POST["sourcee"];

    if(($title != '') and ($imageurl != '') and ($contenturl != '') and ($sourcee != ''))
    {

        $Database->query("INSERT INTO articles VALUES (NULL, '$title', '$contenturl', '$imageurl', '$sourcee')");

        echo "<h2>Successfully added</h2>";
    }
    else {
        echo "such empty";
    }
}
else if(isset($_POST['edit_title']) && isset($_POST['edit_imageurl']) && isset($_POST['edit_contenturl']) && isset($_POST['edit_sourcee']))
{
    $title = $_POST["edit_title"];
    $imageurl = $_POST["edit_imageurl"];
    $contenturl = $_POST["edit_contenturl"];
    $sourcee = $_POST["edit_sourcee"];
    $a_id = $_POST["a_id"];

    if(($title != '') and ($imageurl != '') and ($contenturl != '') and ($sourcee != ''))
    {
        $Database->query("UPDATE articles SET a_title = '$title',a_contenturl = '$contenturl',a_imageurl = '$imageurl',a_source = '$sourcee' WHERE a_id = ".$a_id);

        echo "<h2>Successfully Edited</h2>";
    }
    else {
        echo "such empty";
    }
}
else if(isset($_GET["a_id"]))
{
    $a_id = $_GET["a_id"];

    $Database->query("DELETE FROM articles WHERE a_id =". $a_id);

    header("location:article_list.php");
}
else if(isset($_POST['v_title']) and isset($_POST['videourl']))
{
    $v_src = $_POST['videourl'];
    $v_title = $_POST['v_title'];

    if($v_src != '' and $v_title != '')
    {
        $Database->query("INSERT INTO videos VALUES (NULL ,'$v_src','$v_title')");

        echo "<h2>Successfully Added</h2>";
    }
    else {
        echo "such empty";
    }
}
else if(isset($_POST["v_id"]) and isset($_POST["edit_v_title"]) and isset($_POST["edit_v_src"]))
{
    $v_src = $_POST['edit_v_src'];
    $v_title = $_POST['edit_v_title'];
    $v_id = $_POST['v_id'];

    if($v_id != '' and $v_title != '' and $v_src != '')
    {
        $Database->query("UPDATE videos SET v_title = '$v_title',v_src = '$v_src' WHERE v_id = ".$v_id);

        echo "<h2>Successfully Edited</h2>";
    }
    else {
        echo "Such empty";
    }

}
else if(isset($_GET['delete_video_id']))
{
    $v_id = $_GET['delete_video_id'];

    if($v_id != '')
    {
        $Database->query("DELETE FROM videos WHERE v_id =". $v_id);

        header("location:video_list.php");
    }
}
else if(isset($_POST['push_message']))
{
    $message = $_POST['push_message'];

    if($message != '')
    {
        $Database->query("SELECT device_token FROM naaz_push_notification ORDER BY n_p_id DESC");
        $devices = $Database->results();
        $device_array = array();

        if($Database->count() > 0) {

            foreach ($devices as $d) {
                $device_array[] = $d->device_token;
            }

            require_once 'push_notification/pushnotice.php';

            $push = new PushNotification();
            $data = array('deviceToken' => $device_array,'message' => $_POST['push_message']);
            $push->Notification($data);

            echo "Successfully Send";

        }
        else {
            echo "no device found";
        }

    }
}
else if(isset($_POST['welcome_key']) and isset($_POST['welcome_value']))
{
    $welcome_key = $_POST['welcome_key'];
    $welcome_value = $_POST['welcome_value'];

    if($welcome_key != '' and $welcome_value != '')
    {
        $Database->query("UPDATE document_template SET content = '$welcome_value' WHERE docu_name = '$welcome_key'");

        echo "1";
    }
}
else if(isset($_POST['swap_1']) and isset($_POST['swap_2']))
{
    $swap_1 = $_POST["swap_1"];
    $swap_2 = $_POST["swap_2"];

    if($swap_1 != '' and $swap_2 != '')
    {
        $Database->query("update articles a
 inner join articles b on a.a_id <> b.a_id
   set a.a_title = b.a_title,
       a.a_contenturl = b.a_contenturl,
       a.a_imageurl = b.a_imageurl,
        a.a_source = b.a_source	
 where a.a_id in ($swap_1,$swap_2) and b.a_id in ($swap_1,$swap_2)
");

        echo "1";
    }


}