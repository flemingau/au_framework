<?php
class Common
{
	public function scrapeUrl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 7.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$raw = curl_exec($ch);
		curl_close($ch);
		
		$newlines = array("\t","\n","\r");
		$search = array(chr(0xe2) . chr(0x80) . chr(0x98), 
					chr(0xe2) . chr(0x80) . chr(0x99),
					chr(0xe2) . chr(0x80) . chr(0x9c),
					chr(0xe2) . chr(0x80) . chr(0x9d),
					chr(0xe2) . chr(0x80) . chr(0x93),
					chr(0xe2) . chr(0x80) . chr(0x94));
		$replace = array('&lsquo;','&rsquo;','&ldquo;','&rdquo;','&ndash;','&mdash;');
		
		$fullpage = str_replace($newlines, "", html_entity_decode($raw));
    	$fullpage = str_replace($search, $replace, $fullpage);
    	
    	return $fullpage;
	}
	
	
	public function simulateLogin($params) {
		
		if(empty($params->url)) {
			return 'Error: invalid Url';
		}
		
		$output = "blank";
		//$proxyauth = 'user:password';
		
		$ch = curl_init();
		$ua='Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.7) Gecko/2009021910 Firefox/3.0.7 (.NET CLR 3.5.30729)';
//		$header = array(
//			"GET / HTTP/1.1",
//			"Host: www.google.com",
//			"Connection: keep-alive",
//			"Cache-Control: max-age=0",
//			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,/;q=0.8",
////			"Accept-Encoding: gzip,deflate,sdch",
//			"Accept-Language: en-US,en;q=0.8",
//			"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3",
//			"Content-type: application/json"
//		);
		
		$params->proxy = (!is_null($params->proxy)) ? $params->proxy : $_SERVER['SERVER_ADDR'] . ":80"; 
		$params->cookie_file = (!is_null($params->cookie_file)) ? $params->cookie_file : $params->cookie_jar;
		
		$is_post_action = (strtolower($params->form_action) == "post") ? 1 : 0;
		
		$fields_string = ""; 
		
		if(!is_null($params->post_fields)) {
			foreach($params->post_fields as $key=>$value) { 
				$fields_string .= "$key=$value&"; 
			}
			$fields_string = rtrim($fields_string, "&");
		}
		
		$proxy_user_password = (trim($params->proxy_user) != "" || trim($params->proxy_pass) != "") ? "$params->proxy_user:$params->proxy_pass" : null;
		
		echo $proxy_user_password;
		
		$opt=array(
			CURLOPT_URL=>$params->url,
			CURLOPT_PROXY=>$params->proxy, 
			CURLOPT_PROXYUSERPWD=>$proxy_user_password,
			CURLOPT_COOKIEJAR=>$params->cookie_jar,
			CURLOPT_COOKIEFILE=>$params->cookie_file,
			CURLOPT_POSTFIELDS=>$fields_string,
			CURLOPT_USERAGENT=>$ua,
			CURLOPT_POST=>$is_post_action,
// 			CURLOPT_CUSTOMREQUEST=>$params->form_action, //some proxies can't handle custom requests
			CURLOPT_FOLLOWLOCATION=>1,
			CURLOPT_RETURNTRANSFER=>1,
			CURLOPT_REFERER=>$params->referer,
			CURLOPT_SSL_VERIFYPEER=>false,
			CURLOPT_FRESH_CONNECT=>1,
			CURLOPT_HTTPHEADER=>$params->header,
 			CURLOPT_HEADER=>0
			//CURLOPT_PROXYUSERPWD=>$proxyauth,
		);
		curl_setopt_array($ch, $opt);
		$output = curl_exec($ch);
		
		if(trim(curl_error($ch)) != "") {
//			echo "Custom Error: ";
//			echo curl_error($ch);
			return false;
		} else {
			return $output;
		} 
	}
	
	
	public function decode_special_ascii_hex($string)
	{
		return preg_replace_callback(
	    	'#\\\\x([[:xdigit:]]{2})#ism', 
			create_function(
				'$matches',
	        	'return chr(hexdec($matches[1]));'
	    	),
	    	$string
	    );
	}

	
	public function parseFormElements($params) {
		
		$content = $params->content;
		$rgx_pattern = '/<form(.*?)<\/form>/is';
		preg_match_all($rgx_pattern, $content, $matched_content);
		
// 		var_dump($matched_content);
		
		$form_objects = new stdClass;
		
		$form_content = $matched_content[0];
		
		for($x=0; $x < count($form_content); $x++) {
			
			$form_elements_array = array();
			
//			var_dump($form_content[$x]);
			
			$rgx_name_pattern = '/name="(.*?)"/is';
			preg_match($rgx_name_pattern, $form_content[$x], $matched_form_name); //get form name
			$form_elements_array["form_name"] = trim($matched_form_name[1]);
			
			$rgx_action_pattern = '/action="(.*?)"/is';
			preg_match($rgx_action_pattern, $form_content[$x], $matched_action_content); //get action link
			$form_elements_array["action_path"] = trim($matched_action_content[1]);
			
			$rgx_inputs_pattern = '/<input(.*?)>/is';
			preg_match_all($rgx_inputs_pattern, $form_content[$x], $matched_inputs_content); //get input names
// 			$form_elements_array["inputs"] = $matched_inputs_content[1];
			$form_elements_array["inputs"] = array();
			
			for($y=0, $num_matched_inputs=count($matched_inputs_content[1]); $y < $num_matched_inputs; $y++) {
				
				$input_elements_object = new stdClass;
				$input_elements_array = preg_split( "/(\" | \"|=)/", trim( $matched_inputs_content[1][$y] ) );
				
				for($i=0, $num_input_elements=count($input_elements_array); $i < $num_input_elements; $i++) {
					if(fmod($i,2)==0) {
						
						$key = trim($input_elements_array[$i], "\" \/"); 
						$value = trim($input_elements_array[$i+1], "\" \/");
						
						if($key !== "") {
							$input_elements_object->{$key} = $value;
						}
					} 
				}
				
				array_push($form_elements_array["inputs"], $input_elements_object);
			}
			
			$form_objects->{$form_elements_array["form_name"] . $x} = $form_elements_array;
			
		}
		
		return $form_objects;
	}
	

	public function convertSimpleXmlToArray($xml) {
		if (get_class($xml) == 'SimpleXMLElement') {
			$attributes = $xml->attributes();
			foreach($attributes as $k=>$v) {
				if ($v) $a[$k] = (string) $v;
			}
			$x = $xml;
			$xml = get_object_vars($xml);
		}
		
		if (is_array($xml)) {
			if (count($xml) == 0) return (string) $x; // for CDATA
			foreach($xml as $key=>$value) {
				$r[$key] = $this->convertSimpleXmlToArray($value);
			}
			if (isset($a)) $r['@attributes'] = $a;    // Attributes
			return $r;
		}
		
		return (string)$xml;
	}
	
	
	public function stripInvalidXml($value) {
	    $ret = "";
	    $current;
	    if (empty($value)) 
	    {
	        return $ret;
	    }
	 
	    $length = strlen($value);
	    for ($i=0; $i < $length; $i++)
	    {
	        $current = ord($value{$i});
	        if (($current == 0x9) ||
	            ($current == 0xA) ||
	            ($current == 0xD) ||
	            (($current >= 0x20) && ($current <= 0xD7FF)) ||
	            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
	            (($current >= 0x10000) && ($current <= 0x10FFFF)))
	        {
	            $ret .= chr($current);
	        }
	        else
	        {
	            $ret .= " ";
	        }
	    }
	    return $ret;
	}
	
	public function testRegex($url,$rgxPattern) { 
		$page = $this->scrapeUrl($url);
		preg_match($rgxPattern,$page,$matchResults);
		
		var_dump($matchResults);
	}
	
	
	public function getImageFromUrl($url,$saveto) {
		$ch = curl_init ($url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	    $raw=curl_exec($ch);
	    curl_close ($ch);
	    if(!is_dir($saveto) && file_exists($saveto)){
	        unlink($saveto);
	    }
	    $fp = fopen($saveto,'c');
	    fwrite($fp, $raw);
	    fclose($fp);
	}
	
	
	public function getCurrentURL() {
		$pageURL = 'http';
		
		if ($_SERVER["HTTPS"] == "on") { $pageURL .= "s"; }
		
		$pageURL .= "://";
		
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	
	public function createAlphanumericKey($info=null,$strLength=20) {
		$now = date("Y-m-d H:i:s");
		$ip = $_SERVER['REMOTE_ADDR'];
		$alphanumericKey = substr(strtolower(preg_replace('/[^2-9a-zA-Z]/',"",crypt($ip.$info.$now))),0,$strLength);
		return $alphanumericKey;
	}
	
	
	public function convertToShortState($state_name) {
		switch ($state_name) {
			case "Alabama":
				return "AL";
				break;
			case "Alaska":
				return "AK";
				break;
			case "Arizona":
				return "AZ";
				break;
			case "Arkansas":
				return "AR";
				break;
			case "California":
				return "CA";
				break;
			case "Colorado":
				return "CO";
				break;
			case "Connecticut":
				return "CT";
				break;
			case "Delaware":
				return "DE";
				break;
			case "Florida":
				return "FL";
				break;
			case "Georgia":
				return "GA";
				break;
			case "Hawaii":
				return "HI";
				break;
			case "Idaho":
				return "ID";
				break;
			case "Illinois":
				return "IL";
				break;
			case "Indiana":
				return "IN";
				break;
			case "Iowa":
				return "IA";
				break;
			case "Kansas":
				return "KS";
				break;
			case "Kentucky":
				return "KY";
				break;
			case "Louisana":
				return "LA";
				break;
			case "Maine":
				return "ME";
				break;
			case "Maryland":
				return "MD";
				break;
			case "Massachusetts":
				return "MA";
				break;
			case "Michigan":
				return "MI";
				break;
			case "Minnesota":
				return "MN";
				break;
			case "Mississippi":
				return "MS";
				break;
			case "Missouri":
				return "MO";
				break;
			case "Montana":
				return "MT";
				break;
			case "Nebraska":
				return "NE";
				break;
			case "Nevada":
				return "NV";
				break;
			case "New Hampshire":
				return "NH";
				break;
			case "New Jersey":
				return "NJ";
				break;
			case "New Mexico":
				return "NM";
				break;
			case "New York":
				return "NY";
				break;
			case "North Carolina":
				return "NC";
				break;
			case "North Dakota":
				return "ND";
				break;
			case "Ohio":
				return "OH";
				break;
			case "Oklahoma":
				return "OK";
				break;
			case "Oregon":
				return "OR";
				break;
			case "Pennsylvania":
				return "PA";
				break;
			case "Rhode Island":
				return "RI";
				break;
			case "South Carolina":
				return "SC";
				break;
			case "South Dakota":
				return "SD";
				break;
			case "Tennessee":
				return "TN";
				break;
			case "Texas":
				return "TX";
				break;
			case "Utah":
				return "UT";
				break;
			case "Vermont":
				return "VT";
				break;
			case "Virginia":
				return "VA";
				break;
			case "Washington":
				return "WA";
				break;
			case "Washington D.C.":
				return "DC";
				break;
			case "West Virginia":
				return "WV";
				break;
			case "Wisconsin":
				return "WI";
				break;
			case "Wyoming":
				return "WY";
				break;
			case "Alberta":
				return "AB";
				break;
			case "British Columbia":
				return "BC";
				break;
			case "Manitoba":
				return "MB";
				break;
			case "New Brunswick":
				return "NB";
				break;
			case "Newfoundland & Labrador":
				return "NL";
				break;
			case "Northwest Territories":
				return "NT";
				break;
			case "Nova Scotia":
				return "NS";
				break;
			case "Nunavut":
				return "NU";
				break;
			case "Ontario":
				return "ON";
				break;
			case "Prince Edward Island":
				return "PE";
				break;
			case "Quebec":
				return "QC";
				break;
			case "Saskatchewan":
				return "SK";
				break;
			case "Yukon Territory":
				return "YT";
				break;
			default:
				return $state_name;
		}
	}
	
	
	//work in progress
	function readJsonUrl($url, $arrFields) {
		//$address = urlencode($address);
	 
		//$url = 'http://where.yahooapis.com/geocode?location='.$address.'&flags=J&appid='.$appid;
		$data = file_get_contents($url);
		if ($data != '') {
			$data = json_decode($data);
			if ($data && $data->ResultSet && $data->ResultSet->Error == '0' && $data->ResultSet->Found) {
				return (object) array('lat'=>$data->ResultSet->Results[0]->latitude, 'lng'=>$data->ResultSet->Results[0]->longitude); 
			}
		}
		return false;
	}
	
}
?>