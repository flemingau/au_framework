<?php
	error_reporting(15);

	/* set memory limit */
	ini_set("memory_limit","128M");
	ini_set('max_allowed_packet', '128M');
	ini_set('wait_timeout', '30000');
	set_time_limit(600);
	
	/* Auto-detect the root domain */	
	$http = ((isset($_SERVER['HTTPS']))? "https://" : "http://");
	$uri = parse_url($http.$_SERVER['HTTP_HOST']);
	
	/* Set Main URL and Directory Paths */
	class Configurations {};
	
	$disableWWW			= true;
	$CFG = new Configurations;
	$CFG->root      	= strtolower($http.$_SERVER['HTTP_HOST']."/");
	$CFG->root_no_slash = strtolower($http.$_SERVER['HTTP_HOST']);	
	if($disableWWW) { $CFG->root = preg_replace('/www./', '', $CFG->root, 1); }
	
	// "/home/[username]/domains/domain_name/public_html/"; 
	$CFG->domain		= strtolower(substr($uri['host'], strpos($uri['host'], ".")+1, strlen($uri['host'])));
	
	$CFG->dirroot		= dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
	$CFG->fullname  	= "Example Website";
	$CFG->sitename  	= "example.com";
	$CFG->host      	= "www.".$CFG->domain;
		
	$CFG->incroot   	= $CFG->root . "assets/";
	$CFG->contentpath	= $CFG->root . "content/";
	$CFG->systempath	= $CFG->root . "system/";

	$CFG->csspath   	= $CFG->incroot . "css/";
	$CFG->extpath 		= $CFG->incroot . "ext/";
	$CFG->jspath   		= $CFG->incroot . "js/";
	
	$CFG->img       	= $CFG->contentpath . "img/";
	$CFG->actionpath	= $CFG->contentpath . "actions/";
	$CFG->formpath		= $CFG->contentpath . "forms/";
	
	$CFG->incdir    	= $CFG->dirroot . "assets/";
	$CFG->contentdir	= $CFG->dirroot . "content/";
	$CFG->systemdir		= $CFG->dirroot . "system/";

	$CFG->extdir    	= $CFG->incdir . "ext/";
	$CFG->classdir		= $CFG->incdir . "class/";
	$CFG->cssdir		= $CFG->incdir . "css/";
	$CFG->jsdir			= $CFG->incdir . "js/";

	/* Set Content Directories */
	$CFG->actiondir		= $CFG->contentdir . "actions/";
	$CFG->bodydir		= $CFG->contentdir . "body/";	
	$CFG->commondir		= $CFG->contentdir . "common/";
	$CFG->formdir		= $CFG->contentdir . "forms/";
	$CFG->imgdir		= $CFG->contentdir . "img/";
		
	/* Set Contact Configurations */
	$CFG->admin     	= "info@example.com";
	$CFG->contact   	= "info@example.com";
	$CFG->webmaster 	= "info@example.com";
	
	/* Set Protected Pages */
	$CFG->protected	 	= array("filename-of-page-that-needs-login");
	
	/* Set session timeout */
	$CFG->inactive 		= 172800; //48 hours in seconds
	ini_set('session.gc_maxlifetime', $CFG->inactive);
	
	/* Set Timezone */
	date_default_timezone_set('America/Chicago');
	
	/* Set support E-mail address */
	$CFG->support 		= "info@example.com";
	$CFG->noreply 		= "no-reply@example.com";	
	
	/* Set API Keys */
	$apiKey["fbAppId"] = "exampleApiKey";
	$apiKey["fbSecret"] = "exampleApiKey";
	
?>