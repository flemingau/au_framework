<?php include(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "/system/_config.php"); ?>
<!-- javascript functions

var strClearInputOriginalValue;
var strClearInputOriginalStyleFontColor;
var	strClearInputOriginalStyleFontSize;
var	strClearInputOriginalStyleFontStyle;
var storedIframeId = "";
var Timer;
var TotalSeconds;
var toTimer;

var originalBackgroundColor = "";

function highlightRow(id) {
	originalBackgroundColor = document.getElementById(id).style.backgroundColor; 
	document.getElementById(id).style.backgroundColor = "#dcf1fa";
}

function resetHighlightRow(id) { 
	document.getElementById(id).style.backgroundColor = originalBackgroundColor;
}

function setCookie(c_name,value,exdays)
{
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;
}

function getCookie(c_name)
{
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++)
	{
	  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
	  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
	  x=x.replace(/^\s+|\s+$/g,"");
	  if (x==c_name)
	    {
	    return unescape(y);
	    }
	  }
}

function filterKeys(id, e, sRegEx)
{
	var keynum = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
	var keychar;
	var charcheck;
	var obj = document.getElementById(id);
	var val = obj.value;
	var regExp = new RegExp(sRegEx);

	keychar = String.fromCharCode(keynum);
	charcheck = regExp;
  
  //alert(keynum); //find keycode 
  //alert(charcheck);

	if(keynum == "8" || keynum == "9" || keynum == "13" || keynum == "35" || keynum == "36" || keynum == "37" || keynum == "39" || keynum == "46") {
		return true;
  } else {
	  return charcheck.test(keychar);
  }
}

function clearChars(id, sRegEx)
{
  var obj = document.getElementById(id);
  var val = obj.value; 
  var regExp = new RegExp(sRegEx);
  
  obj.value = val.replace(regExp,"");
}


function confirmAction(message) {
	var answer = confirm(message)
	if (!answer){
		return false;
	}
}

function clearInput(id)
{
	strClearInputOriginalValue = document.getElementById(id).value;
	strClearInputOriginalStyleFontColor = document.getElementById(id).style.color;
	strClearInputOriginalStyleFontSize = document.getElementById(id).style.fontSize;
	strClearInputOriginalStyleFontStyle = document.getElementById(id).style.fontStyle;
	
	if(strClearInputOriginalValue == "Which School Will You Be Attending?" ||
		strClearInputOriginalValue == "Search by City or School" || 
		strClearInputOriginalValue == "enter e-mail address" ||
		strClearInputOriginalValue == "click here and enter your e-mail address" ||
		strClearInputOriginalValue == "Enter Your E-mail Here" ||
		strClearInputOriginalValue == "yyyy-mm-dd" || 
		strClearInputOriginalValue == "miles" || 
		strClearInputOriginalValue == "Enter Search Terms Here" || 
		strClearInputOriginalValue == "Min Rent" || strClearInputOriginalValue == "Max Rent" ||
		strClearInputOriginalValue == "Min Sq feet" || strClearInputOriginalValue == "Max Sq feet" || 
		strClearInputOriginalValue == "Enter Address or Intersection Here" || 
		strClearInputOriginalValue == "Email Address" || 
		strClearInputOriginalValue == "Add a Message Here" ||
		strClearInputOriginalValue == "Name POI") { 
	  	
	  	document.getElementById(id).value = "";
	  	document.getElementById(id).style.color = "#333333";
	    //document.getElementById(id).style.fontSize = "12px";
	    document.getElementById(id).style.fontStyle = "normal";
    }
}

function resetInput(id)
{
	if(document.getElementById(id).value.replace(/^\s+|\s+$/g, "") == "") {
	
		if(strClearInputOriginalValue == "Which School Will You Be Attending?" ||
		strClearInputOriginalValue == "Enter Your E-mail Here" ||
		strClearInputOriginalValue == "Enter Search Terms Here" ||
		strClearInputOriginalValue == "click here and enter your e-mail address" || 
		strClearInputOriginalValue == "Min Rent" || strClearInputOriginalValue == "Max Rent" ||
		strClearInputOriginalValue == "Min Sq feet" || strClearInputOriginalValue == "Max Sq feet" || 
		strClearInputOriginalValue == "Enter Address or Intersection Here" ||
		strClearInputOriginalValue == "Enter Address" ||
		strClearInputOriginalValue == "Add a Message Here" ||
		strClearInputOriginalValue == "Name POI") { 
			document.getElementById(id).value = strClearInputOriginalValue;
			document.getElementById(id).style.color = strClearInputOriginalStyleFontColor;
		    document.getElementById(id).style.fontSize = strClearInputOriginalStyleFontSize;
		    document.getElementById(id).style.fontStyle = strClearInputOriginalStyleFontStyle;
	    }
    }
}


var picDescriptionId = "";

function hidePanel(id)
{
	document.getElementById(id).style.visibility = "hidden";
	document.getElementById(id).style.display = "none";
}

function showPanel(id,display)
{
	if(display == null) {
		display = "block";
	}
	
	document.getElementById(id).style.display = display;
	document.getElementById(id).style.visibility = "visible";
}

function stateChanged() 
{ 
	if (xmlHttp.readyState==4)
	{
		document.getElementById(picDescriptionId).innerHTML=xmlHttp.responseText;
	}
}


function updateMessageIndicator(action, chainID) {
	switch(action) {
	case "favorite":
		if(document.getElementById(action + "-" + chainID).className == "icon-sm-mailfav") {
			document.getElementById("favorite-" + chainID).className = "icon-sm-mailfavon";
		} else {
			document.getElementById("favorite-" + chainID).className = "icon-sm-mailfav";
		}
		break;
		
	case "delete":
		document.getElementById("message-" + chainID).style.display = "none";
		document.getElementById("message-" + chainID).style.visibility = "hidden";
		break;
		
	default:
		break;
	}
}


function updateFavoriteIndicator(action, type, objID) {
	switch(action) {
	case "favorite":
		if(document.getElementById(action + "-" + type + "-" + objID).className == "icon-sm-fav") {
			document.getElementById(action + "-" + type + "-" + objID).className = "icon-sm-favon";
		} else {
			document.getElementById(action + "-" + type + "-" + objID).className = "icon-sm-fav";
		}
		break;
		
	case "delete":
		document.getElementById("message-" + chainID).style.display = "none";
		document.getElementById("message-" + chainID).style.visibility = "hidden";
		break;
		
	default:
		break;
	}
}


function checkEmail() {
	var email = document.getElementById('splash-email-address');
	var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if (!filter.test(email.value)) {
		alert('Please provide a valid email address');
		email.focus;
		return false;
	}
}

function disableEnterKey(e) {
	var key;
	
	if(window.event) {
		key = window.event.keyCode; //IE
	} else {
		key = e.which; //firefox
	}
	
	if(key != 13) {
		return true;
	} else {
		return false;
	}
}


// -->
