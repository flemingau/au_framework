<?php 

class Page {

	function __construct() {
		$this->Section = ((isset($_GET["p"]))? $_GET["p"] : null);
	}

	public function generateTemplate($section)
	{
		global $CFG;
		
		$login = new Login();
		$user = new User();

		$login->checkLoginRequirement($section);
		$user->checkProfileRequirement($section);
		// $this->getSectionCategory($section);
		$this->setMetaInformation($section);
		$this->getHeader();		
		$this->getBody($section);
		$this->getFooter();
	}
	

	/*
	 * Get header file
	 */
	protected function getHeader()
	{
		global $CFG;
		global $db;
		
		$sectionCategory = $this->getCategory();
		$sectionTitle = $this->getTitle();
		$sectionDescription = $this->getDescription();
		$sectionKeywords = $this->getKeywords();
		
		include($CFG->commondir . "header.php");
		
	}
	
	
	/*
	 * Get page to category file
	 */
	public function getSectionCategory($section)
	{
		global $CFG;
		
		include($CFG->datadir . "sectionCategory.php");
		
		$section = trim($section);
		
		if(isset($sectionCategory[$section])) {
			$this->Category = $sectionCategory[$section];
		} else {
			$this->Category = "";
		}
	}
	
	
	/*
	 * Set page information file
	 */
	public function setMetaInformation($section)
	{
		global $CFG;
		
		include($CFG->contentdir . "common/" . "meta.php");
		
		$this->Title = $sectionTitle;
		$this->Description = $sectionDescription;
		$this->Keywords = $sectionKeywords;
		
		if(trim($sectionTitle) != "") {
			$this->Title .= " ";
		}
	}
	
	
	/*
	 * Get page title
	 */
	public function getTitle()
	{
		return $this->Title;
	}
	
	
	/*
	 * Get page category
	 */
	public function getCategory()
	{
		return $this->Category;
	}
	
	
	/*
	 * Get page description
	 */
	public function getDescription()
	{
		return $this->Description;
	}
	
	
	/*
	 * Get page keywords
	 */
	public function getKeywords()
	{
		return $this->Keywords;
	}
	
	
	/*
	 * Get respective content
	 */
	protected function getBody($section)
	{
		global $CFG;
		global $db;
		global $apiKey;
		
		$fileContent = $CFG->bodydir . $section . ".body.php";
		$fileDefault = $CFG->bodydir . "_default.body.php";
		//$fileDefault = $CFG->dirroot . "default.php";
		//$fileNotFound = $CFG->bodydir . "404.php";
		$fileNotFound = $CFG->bodydir . "_default.body.php";
		
		$sectionCategory = $this->getCategory();
		
		if(file_exists($fileContent)) {
			include($fileContent);
		} else if($section == "") {
			include($fileDefault);
		} else {
			//include($fileDefault);
			include($fileNotFound);
		}
	}	
	
	
	/*
	 * Get footer file
	 */
	protected function getFooter()
	{
		global $CFG;
		include($CFG->commondir . "footer.php");
	}
	
	
	/*
	 * Get navigation file
	 */
	public function getNavigation()
	{
		global $CFG;
		include($CFG->commondir . "navigation.php");
	}
	
	
	
	/*
	 * Generate form elements
	 */
	public function generateFormElement($type, $name, $function, $section, $id=null, $value=null, $attributes=null, $required=null) {
		
		//$arrClass = array($section, $function, $name, $type);
		$arrClass = array($type, $function);
		$class = implode("-",$arrClass);
		$class = preg_replace('/[\-]+/',"-",$class);
		$class =  rtrim($class, ' -');
		$class =  ltrim($class, ' -');
		$required = ($required==true) ? " required" : null;
		$attributes = " $attributes";
		
		//if (substr($class,0,1) == '-') { $class = substr( $class, 1 ); }
		
		if($id == null) { $id = "$class-$name"; }
		
		switch($type) {
			case "label":				
				$input = "<label id=\"$id\" class=\"$type $class\"$attributes>$value</label>";
				break;
				
			case "textbox":
				$input = "<input type=\"text\" id=\"$id\" name=\"$id\" class=\"$type $class"."$required\" value=\"$value\"$attributes />";
				break;
				
			case "password":
				$input = "<input type=\"password\" id=\"$id\" name=\"$id\" class=\"$type $class"."$required\" value=\"$value\"$attributes />";
				break;

			case "hidden":
				$input = "<input type=\"hidden\" id=\"$id\" name=\"$id\" value=\"$value\">";
				break;
				
			case "button":
				$input = "<button type=\"submit\" name=\"$id\" id=\"$id\" class=\"$type $class\" value=\"$value\"$attributes>$value</button>";
				break;

			case "dropdown":
				$input = "<select id=\"$id\" name=\"$id\" class=\"$type $id"."$required\"$attributes>";
				if(is_array($value)) {
					for($i=0; $i < count($value);$i++) {
						$selected = (isset($value[$i][2]) && strtolower($value[$i][2]) == "selected") ? " selected=\"selected\"" : null; 
						$input .= "<option value=\"".$value[$i][0]."\" $selected>".$value[$i][1]."</option>";
					}
				} else {
					$input .= "<option value=\"$value\">$value</option>";
				}
				$input .= "</select>";
				break;
					
			case "textarea":
				$input = "<textarea id=\"$id\" class=\"$type $class"."$required\" name=\"$id\"$attributes>$value</textarea>";
				break;
				
			case "checkbox":
				$input = "<input type=\"checkbox\" id=\"$id\" name=\"$id\" class=\"$type $class"."$required\" value=\"$value\"$attributes />";
				break;
			
			case "radio":
				$input = "<input type=\"radio\" id=\"$id\" name=\"$id\" class=\"$type $class"."$required\" value=\"$value\"$attributes />";
				break;
				
			default:
				$input = null;
				break;
		}
		
		//$input = preg_replace('/\s+/'," ",$input);
		echo $input;
	}	

	public function retrieveTrueSection($string) {
		$value = explode("/",$string);
		return $value[0];
	}

}

?>