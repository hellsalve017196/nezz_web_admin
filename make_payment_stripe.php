<?php
// Start Session
session_start();
date_default_timezone_set('UTC');

// Include Config
require('config.php');

require('classes/Database.php');
require('classes/Messages.php');

// Include stripe library
require('lib/Stripe/Stripe.php');

$database = new Database;

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$pageTitle = "Make Stripe Payment";

$stripe = array(
  "secret_key"      => "sk_live_3zfopf9Zuq5sD2T23GAN6Wpc",
  "publishable_key" => "pk_live_ngLjHeX9VkdHiXy5LejkcLS0"
);
$apiKey = "pk_live_ngLjHeX9VkdHiXy5LejkcLS0";

if ($_POST) {
  Stripe::setApiKey($apiKey);

  try 
  {
    if (!isset($post['stripeToken'])) Messages::set("The Stripe Token was not generated correctly");    
    else {
      Stripe_Charge::create(array("amount" => 1000,
                                  "currency" => "usd",
                                  "card" => $post['stripeToken']));
      Messages::set("Your payment was successful.", "success");
    }
  }
  catch (Exception $e) 
  {
    Messages::set($e->getMessage());
  }  
}

switch (strtoupper($get["a"])) {
  case "CHECKOUT" :    
    break;  
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>
    <link href="assets/css/style_braintree.css" type="text/css" rel="stylesheet" />
    <title>Subway Talent | <?php echo strtoupper($pageTitle); ?></title>
  </head>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <!-- page content -->
        <div class="col-md-3"></div>
        <div class="col-md-6">        
          <div class="col-middle">
            <div class="x_panel">   
              <div class="row x_title">
                <div class="col-md-10">
                  <h2><i class="fa fa-user"></i>&nbsp;<?php echo $pageTitle; ?></h2>                    
                </div>
                <div class="col-md-2" style="text-align: right;">
                  <a href="make_payment.php" class="btn btn-danger">Back</a>
                </div>                
              </div>              
              <div class="row x_content">                
                <?php Messages::display(); ?>
                <form action="<?php echo $_SERVER['PHP_SELF'].'?iid='.$get["iid"].'&eid='.$get["eid"]; ?>" method="POST" id="payment-form" class="form-horizontal form-label-left">
                  <div class="form-group">
                    <label>Card Number</label>                    
                    <input type="text" size="20" autocomplete="off" required="required" class="form-control card-number" />
                  </div>
                  <div class="form-group">
                    <label>CVC</label>                    
                    <input type="text" size="4" autocomplete="off" required="required" class="form-control card-cvc" />
                  </div>
                  <div class="form-group">
                    <label class="col-md-4">Expiration</label>                    
                    <div class="col-md-8">
                      <div class="col-md-6 col-xs-12 form-group">
                        <input type="text" size="2" class="form-control card-expiry-month" placeholder="MM" />
                      </div>
                      <div class="col-md-6 col-xs-12 form-group">
                        <input type="text" size="4" class="form-control card-expiry-year" placeholder="YYYY" />
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-success submit-button">Submit Payment</button>                  
                </form>                
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3"></div>
        <!-- /page content -->
      </div>
    </div>  
    <?php if(!@include('templates/footer.php')) throw new Exception("Failed to include 'footer'"); ?>    
    <script type="text/javascript">    
    </script>
  </body>
</html>
