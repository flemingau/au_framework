<?php 
	require("system/_config.php");

	$page = new Page();
	$section = ((isset($_GET["p"])) ? $page->retrieveTrueSection($_GET["p"]) : null);
	$page->generateTemplate($section);
?>
