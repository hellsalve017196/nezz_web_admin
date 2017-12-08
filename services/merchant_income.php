<?php
date_default_timezone_set('UTC');

// Include Config
require('../config.php');
require('../classes/Database.php');
require('../classes/Messages.php');

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

$range = $post["range"];
$rangeArr = explode(" - ", $range);

$dateFrom = new DateTime($rangeArr[0]);
$dateTo = new DateTime($rangeArr[1]);
$dateTo->modify('+1 day');

$merchant_id = intval($post["merchant"]);

$database = new Database;

$transactions = array();
$dates = array();
$TalentFee = array();
$AdminCharge = array();
$MerchantCharge = array();
$Total = array();

if ((count($rangeArr) == 2) && $merchant_id) {
    $database->query("SELECT payment_name, DATE_FORMAT(planner_payments.date_created,'%m/%d/%Y') AS payment_date, IFNULL(SUM(event_planner.payment_total), 0) AS pay_total, IFNULL(SUM(user_account.rate), 0) AS talent_total, IFNULL(SUM(event_planner.admin_pay_total), 0) AS admin_total, IFNULL(SUM(event_planner.payment_gateway_additional), 0) AS merchant_total FROM planner_payments 
        LEFT JOIN user_account ON planner_payments.user_id = user_account.userId 
        LEFT JOIN payment_methods ON planner_payments.payment_method_id = payment_methods.id 
        LEFT JOIN event_planner ON planner_payments.id = event_planner.planner_payment_id 
    WHERE DATE(planner_payments.date_created) >= :dateFrom AND DATE(planner_payments.date_created) <= :dateTo AND payment_status IN (1, 2) AND planner_payments.payment_method_id = :merchant 
    GROUP BY DATE(planner_payments.date_created), payment_method_id");
    $database->bind(":dateFrom", $dateFrom->format('Y-m-d'));
    $database->bind(":dateTo", $dateTo->format('Y-m-d'));
    $database->bind(":merchant", $merchant_id);
    $transactions = $database->resultset();

    if (count($transactions) > 0) {        
        do {
            $found = false;
            for ($i = 0; $i < count($transactions); $i++) {
                if ($transactions[$i]["payment_date"] == $dateFrom->format("m/d/Y")) {
                    $TalentFee[] = $transactions[$i]["talent_total"];
                    $AdminCharge[] = $transactions[$i]["admin_total"];
                    $MerchantCharge[] = $transactions[$i]["merchant_total"];
                    $Total[] = $transactions[$i]["pay_total"];

                    $found = true;
                    break;
                }
            }
            if (!$found) {                
                $TalentFee[] = 0;
                $AdminCharge[] = 0;
                $MerchantCharge[] = 0;
                $Total[] = 0;                
            }
            
            $dates[] = $dateFrom->format('m/d');            
            $dateFrom->modify('+1 day');            
        }
        while ($dateFrom->format('Y-m-d') != $dateTo->format('Y-m-d'));        
    }
}
else Messages::set("Missing fields");

$legend = array();
$data = array();
for ($i = 1; $i <= 4; $i++) {    
    $name = "";
    $value = array();
    switch ($i) {
        case 1:
            $name = "Talent Fee";
            $value = $TalentFee;
            break;
        case 2:
            $name = "Admin Charge";
            $value = $AdminCharge;
            break;
        case 3:
            $name = "Merchant Charge";
            $value = $MerchantCharge;
            break;
        case 4:
            $name = "Total";
            $value = $Total;
            break;
    }
    $legend[] = $name;
    $data[] = array(
        "name" => $name, 
        "type" => "line", 
        "smooth" => "true", 
        "itemStyle" => array(
                "normal" => array(
                        "areaStyle" => array(
                            "type" => "default"
                            )
                    )
            ), 
        "data" => $value
        );
}
$output = array(
        "legend" => $legend, 
        "xAxisData" => $dates, 
        "data" => $data
    );
print(json_encode(array(
  "status" => Messages::hasError() ? "error" : "success", 
  "data" => $output,
  "recordCount" => count($output["data"]),
  "message" => Messages::text()
)));