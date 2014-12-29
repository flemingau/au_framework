<?php
include(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "/system/_config.php");

$login = new Login();
$login->require_login();
?>