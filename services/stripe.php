<?php
// Start Session
session_start();
date_default_timezone_set('UTC');

// Include Config
require('../config.php');

// Include if secured page
include('../templates/secure.php');

require('../classes/Database.php');
require('../classes/Messages.php');

require_once('../vendor/stripe/stripe-php/init.php');

$database = new Database;

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

/*
// LIVE
$stripe = array(
  "pKey" => "pk_live_ngLjHeX9VkdHiXy5LejkcLS0", 
  "secKey" => "sk_live_3zfopf9Zuq5sD2T23GAN6Wpc"
);
*/

// TEST
$stripe = array(
  "pKey" => "pk_test_XXaSPKS1mpUnqk2juZHk4Emp", 
  "secKey" => "sk_test_zjctFNT6vI3PCEZxNrP85aWy"  
);

$stripe_ClientID = array(
  "dev" => "ca_AiPXfFQ7gkSPbjVS39G6l28cno68hl7V",
  "pro" => "ca_AiPXHXNKOno6ajBGcP25Ttmnmf8JSutq"
);

\Stripe\Stripe::setApiKey($stripe["secKey"]);

$acc_id = $get["id"];
$acc = array();

switch (strtoupper($get["a"])) {  
  case "CREATE_STRIPE":  
  	// create a custom connect account
    $acc = \Stripe\Account::create(array(
        "country" => "US", 
        "type" => "custom",         
        "email" => $post["email"]
      ));
    if (array_key_exisits("id", $acc)) {
    	// update column stripeAccount
    	$database->query("UPDATE user_account SET stripeAccount = :stripeAccount WHERE email = :email;");
    	$database->bind(":stripeAccount", $acc["id"]);
    	$database->bind(":email", $post["email"]);
    	$database->execute();

    	Messages::set("Stripe account created!");
    }        
    break;
  case "DELETE_STRIPE":
    $acc = \Stripe\Account::retrieve($acc_id);
    $acc->delete();
    break;  
}
print(json_encode(array(
  "status" => Messages::hasError() ? "error" : "success", 
  "data" => $acc,
  "recordCount" => count($acc),
  "message" => Messages::text()
)));