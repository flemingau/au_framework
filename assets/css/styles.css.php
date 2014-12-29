<?php 
	header("Content-type: text/css");
	include("../../system/_config.php"); 
?>
@charset "utf-8";
/* CSS Document */

body {
	font-family: 'Source Sans Pro', sans-serif;
	font-weight: 200;
	margin: 0;
}

ul#navigation {
	display: block;
	margin: 0;
	margin-left: 10px;
	padding: 0;
}

ul#navigation li {
	display: inline-block;
}

ul#navigation li a {
	background: #777;
	color: #fff;
	display: inline-block;
	font-weight: bold;
	padding: 5px 10px;
	text-decoration: none;
}

ul#navigation li a:hover {
	background: #333;
}

#footer {
	background: #333;
	bottom: 0;
	color: #fff;
	height: 50px;
	position: absolute;
	display: block;
	width: 100%;
}

#footer .row {
	padding: 10px;
}

#header {
	background: #ddd;
	border-bottom: 1px solid #777;
}

#header .row {
	display:block;
	font-size: 2.5em;
	margin-bottom: 10px;
	margin-left: 7px;
}

#main {
	margin-bottom: 10px;
	margin-left: auto;
	margin-right: auto;
	width: 90%;
}