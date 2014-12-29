<?php 
/* logout */
require_once("system/_config.php");

session_destroy();

header("Location: ".$CFG->root);

?>