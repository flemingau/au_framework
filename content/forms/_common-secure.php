<?php
include(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "/system/_config.php");

$login = new Login();
$login->require_login();	

$arrCategory = array("Apparel","Automotive","Baby Accessories","Beauty","Blended","Books","Classical","DVD","Electronics","Gourmet Food","Grocery","Health/Personal Care","Home/Garden","Industrial","Jewelry","Kitchen","Magazines","Merchants","Miscellaneous","Music","Musical Instruments","Office Products","Outdoor Living","PC Hardware","Pet Supplies","Photo","Shoes","Software","Sporting Goods","Tools","Toys","Unbox Video","Video","Video Games","Watches","Wireless","Wireless Accessories");
$arrCategoryValue = array(
	"Other" => "Other", 
	"Apparel" => "Apparel", 
	"Automotive" => "Automotive", 
	"Baby Accessories" => "Baby", 
	"Beauty" => "Beauty", 
	"Blended" => "Blended", 
	"Books" => "Books", 
	"Classical" => "Classical", 
	"DVD" => "DVD", 
	"Electronics" => "Electronics", 
	"Gourmet Food" => "GourmetFood", 
	"Grocery" => "Grocery", 
	"Health/Personal Care" => "HealthPersonalCare", 
	"Home/Garden" => "HomeGarden", 
	"Industrial" => "Industrial", 
	"Jewelry" => "Jewelry",  
	"Kitchen" => "Kitchen", 
	"Magazines" => "Magazines", 
	"Merchants" => "Merchants", 
	"Miscellaneous" => "Miscellaneous",
	//"Money" => "Money",   
	"Music" => "Music", 
	"Musical Instruments" => "MusicalInstruments", 
	"Office Products" => "OfficeProducts", 
	"Outdoor Living" => "OutdoorLiving", 
	"PC Hardware" => "PCHardware", 
	"Pet Supplies" => "PetSupplies", 
	"Photo" => "Photo", 
	"Shoes" => "Shoes", 
	"Software" => "Software", 
	"Sporting Goods" => "SportingGoods", 
	"Tools" => "Tools", 
	"Toys" => "Toys", 
	"Unbox Video" => "UnboxVideo", 
	"Video" => "Video", 
	"Video Games" => "VideoGames", 
	"Watches" => "Watches", 
	"Wireless" => "Wireless", 
	"Wireless Accessories" => "WirelessAccessories");
$arrCurrency = array('USD', 'AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD','BDT','BGN','BHD','BIF','BMD','BND','BOB','BRL','BSD','BTN','BWP','BYR','BZD','CAD','CDF','CHF','CLP','CNY','COP','CRC','CUP','CVE','CYP','CZK','DJF','DKK','DOP','DZD','EEK','EGP','ERN','ETB','EUR','FJD','FKP','GBP','GEL','GGP','GHS','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','IMP','INR','IQD','IRR','ISK','JEP','JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KWD','KYD','KZT','LAK','LBP','LKR','LRD','LSL','LTL','LVL','LYD','MAD','MDL','MGA','MKD','MMK','MNT','MOP','MRO','MTL','MUR','MVR','MWK','MXN','MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','OMR','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SDG','SEK','SGD','SHP','SLL','SOS','SPL','SRD','STD','SVC','SYP','SZL','THB','TJS','TMM','TND','TOP','TRY','TTD','TVD','TWD','TZS','UAH','UGX','UYU','UZS','VEB','VEF','VND','VUV','WST','XAF','XAG','XAU','XCD','XDR','XOF','XPD','XPF','XPT','YER','ZAR','ZMK','ZWD');

?>
