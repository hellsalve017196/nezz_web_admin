<?php
// Start Session
session_start();
date_default_timezone_set('UTC');

// Include Config
require('config.php');

unset($_SESSION['is_logged_in']);
unset($_SESSION['user_data']);
session_destroy();

header('Location: login.php?a=logout');
