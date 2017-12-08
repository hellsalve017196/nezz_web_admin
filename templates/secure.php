<?php
// check sessions
if (!isset($_SESSION['user_data']) || (isset($_SESSION['user_data']) && time() > $_SESSION['user_data']['expire'])) {
    // destroy
    session_unset();
    session_destroy();    

    header('Location: login.php?a=session');
}
else {
    // refresh
    $_SESSION['user_data']['expire'] = time() + 3600;
}

function session_userid() {
    if (!isset($_SESSION['user_data'])) return 0;
    return intval($_SESSION["user_data"]["id"]);
}