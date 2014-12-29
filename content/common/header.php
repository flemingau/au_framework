<?php 
	$page = new Page();
	$user = new User();
?>
<!DOCTYPE html>
<html xmlns="//www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">

    <title><?php echo $sectionTitle . $CFG->fullname; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if(isset($sectionDescription)) { ?><meta name="description" content="<?php echo $sectionDescription; ?>"><?php } ?>
    <meta name="keywords" content="<?php echo $sectionKeywords; ?>">
	<meta name="robots" content="index, follow">
	<meta name="author" content="">

	<link rel="canonical" href="<?php echo $CFG->root; ?>" />
	<link rel="shortcut icon" href="<?php echo $CFG->root; ?>favicon.ico" type="image/x-icon" />
	<link rel="icon" href="<?php echo $CFG->root; ?>favicon.ico" type="image/x-icon" />
    
	<!-- fonts -->
    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200,400' rel='stylesheet' type='text/css'>
    
    <!-- stylesheets -->
	<link rel="stylesheet" href="<?php echo $CFG->csspath; ?>styles.css.php" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo $CFG->csspath; ?>styles.css.php" type="text/css" media="print" />
      
   	<!--Scripts-->
	<script type="text/javascript" src="<?php echo $CFG->jspath; ?>util.js.php"></script>
	<script type="text/javascript" src="<?php echo $CFG->jspath; ?>scripts.js.php<?php echo (isset($searchId)) ? "?sid=$searchId" : null; ?>"></script>


	<script type="text/javascript">
	
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-XXXXXXXX-X']);
	  _gaq.push(['_trackPageview']);
	
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	
	</script>
</head>

<body>

<div id="header">
	<div class="row">
		<?php echo $CFG->fullname; ?>
	</div>
	<?php include_once($CFG->commondir."navigation.php"); ?>
</div>

<div id="main">
<!-- end header -->