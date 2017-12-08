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

require_once('vendor/stripe/stripe-php/init.php');

$database = new Database;

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$pageTitle = "Make Payments";

$list = "";

$adminPayPerc = 100;
$database->query("SELECT setting_value FROM subway_settings WHERE setting_name = :setting_name;");
$database->bind(":setting_name", "ADMIN_PAY_PERCENTAGE");
$resultSetting = $database->single();
if ($resultSetting) $adminPayPerc = $resultSetting["setting_value"];
$adminPayPerc = $adminPayPerc/100;

$database->query("SELECT * FROM (
  SELECT event_invites.id AS invite_id, user_account.firstName, user_account.lastName, DATE(user_account.birthday) AS birthdate, user_account.email, user_account.rate AS user_rate, user_account.stripeAccount, user_account.paypalAccount, event.Name AS eventName, event.dateEnd, (SELECT COUNT(id) FROM event_planner WHERE user_id = user_account.userId LIMIT 1) AS eventCount, event_planner.*
    FROM event_invites 
      LEFT JOIN user_account ON event_invites.user_id = user_account.userId 
      LEFT JOIN event ON event_invites.event_id = event.Id 
      LEFT JOIN event_planner ON event.Id = event_planner.event_id
      WHERE event_planner.payment_status = 1
  ) AS payments
  WHERE eventCount = 0
  ORDER BY dateEnd ASC");
$list = $database->resultset();
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
                    <h2><i class="fa fa-credit-card"></i>&nbsp;<?php echo $pageTitle; ?></h2>
                  </div>                  
                </div>
                <div class="row x_content">
                  <?php Messages::display(); ?>
                  <?php if ($id == "") { ?>
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                      <thead>
                        <tr>                                                                              
                          <th>Name</th>
                          <th>Type</th>
                          <th>Event</th>
                          <th>Date End</th>
                          <th style="width: 25%;">Payment</th>                          
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                        $i = 0;
                        foreach($list as $row) : ?>                        
                          <tr>
                            <?php
                            $displayName = trim($row["firstName"]." ".$row["lastName"]);
                            if ($displayName == "") $displayName = $row["email"];
                            if ($displayName == "") $displayName = $row["user_id"];
                            ?>                                                                                      
                            <td><?php echo $displayName; ?></td>
                            <td><?php echo intval($row["eventCount"]) == 0 ? "Talent" : "Planner"; ?></td>
                            <td><?php echo $row["eventName"]; ?></td>
                            <td><?php echo date("Y-m-d", strtotime($row["dateEnd"])); ?></td>
                            <td>
                              <?php
                              $adminPayPercDb = $row["admin_percentage"];                              
                              ?>
                              <ul style="padding-left: 15px;">                                
                               <!-- <li>Total Paid: $--><?php /*echo number_format($row["user_rate"]*(1+$adminPayPerc), 2); */?>
                                <li>Total Paid: $<?php echo number_format($row["user_rate"], 2); ?>
                                    <?php
                                        $admin_fee = number_format($row["user_rate"]*$adminPayPerc, 2);
                                        $talent_fee = number_format($row["user_rate"] - $admin_fee, 2);
                                    ?>
                                  <ul>
                                    <li>Talent Fee: $<?php echo $talent_fee; ?></li>
                                <!--    <li>Talent Fee: $<?php echo number_format($row["user_rate"], 2); ?></li> -->
                                    <li>Income @ %: $<?php echo $admin_fee.",Percentege:".($adminPayPerc*100)."%"; ?>
                                 <!--   <li>Income @ %: $<?php echo number_format($row["user_rate"]*$adminPayPerc, 2)." (".($adminPayPerc*100)."%)"; ?>  -->
                                    </li>
                                  </ul>
                                </li>
                              </ul>
                              <div style="text-align: center;">
                                <?php
                                // check if has valid stripe account and required fields
                                $requiredFields = array();                                
                                if ($row["email"] == "") $requiredFields[] = "Email";

                                if (($row["stripeAccount"] == "") && (count($requiredFields) > 0)) {
                                  echo "<strong>";
                                  echo "The following required field is missing to make payment: ";
                                  for ($j = 0; $j < count($requiredFields); $j++) {
                                    if ($j > 0) echo ", ";
                                    echo $requiredFields[$j];
                                  }
                                  echo "</strong>";                                  
                                }
                                else {
                                  $payment = number_format($row["user_rate"], 2);
                                  $name = $row["firstName"]; // ." ".$row["lastName"];
                                  $email = $row["email"];
                                  $amount = $row["user_rate"];
                                  if ($row["stripeAccount"] == "") {
                                    ?>
                                    <button type="button" id="btn-email-<?php echo $i; ?>" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Sending" onclick="javascript:emailSend('btn-email-<?php echo $i; ?>', '<?php echo $name; ?>', '<?php echo $email; ?>', '<?php echo $amount; ?>');" class="btn btn-info"><i class="fa fa-envelope-o"></i>&nbsp;Send Email</button>                                    
                                    <?php
                                    // TODO: create stripe account
                                  }
                                  else {
                                    // TODO: manage external accounts
                                  }
                              }
                                ?>                                
                              </div>
                            </td>
                          </tr>
                        <?php 
                        $i++;
                        endforeach; ?>
                      </tbody>
                    </table>                  
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
    function emailSend(id, name, email, amount) {
      var btn = $("#" + id);
      btn.button('loading');

      if (!confirm("Send Email to Talent?")) {
        btn.button('reset');
        return;
      }

      $.ajax({
          url: "services/sendswiftmailer.php?a=PAY_SEND",
          method: "POST",
          data: {
            "payName": name, 
            "payEmail": email,
            "payAmount": amount
          },
          success: function(data) {                           
            var response = JSON.parse(data);
            btn.button('reset');

            window.location.href = window.location.href;
          },
          error: function(data) {
            console.log(data);
            btn.button('reset');

            window.location.href = window.location.href;
          }
      });       
    };

    $(document).ready(function() {    
      $("#datatable-buttons").DataTable({
        destroy: true, 
        order: [[ 3, "asc" ]], 
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
