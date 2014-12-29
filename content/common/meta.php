<?php 
switch( strtolower( $section ) )
{	
	case "about":
		$sectionTitle = "About Us |";
		break;

	default:
		$page = new Page();
		$sectionTitle = ($page->Section!="") ? ucwords($page->Section) . " | " : null;
		$sectionDescription = "section description";
		$sectionKeywords = "section keywords";
		break;
	
}
?>