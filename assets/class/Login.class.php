<?php
	
/**
* Class used to handle user authentication
*/
class Login {
	
	function __construct() {
		$this->function = "login";
		$this->currentPage = "default";
		$this->lblValueEmail = "E-mail Address:";		
		$this->lblValuePassword = "Password:";	
		$this->btnValueSubmit = "Login";
		$this->displayButton = true;
		$this->loggedIntoFacebook = false;
	}
	
	
	/*
	 * Display login prompt
	 */
	public function displayLogin()
	{
		global $CFG;
		$page = new Page();
		
		if($this->is_logged_in()) {
			echo "no login prompt needed";
		} else {
			echo "<form id=\"form-login\" name=\"form-login\" method=\"post\" action=\"" . $CFG->actionpath . "authenticate.php\">";
			//$this->generateFormElement($type, $name, $function, $page)
			echo"<div>";
			$page->generateFormElement("label", "email", $this->function, $this->currentPage, null, $this->lblValueEmail);
			$page->generateFormElement("textbox", "email", $this->function, $this->currentPage, "Email");
			echo "</div><div>";
			$page->generateFormElement("label", "password", $this->function, $this->currentPage, null, $this->lblValuePassword);
			$page->generateFormElement("password", "password", $this->function, $this->currentPage, "Password");
			echo "</div><div>";
			$page->generateFormElement("label", null, $this->function, $this->currentPage, null, null);
			echo "<div style=\"display:inline-block;margin:0px 0px 10px 5px;zoom:1*display:inline;\">
				<a href=\"$CFG->root"."forgot-password/\">Forgot your password?</a>
			</div>";
			echo "</div>";
			if($this->displayButton) {
				$page->generateFormElement("button", "submit", $this->function, $this->currentPage, null, $this->btnValueSubmit);
			}
			echo "</form>";
		}
	}
	
	
	/*
	 * Display Facebook Login Status
	 */
	public function isLoggedIntoFacebook() {
		$fb = new FacebookConnect();

		$user = $fb->getUser(); //detects if user is logged into facebook
		$user_profile = null;
		$app_scope = $fb->app_scope;
		
		if($user) {		
			try {
				$user_profile = $fb->api('/me');
			} catch (FacebookApiException $e) {
				$user = null;
			}
		} 
		
		if(!$user_profile) { 
			//display this section when not logged into Facebook
			//if logged into roomaloo jump to user dashboard
			//popup login works.
			//echo "no user_profile";

			echo "<div id=\"fb-root\"></div>";
			
			echo "<script>               
		      window.fbAsyncInit = function() {
		        FB.init({
		          appId: '".$fb->getAppID()."', 
		          cookie: true, 
		          xfbml: true,
		          oauth: true
		        });
		      };
		      
		      // Load the SDK Asynchronously
			  (function(d){
			     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
			     if (d.getElementById(id)) {return;}
			     js = d.createElement('script'); js.id = id; js.async = true;
			     js.src = \"//connect.facebook.net/en_US/all.js\";
			     ref.parentNode.insertBefore(js, ref);
			   }(document));
		    </script>";
			return false;
		} else {
			return true;
		}
	}
	
	
	/*
	 * Display Facebook Login
	 */
	public function displayFacebookLogin($redirectUrl, $message=null) {
		global $CFG;
		global $apiKey;

		$fb = new FacebookConnect();

		$user = $fb->getUser(); //detects if user is logged into facebook
		$user_profile = null;
		$app_scope = $fb->app_scope;
		
		if($user) {		
			try {
				//Proceed knowing you have a logged in user who's authenticated.
				$user_profile = $fb->api('/me');
				//echo $user_profile["name"];
			} catch (FacebookApiException $e) {
				//echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
				$user = null;
			}
		} 
		
		if(!$user_profile) { 
			//display this section when not logged into Facebook
			//if logged into roomaloo jump to user dashboard
			//popup login works.
			//echo "no user_profile";

			echo "<div id=\"fb-root\"></div>";
			
			echo "<script>               
		      window.fbAsyncInit = function() {
		        FB.init({
		          appId: '".$fb->getAppID()."', 
		          cookie: true, 
		          xfbml: true,
		          oauth: true
		        });
		        FB.Event.subscribe('auth.login', function(response) {
		        	window.location = '$redirectUrl';
		        });
		        FB.Event.subscribe('auth.logout', function(response) {
		        	window.location = '".$CFG->root."';
		        });
		      };
		      
		      // Load the SDK Asynchronously
			  (function(d){
			     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
			     if (d.getElementById(id)) {return;}
			     js = d.createElement('script'); js.id = id; js.async = true;
			     js.src = \"//connect.facebook.net/en_US/all.js\";
			     ref.parentNode.insertBefore(js, ref);
			   }(document));
		    </script>";
			$this->loggedIntoFacebook = false;
		} else {
			$this->loggedIntoFacebook = true;
		}
	}
	
	
	public function getFacebookPopupLink($redirectUrl) {
		global $CFG;
		global $apiKey;
	
		$redirect_uri = urlencode($redirectUrl);
    	$state = md5($_SERVER["REMOTE_ADDR"]);
    	$scope = urlencode("user_activities,user_birthday,user_checkins,user_education_history,user_events,user_groups,user_hometown,user_interests,user_likes,user_location,user_notes,user_photos,user_questions,user_relationships,user_relationship_details,user_religion_politics,user_status,user_subscriptions,user_videos,user_website,user_work_history,email,read_friendlists,read_insights,read_mailbox,read_requests,read_stream,xmpp_login,ads_management,create_event,manage_friendlists,manage_notifications,user_online_presence,friends_online_presence,publish_checkins,publish_stream,rsvp_event");
    	$display = "popup";
    	
    	$fbAuthLink = "http://www.facebook.com/dialog/oauth/?client_id=$apiKey[fbAppId]&redirect_uri=$redirect_uri&state=$state&scope=$scope&display=$display";
    	
    	return $fbAuthLink;
	}
	
	
	/*
	 * Checks to see if page requires login
	 */
	public function checkLoginRequirement($page) 
	{
		global $CFG;
		
		$arrProtected = $CFG->protected;
		$boolProtected = false;	
		
		if(in_array(trim($page),$arrProtected)) {
			$boolProtected = true;
		}
	
		if($boolProtected) {
			$this->manageSession();
			if(!$this->is_logged_in()) {
				//$common = new Common();
				//$currentURL = $common->getCurrentURL(); 
				//$encodedURL = urlencode($currentURL);
				//$_SESSION["lref"] = $encodedURL;
				//header("location: " . $CFG->root . "login/");
				$_SESSION["lastUrl"] = $_SERVER["REQUEST_URI"];
				header("location: " . $CFG->actionpath . "connect-facebook.php"); 
			} else {
				$_SESSION['timeout'] = time() + $CFG->inactive; //set time session should timeout
				$_SESSION["lastUrl"] = $_SERVER["REQUEST_URI"];
				
			}
		}
	}
	
	
	/*
	 * Manage session
	 */
	public function manageSession() {
		
		global $CFG;
		
		// check to see if $_SESSION['timeout'] is set
		if(isset($_SESSION['timeout']) ) {
			//$session_life = time() - $_SESSION['timeout'];
		
			if(time() >= $_SESSION['timeout']) { //if current time has exceeded timeout time then destroy session
				session_destroy();
				header("Location: " . $CFG->root . "logout.php");
				die; 
			} else { //extend the session based on inactive time
				$_SESSION['timeout'] = time() + $CFG->inactive;
			}
		}
	}
	
	
	/*
	 * Check if the current user is logged in
	 */
	public function is_logged_in() {
		//if($this->is_logged_in_locally() || $this->is_logged_into_fb()) {
		if($this->is_logged_in_locally()) {
			return true;
		} else {
			return false;
		}
	}
	
	public function is_logged_in_locally() {
	   global $_SESSION;

	   return isset($_SESSION["LOGIN"]) && isset($_SESSION["LOGIN"]["ID"]) && !empty($_SESSION["LOGIN"]["ID"])
	   	 && isset($_SESSION["LOGIN"]["Validated"]) && !empty($_SESSION["LOGIN"]["Validated"]) 
	   	 && !isNull($_SESSION["LOGIN"]["Validated"]);
		  
	}
	
	public function is_logged_into_fb() {
		global $apiKey;
	
		$facebook = new Facebook(array(
			'appId'  => $apiKey["fbAppId"],
			'secret' => $apiKey["fbSecret"]
		));
		
		$user = $facebook->getUser();
		$user_profile = null;
		
		if ($user) {
		  try {
		    // Proceed knowing you have a logged in user who's authenticated.
		    $user_profile = $facebook->api('/me');
		  } catch (FacebookApiException $e) {
		    $user = null;
		  }
		}
		
		
		if($user) {
			return true;
		} else {
			return false;
		}
		
	}
	
	

	/*
	 * Display the login form unless the user is logged in
	 */
	public function require_login() {
	
	   global $CFG, $HTTP_HOST, $REQUEST_URI, $PHP_SELF;
	
		if (!$this->is_logged_in()) {
			//$login = new Login();
			//$login->displayLogin();
			//die;
			header("location: " . $CFG->actionpath . "connect-facebook.php");
		}
		
		/*
	   if (!$this->is_logged_in()) {
	      $path = isset($REQUEST_URI) ?
		 strip_querystring($REQUEST_URI) : $PHP_SELF;
	      header("Location: $CFG->root"."log-in/");
	   }*/
	}
	
	
	
	
	
	
	
	public function strip_querystring($url) {
		if ($commapos = strpos($url, '?')) {
			return substr($url, 0, $commapos);
		} else {
			return $url;
		}
	}
	
	public function email_exists($Email) {
	/* returns true the email address exists */
	
	   $qid = db_query("SELECT 1 FROM Users WHERE Email = '$Email'");
	   return db_num_rows($qid);
	}
	
	public function valid_email($Email) {
	/* returns true if the email address has a valid format */
	
	   return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $Email);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	* Check if user is currently authenticated
	*/
	
	public static function isUserAuthenticated() {
		return (isset($_SESSION['auth_level']) && $_SESSION['auth_level'] > 0);
	}
		
	/**	
	* Attempt to authenticate a username and password. Note that	
	* password should be a 32-character MD5 hash of the plaintext pw	
	*/	
	public static function authenticate($username, $password) {
		
		$query = "SELECT COUNT(ID) FROM Users WHERE Username = ? AND Password = ?";
		$params = array($username, $password);	
		
		$db = new Database;		
		$results = $db->execute_query($query, $params);	
		
		if(($row = $results->fetchRow()) && ($row[0] == 1)) {
			$_SESSION['auth_level'] = 1;		
			return true;		
		}
		
		// Maybe plaintext got sent, try again using hash		
		$params = array($username, strtolower(md5($password)));		
		$results = $db->execute_query($query, $params);
		
		if(($row = $results->fetchRow()) && ($row[0] == 1)) {		
			$_SESSION['auth_level'] = 1;
			return true;			
		}
	
		return false;
	}	

}

?>