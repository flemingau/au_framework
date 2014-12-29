<?php
	/* session management */
	if(session_id() == null) { session_start(); }
	
	/* load settings configs */
	require_once("_settings.php");
	
	/* load API files */
	require_once($CFG->extdir."facebook/src/facebook.php"); //Facebook Social 

	/* load class files */
	include_once($CFG->classdir."_Exclusive.class.php"); //special classes exclusive to this site
	include_once($CFG->classdir."Common.class.php");
	include_once($CFG->classdir."Database.class.php"); 
	include_once($CFG->classdir."Communication.class.php");
	include_once($CFG->classdir."Login.class.php");
	include_once($CFG->classdir."Page.class.php");
	include_once($CFG->classdir."User.class.php");
	
	/* load database configs */
	require_once("_db.php");
?>