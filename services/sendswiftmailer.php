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

require_once('../vendor/autoload.php');

$database = new Database;

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$from = "info@subwaytalentapp.com";
$to = $post["payEmail"];
$subject = "";
$content = "";

switch (strtoupper($get["a"])) {
  case "PAY_SEND":
    $subject = "Congratulations! Your payment is almost on the way!";
    $content = "
    <p>Good day!</p>
    <p>Thank you for your service! Your payment of <strong>$".$post["payAmount"]."</strong> is already pending for process. We just need details on where to credit the payment. Next step would be for you to reply to this email your preferred mode of payment:</p>
    <ol>
      <li><strong>PayPal</strong> - please provide your PayPal account/email address</li>
      <li><strong>Bank Account</strong> - please provide the bank account details:
        <ul>
          <li>Bank name</li>
          <li>Account name</li>
          <li>Account number</li>
          <li>Account Type</li>
        </ul>
      </li>
    </ol>
    <p>Once we received your information we can continue with the process.</p>
    <p>Thanks,<br/>Subway Talent Admin</p>";
    break;
}
// allow less secure on your gmail
// https://myaccount.google.com/lesssecureapps?pli=1

// Create the Transport
$transport = (new Swift_SmtpTransport('smtp.gmail.com', 465, "ssl"))
  // set credentials 
  ->setUsername($from)
  ->setPassword('LTrain17');

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);

// Create a message
$message = (new Swift_Message($subject))
->setFrom(array($from => 'Subway Talent Administrator')) // admin email
->setTo(array($to => $post["payName"])) // recipient
->setBcc(array('sam.alhambra@gmail.com', 'subwaytalentapp@gmail.com', 'bens@subwaytalentapp.com', 'benshilaire@gmail.com', 'albernabe@gmail.com')) // bcc admins
->setBody($content, 'text/html');

// for text/plain format
// $message->addPart('My amazing body in plain text', 'text/plain');

if (!$mailer->send($message, $failures)) {
  Messages::set("Failures", "error");
}
else Messages::set("Email sent", "success");

print(json_encode(array(
  "status" => Messages::hasError() ? "error" : "success", 
  "data" => array(
      "body" => $content, 
      "failures" => $failures
    ),
  "recordCount" => 1,
  "message" => Messages::hasError() ? Messages::text() : ""
)));