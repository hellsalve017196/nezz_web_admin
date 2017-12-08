<?php
// Include Config
require('../config.php');
require('../classes/Database.php');

$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$state_id = $get["state_id"];

$database = new Database;

$database->query("SELECT us_cities_id, city FROM us_cities WHERE state_id = :state_id  ORDER BY city;");
$database->bind(":state_id", $state_id);
$cities = $database->resultset();
print(json_encode($cities));