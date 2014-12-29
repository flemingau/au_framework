<?php

class Exclusive
{
	public function logKeyword() 
	{ 
		global $db;
		
		if(search_engine_query_string() != null) {
			$keyword = search_engine_query_string();
			$keyword = str_replace("-"," ",$keyword);
			$keyword = preg_replace('/[^a-zA-Z0-9 ]/', "", $keyword);
			$keyword = preg_replace('/\s+/', " ", $keyword);
			$datetime = date("Y-m-d H:i:s");
			
			$selKeywords = $db->query("SELECT Keyword FROM Keywords WHERE Keyword ='$keyword'");
			$findKeyword = $db->num_rows($selKeywords);
			
			if($findKeyword < 1) { 
				$insert = $db->query("INSERT INTO Keywords (Keyword, Updated, Count) 
					VALUES ('$keyword','$datetime','1')");
			} else {
				$update = $db->query("UPDATE Keywords SET Updated='$datetime', Count = Count+1 
					WHERE Keyword='$keyword'");
			}
			
			//echo mysql_error();
		}
	}
	
	
	public function showProfileCompletion() {
		global $db;
		global $CFG;
		
		$user = new User();
		
		$arrProfileCompletion = array();
		
		$userFbVerified = null;
		$userProfilePicExists = null;
		$userShortBio = null;
		$userFirstName = null;
		$userLastName = null;
		$userHomeCity = null;
		$userHomeState = null;
		$userBirthday = null;
		$userSearchCity = null;
		$userSearchState = null;
		$userMoveStartDate = null;
		$userSearchBudgetMin = null;
		$userSearchBudgetMax = null;
		$userQuickAnswered = null;
		
		$score = 0;
		
		$numQuestionsAnswered = $db->num_rows($db->query("SELECT UA.ID
			FROM UserAnswers UA
			WHERE UA.UserID='$user->ID' AND UA.Skipped='0000-00-00 00:00:00'"));
		
		$selUser = $db->query("SELECT U.FacebookVerified, U.About, U.FirstName, U.LastName, 
				U.HomeCity, U.HomeState, U.BirthDate, U.SearchCity, U.SearchState, 
				U.MoveStartDate, U.SearchBudgetMin, U.SearchBudgetMax
			FROM Users U
			WHERE U.ID='$user->ID' AND U.Active='Y'
			LIMIT 1");
		
		if($u = $db->fetch_object($selUser)) {
			$userFbVerified = $u->FacebookVerified;
			$userShortBio = trim($u->About);
			
			$userFirstName = $u->FirstName;
			$userLastName = $u->LastName;
			$userHomeCity = $u->HomeCity;
			$userHomeState = $u->HomeState;
			$userBirthday = $u->BirthDate;
			$userSearchCity = $u->SearchCity;
			$userSearchState = $u->SearchState;
			$userMoveStartDate = $u->MoveStartDate;
			$userSearchBudgetMin = $u->SearchBudgetMin;
			$userSearchBudgetMax = $u->SearchBudgetMax;
			
		}
		
		$selUserProfilePhoto = $db->query("SELECT UP.File
			FROM UserPhotos UP
			WHERE UP.UserID='$user->ID' AND UP.Main='Y' AND  UP.Deleted='0000-00-00 00:00:00'
			ORDER BY Uploaded DESC
			LIMIT 1");
		
		if($pic = $db->fetch_object($selUserProfilePhoto)) {
			$userProfilePicExists = $pic->File;
		}
		
		$selAnsweredQuickQuestions = $db->query("SELECT KA.ID 
			FROM QuickAnswers KA, QuickQuestions KQ
			WHERE KA.UserID='$user->ID' AND TRIM(KA.Answer)!='' AND KA.Skipped='0000-00-00 00:00:00' AND KA.QuestionID=KQ.ID");
		
		$numAnsweredQuickQuestions = $db->num_rows($selAnsweredQuickQuestions);
		
		$selQuickQuestions = $db->query("SELECT KQ.ID 
			FROM QuickQuestions KQ
			WHERE KQ.Active='Y'");
		
		$numQuickQuestions = $db->num_rows($selQuickQuestions);
		
		
		$selRoomalooFriends = $db->query("SELECT U.ID
			FROM Users U, UserFriends UF
			WHERE UF.UserID='$user->ID' AND UF.FriendID=U.oauthUserID");

		$numRoomalooFriends = $db->num_rows($selRoomalooFriends);
		
		
		$score += $questionsAnsweredDone = ($numQuestionsAnswered >= 10) ? 1 : 0;
		$score += $facebookVerifiedDone = ($userFbVerified == "Y") ? 1 : 0;
		$score += $profilePictureDone = (!isNull($userProfilePicExists)) ? 1 : 0;
		$score += $shortBioDone = (!isNull($userShortBio)) ? 1 : 0;
		
		$score += $firstNameEntered = (!isNull($userFirstName)) ? 1 : 0;
		$score += $lastNameEntered = (!isNull($userLastName)) ? 1 : 0;
		$score += $homeCityEntered = (!isNull($userHomeCity)) ? 1 : 0;
		$score += $homeStateEntered = (!isNull($userHomeState)) ? 1 : 0;
		$score += $birthdayEntered = (!isNull($userBirthday)) ? 1 : 0;
		$score += $searchCityEntered = (!isNull($userSearchCity)) ? 1 : 0;
		$score += $searchStateEntered = (!isNull($userSearchState)) ? 1 : 0;
		$score += $moveStartDateEntered = (!isNull($userMoveStartDate)) ? 1 : 0;
		$score += $searchBudgetMinEntered = (!isNull($userSearchBudgetMin)) ? 1 : 0;
		$score += $searchBudgetMaxEntered = (!isNull($userSearchBudgetMax)) ? 1 : 0;
		
		$score += $quickQuestionsDone = (($numAnsweredQuickQuestions / $numQuickQuestions) == 1) ? 1 : 0;
		$score += $roomalooFriendsDone = ($numRoomalooFriends >= 3) ? 1 : 0;
		
		$percentComplete = floor(($score / 16) * 100);
			
		$arrCompletionDetails = array(
			"Answer 10 roommate match questions" => "$questionsAnsweredDone",
			"Get Facebook Verified" => "$facebookVerifiedDone",
			"Add a profile picture" => "$profilePictureDone",
			"Enter your vital stats" => "$firstNameEntered;$lastNameEntered;$homeCityEntered;$homeStateEntered;$birthdayEntered;$searchCityEntered;$searchStateEntered;$moveStartDateEntered;$searchBudgetMinEntered;$searchBudgetMaxEntered;",
			"Fill in your short bio" => "$shortBioDone",
			"Complete Q&A section" => "$quickQuestionsDone",
			"Add 3 Friends" => "$roomalooFriendsDone"
		);
		
		array_push($arrProfileCompletion,$percentComplete);
		array_push($arrProfileCompletion,$arrCompletionDetails);
		
		//return $percentComplete;
		return $arrProfileCompletion;
	}
	
	
	public function getNeighborhoodName($neighborhoodID) {
		global $db;
		global $CFG;
		
		$selNeighborhood = $db->query("SELECT N.* FROM Neighborhoods N WHERE N.ID='$neighborhoodID' LIMIT 1");
		
		if($n = $db->fetch_object($selNeighborhood)) {
			return $n->Neighborhood;
		}
	}
	
	
	protected function getBooleanString($curFilterType, $lastFilterType, $lastBool) {
		if($curFilterType != $lastFilterType) {
			$bool = (($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND " : " AND ";
		} else {
			//$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
			//$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (")) ? ") AND (" : $bool;
		}
		
		return $bool;
	}
	
	
	public function buildFilterString($filterBy) {
		if(is_array($filterBy)) {
				$filteryBy = array_filter($filterBy);
			asort($filterBy);
			
			$lastFilterType = null;
			$lastBool = null;
			$filterResult = null;
			$arrFilterResults = array();
			$filterTable = null;
			$filterTables = null;
			
			foreach($filterBy as $filter) {
				$bool = null;
				
				$filter = str_replace("filter","",$filter);
				if(isset($filter[0])) {
					$filter[0] = strtolower($filter[0]);
				}
				$curFilterType = explode("-",$filter);
				$curFilterType = (is_array($curFilterType)) ? $curFilterType[0] : $curFilterType;
				
				switch($filter) {
					case null:
						$lastBool = "";
						break;
						
					/* Listing related filters */
					case "rentalType-entireUnit":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."L.ListingType='Entire Unit'";
						break;
						
					case "rentalType-bedroomAvailable":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."L.ListingType='Bedroom Available'";
						break;
						
					case "propertyType-Apartment":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."L.PropertyType='Apartment'";
						break;
					
					case "propertyType-House":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."L.PropertyType='House'";
						break;
					
					case "propertyType-Highrise":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."L.PropertyType='Highrise'";
						break;
					
					case "propertyType-Loft":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."L.PropertyType='Loft'";
						break;
					
					case "propertyType-Townhouse":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."L.PropertyType='Townhouse'";
						break;
						
					case "propertyType-WalkUpApartment":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."L.PropertyType='Townhouse'";
						break;	
						

					case stristr($filter,"neighborhood-") !== FALSE:
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$hashPos = stripos($filter,"-") + 1;
						$filterValue = substr($filter,$hashPos);
						$filterResult .= $bool."(L.Neighborhood1='$filterValue' OR L.Neighborhood2='$filterValue' OR L.Neighborhood3='$filterValue')";
						//$filterTable .= ", UserNeighborhoods UN";
						break;
						
					case stristr($filter,"spaceAmenities-") !== FALSE:

						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$hashPos = stripos($filter,"-") + 1;
						$filterValue = substr($filter,$hashPos);
						if($filterValue!="FurnishedBedroom" || $filterValue!="PrivateBathroom") {
							$filterResult .= $bool."LAM.$filterValue='true'";
						} else {
							$filterResult .= $bool."R.$filterValue='true'";
						}
						//$filterTable .= ", UserNeighborhoods UN";
						break;
					
					case stristr($filter,"propertyAmenities-") !== FALSE:
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$hashPos = stripos($filter,"-") + 1;
						$filterValue = substr($filter,$hashPos);
						if($filterValue!="FurnishedBedroom" || $filterValue!="PrivateBathroom") {
							$filterResult .= $bool."LAM.$filterValue='true'";
						} else {
							$filterResult .= $bool."R.$filterValue='true'";
						}
						//$filterTable .= ", UserNeighborhoods UN";
						break;
						
						
					case stristr($filter,"leaseLength-") !== FALSE:
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$hashPos = stripos($filter,"-") + 1;
						$filterValue = substr($filter,$hashPos);
						$filterResult .= $bool."R.LeaseTerm='$filterValue'";
						break;

					/*
					case stristr($filter,"bed#") !== FALSE:
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$hashPos = stripos($filter,"-") + 1;
						$filterValue = substr($filter,$hashPos);
						$filterValue = explode("-",$filterValue);
						$filterMin = $filterValue[0];
						$filterMax = $filterValue[1]; 
						$filterResult .= $bool."(AND L.Bedrooms >= $filterMin AND L.Bedrooms <= $filterMax)";
						//$filterTable .= ", UserNeighborhoods UN";
						break;
					
					case stristr($filter,"bath#") !== FALSE:
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$hashPos = stripos($filter,"-") + 1;
						$filterValue = substr($filter,$hashPos);
						$filterValue = explode("-",$filterValue);
						$filterMin = $filterValue[0];
						$filterMax = $filterValue[1]; 
						$filterResult .= $bool."(AND L.Bathrooms >= $filterMin AND L.Bathrooms <= $filterMax)";
						//$filterTable .= ", UserNeighborhoods UN";
						break;
					*/
							
					default:
						break;
				}
				
				$lastFilterType = $curFilterType;
				$lastBool = $bool;
			}
			
			$filterResult .= ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND ("  || $lastBool == null) ? ")" : null;
			
			if($filterResult == ")") {
				$filterResult = null;
			}
			
			$arrFilterTable = array_unique(explode(",",$filterTable));
			$filterTables = (is_array($arrFilterTable)) ? implode(",",$arrFilterTable) : $arrFilterTable;
			
			$arrFilterResults["tables"] = $filterTables;
			$arrFilterResults["filters"] = $filterResult;
			
			return $arrFilterResults;
		}
	}
	
	
	public function buildOrderByString($sortBy) {
		switch($sortBy) {
			case "available-sooner":
				$orderBy = "ORDER BY R.StartDate ASC, R.Updated DESC";
				break;	
			case "available-later":
				$orderBy = "ORDER BY R.StartDate DESC, R.Updated DESC";
				break;
			case "newest":
				$orderBy = "ORDER BY R.Inserted DESC, R.Updated DESC";
				break;
			case "rent-low":
				$orderBy = "ORDER BY R.Rent ASC, R.Updated DESC";
				break;
			case "rent-high":
				$orderBy = "ORDER BY R.Rent DESC, R.Updated DESC";
				break;
			default:
				$orderBy = "ORDER BY R.Inserted DESC, R.Updated DESC";
				break;
		}
		
		return $orderBy;
	}
	
	
	
	
	
	
	
	
	
	
	/*** ROOMMATES FILTERS ***/
	public function buildFilterStringRoommates($filterBy) {
		if(is_array($filterBy)) {
				$filteryBy = array_filter($filterBy);
			asort($filterBy);
			
			$lastFilterType = null;
			$lastBool = null;
			$filterResult = null;
			$arrFilterResults = array();
			$filterTable = null;
			$filterTables = null;
			
			foreach($filterBy as $filter) {
				$bool = null;
				
				$filter = str_replace("filter","",$filter);
				if(isset($filter[0])) {
					$filter[0] = strtolower($filter[0]);
				}
				$curFilterType = explode("-",$filter);
				$curFilterType = (is_array($curFilterType)) ? $curFilterType[0] : $curFilterType;
				
				switch($filter) {
					case null:
						$lastBool = "";
						break;
						
					/* User related filters */
					case "maritalStatus-Married":
						//$bool = $this->getBooleanString($curFilterType, $lastFilterType, $lastBool);
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool;
						$filterResult .= $bool."U.MaritalStatus='Married'";
						break;
					case "maritalStatus-Single":
						//$bool = $this->getBooleanString($curFilterType, $lastFilterType, $lastBool);
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool;
						$filterResult .= $bool."U.MaritalStatus='Single'";
						break;
					case "maritalStatus-NoAnswer":
						//$bool = $this->getBooleanString($curFilterType, $lastFilterType, $lastBool);
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool;
						$filterResult .= $bool."U.MaritalStatus='No Answer'";
						break;

						
						
					case "ethnicity-asian":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Ethnicity LIKE '%asian%'";
						break;
						
					case "ethnicity-black":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Ethnicity LIKE '%black%'";
						break;
						
					case "ethnicity-indian":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Ethnicity LIKE '%indian%'";
						break;
						
					case "ethnicity-hispanicLatin":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Ethnicity LIKE '%hispanic/latin%'";
						break;
						
					case "ethnicity-middleEastern":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Ethnicity LIKE '%middle eastern%'";
						break;
						
					case "ethnicity-nativeAmerican":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Ethnicity LIKE '%native american%'";
						break;
						
					case "ethnicity-pacificIslander":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Ethnicity LIKE '%pacific islander%'";
						break;
						
					case "ethnicity-white":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Ethnicity LIKE '%white%'";
						break;
						
					case "ethnicity-other":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Ethnicity LIKE '%other%'";
						break;
					
					case "degree-ged":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Education='GED'";
						break;
						
					case "degree-graduate":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Education='Graduate'";
						break;
						
					case "degree-highSchool":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Education='High School'";
						break;
						
					case "degree-phd":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Education='PhD'";
						break;
					
					case "degree-undergrad":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Education='Undergrad'";
						break;
					
					
					case "drinks-never":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Drinker='Never'";
						break;
					
					case "drinks-often":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Drinker='Often'";
						break;
						
					case "drinks-rarely":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Drinker='Rarely'";
						break;
						
					case "drinks-socially":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Drinker='Socially'";
						break;
						
					case "drinks-veryOften":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Drinker='Very Often'";
						break;
						
					case "gender-female":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Gender='female'";
						break;
						
					case "gender-male":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Gender='male'";
						break;

					
					case "goal-justRoommate":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."(UG.FindRoommate='1' AND UG.FindSharedSpace='0' AND UG.FindEntireSpace='0' AND UG.ListSpace='0')";
						$filterTable .= ", UserGoals UG";
						break;
					case "goal-ownSpace":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."(UG.FindRoommate='0' AND UG.FindSharedSpace='0' AND UG.FindEntireSpace='1' AND UG.ListSpace='0')";
						$filterTable .= ", UserGoals UG";
						break;
					
					case "goal-roommateRoom":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."(UG.FindRoommate='1' AND UG.FindSharedSpace='1' AND UG.FindEntireSpace='0' AND UG.ListSpace='0')";
						$filterTable .= ", UserGoals UG";
						break;
					
					
					case "interested-men":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."(U.InterestedIn='male' OR U.InterestedIn='female,male' OR U.InterestedIn='' OR U.InterestedIn IS NULL)";
						break;
						
					case "interested-women":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."(U.InterestedIn='female' OR U.InterestedIn='female,male' OR U.InterestedIn='' OR U.InterestedIn IS NULL)";
						break;

					case "pets-cat":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."UPT.Pet='cat'";
						$filterTable .= ", UserPets AS UPT";
						break;
						
					case "pets-dog":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."UPT.Pet='dog'";
						$filterTable .= ", UserPets AS UPT";
						break;
						
					case "pets-none":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."UPT.Pet='none'";
						$filterTable .= ", UserPets AS UPT";
						break;
						
					case "pets-other":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."UPT.Pet='other'";
						$filterTable .= ", UserPets AS UPT";
						break;
				
						
					case "smokes-no":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Smoker='No'";
						break;
						
					case "smokes-occassionally":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Smoker='Occassionally'";
						break;
						
					case "smokes-yes":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.Smoker='Yes'";
						break;
						
					case "work-artistic":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Artistic / Musical / Writer'";
						break;
						
					case "work-banking":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Banking / Financial / Real Estate'";
						break;
						
					case "work-clerical":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Clerical / Administrative'";
						break;
						
					case "work-computer":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Computer / Hardware / Software'";
						break;
						
					case "work-construction":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Construction / Craftsmanship'";
						break;
						
					case "work-education":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Education / Academia'";
						break;
						
					case "work-entertainment":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Entertainment / Media'";
						break;
						
					case "work-executive":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Executive / Management'";
						break;
						
					case "work-hospitality":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Hospitality / Travel'";
						break;
						
					case "work-law":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Law / Legal Services'";
						break;
						
					case "work-medicine":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Medicine / Health'";
						break;
						
					case "work-military":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Military'";
						break;
						
					case "work-other":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Other'";
						break;
						
					case "work-political":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Political / Government'";
						break;
						
					case "work-ratherNotSay":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Rather not say'";
						break;
						
					case "work-retired":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Retired'";
						break;
						
					case "work-sales":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Sales / Marketing / Biz Dev'";
						break;
						
					case "work-science":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Science / Tech / Engineering'";
						break;
						
					case "work-transportation":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Transportation'";
						break;
						
					case "work-unemployed":
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$filterResult .= $bool."U.WorkIndustry='Unemployed'";
						break;
						
						
						
					case stristr($filter,"neighborhood-") !== FALSE:
						$bool = ($curFilterType == $lastFilterType) ? " OR " : " AND (";
						$bool = ($bool == " AND (" && ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND (")) ? ") AND (" : $bool ;
						$hashPos = stripos($filter,"-") + 1;
						$filterValue = substr($filter,$hashPos);
						$filterResult .= $bool."UN.NeighborhoodID='$filterValue' AND UN.UserID=U.ID";
						$filterTable .= ", UserNeighborhoods UN";
						break;
						
						
					
						
					default:
						break;
				}
				
				$lastFilterType = $curFilterType;
				$lastBool = $bool;
			}
			
			$filterResult .= ($lastBool == " OR " || $lastBool == " AND (" || $lastBool == ") AND ("  || $lastBool == null) ? ")" : null;
			
			if($filterResult == ")") {
				$filterResult = null;
			}
			
			$arrFilterTable = array_unique(explode(",",$filterTable));
			$filterTables = (is_array($arrFilterTable)) ? implode(",",$arrFilterTable) : $arrFilterTable;
			
			$arrFilterResults["tables"] = $filterTables;
			$arrFilterResults["filters"] = $filterResult;
			
			return $arrFilterResults;
		}
	}
	
	
	public function buildOrderByStringRoommates($sortBy) {
		switch($sortBy) {
			case "age-younger":
				$orderBy = "ORDER BY Age ASC, U.BirthDate ASC, U.LastUpdated DESC";
				break;
			case "age-older":
				$orderBy = "ORDER BY Age DESC, U.BirthDate DESC, U.LastUpdated DESC";
				break;
			case "budget-low":
				$orderBy = "ORDER BY U.SearchBudgetMax ASC, U.SearchBudgetMin ASC, U.LastUpdated DESC";
				break;
			case "budget-high":
				$orderBy = "ORDER BY U.SearchBudgetMax DESC, U.SearchBudgetMin DESC, U.LastUpdated DESC";
				break;
			case "compatibility":
				$orderBy = "ORDER BY U.AcceptTerms DESC, U.LastUpdated DESC";
				break;
			case "move-sooner":
				$orderBy = "ORDER BY U.MoveStartDate ASC, U.LastUpdated DESC";
				break;	
			case "move-later":
				$orderBy = "ORDER BY U.MoveStartDate DESC, U.LastUpdated DESC";
				break;
			case "newest":
				$orderBy = "ORDER BY U.AcceptTerms DESC, U.LastUpdated DESC";
				break;
			default:
				$orderBy = "ORDER BY U.AcceptTerms DESC, U.LastUpdated DESC";
				break;
		}
		
		return $orderBy;
	}
	
	
	
	public function buildFilterQueryAJAX($arrFilter, $filterResults, $filterValue, $action=null, $filterKey=null) {
		//$filterResults = "";
		$filterAnd = "";
		$filterOr = "";
		//$filterRange = "";
		//$filterString = "";
		//$orExists = "";
		
		$filterId = ucfirst($arrFilter[$filterKey][0]);
		$filterDescription = $arrFilter[$filterKey][1];
		$filterTable = $arrFilter[$filterKey][3];
		$filterCondition = $arrFilter[$filterKey][4];
		
		if($filterValue != null) {
			switch($filterCondition) {
				case "AND":
					$filterAnd .=  " AND $filterTable='$filterValue'";
					break;
					
				case "OR":
					$rgxFilter = '/AND [\(](.*?)[\)](.*)/is';
					preg_match($rgxFilter,$filterResults,$matchFilter);
					//preg_match_all($rgxFilter,$filterResults,$matchFilter);
					$orExists = isset($matchFilter[1]);
					//$orExists = isset($matchFilter[0][0]);	
					
					if($action=="add") {
						$tableMatch = strpos($filterResults,$filterTable);
					
						if($tableMatch !== false) {
							$patternReplace = str_replace(".","\.",$filterTable);
							$rgxPattern = "/".$patternReplace."=\'(.*?)\'/"; 
							preg_match_all($rgxPattern,$filterResults,$matchOr,PREG_SET_ORDER);
							$oldPattern = "";
							$countMatch = count($matchOr);
							
							for($c=0; $c < $countMatch; $c++) {
								$oldPattern .= $matchOr[$c][0];
								if($c < $countMatch-1) {
									$oldPattern .= " OR ";
								}
							}
							
							$newPattern = "$oldPattern OR $filterTable='$filterValue'";
							
							$filterResults = str_replace($oldPattern,$newPattern,$filterResults);
		
						} else { //if this table wasn't found
							$filterAnd .= " AND ($filterTable='$filterValue')";
						}
					} 
					break;
					
				case "LESS THAN":
					$filterAnd .= ($filterAnd == "") ? "$filterTable <= '$filterValue'" : " AND $filterTable <= '$filterValue'";
					break;
					
				case "GREATER THAN":
					$filterAnd .= ($filterAnd == "") ? "$filterTable >= '$filterValue'" : " AND $filterTable >= '$filterValue'";
					break;
					
				default:
					break;
			}
		}
	
			
		switch($action) {
			case "add":
				$filterResults .= ($filterOr) ? "$filterAnd AND ($filterOr)" : $filterAnd;					
				break;
				
			case "remove":		
				if($filterCondition == "AND" || $filterCondition == "LESS THAN" || $filterCondition == "GREATER THAN") {
					$filterResults = str_replace($filterAnd,"",$filterResults);
				} else {
					$filterRemove = "$filterTable='$filterValue'";
					//echo "<br />(remove)$filterRemove(from)$filterResults<br />";
					
					$filterResults = str_replace($filterRemove,"",$filterResults);
					$filterResults = preg_replace('/\s*OR\s*OR\s*/'," OR ",$filterResults);
					$filterResults = preg_replace('/\(\s*OR/',"(",$filterResults);
					$filterResults = preg_replace('/OR\s*\)/',")",$filterResults);
					$filterResults = preg_replace('/AND\s*\(\s*\)/',"",$filterResults);
					$filterResilts = preg_replace('/\s{2}/'," ", $filterResults);	
				}
				break;
				
			default:
				break;
		}
		
		return $filterResults;
	}
	
	
	public function buildRoomMapXML($query) {
	
		$arrListings = array();
	
		$dbQuery = $db->query($query);
		
		while($q = $db->fetch_object($dbQuery)) {
			$arrInfo["ListingID"] = $q->LID;
			$arrInfo["UserID"] = $q->UserID;
			$arrInfo["Address1"] = $q->Address1;
			$arrInfo["Address2"] = $q->Address2;
			$arrInfo["City"] = $q->City;
			$arrInfo["State"] = $q->State;
			$arrInfo["ZipCode"] = $q->ZipCode;
			$arrInfo["Country"] = $q->Country;
			$arrInfo["Latitude"] = $q->Latitude;
			$arrInfo["Longitude"] = $q->Longitude;
			$arrInfo["Type"] = $q->Type;
			$arrInfo["Bedrooms"] = $q->Bedrooms;
			$arrInfo["Title"] = $q->Title;
			$arrInfo["Description"] = $q->Description;
			$arrInfo["Price"] = $q->Price;
			$arrInfo["LastUpdated"] = $q->LastUpdated;
			$arrInfo["Active"] = $q->Active;
			$arrInfo["Distance"] = $q->distance;
			
			array_push($arrListings, $arrInfo);
		}
		
		//Start XML file, create parent node
		$dom = new DOMDocument("1.0");
		$node = $dom->createElement("markers");
		$parnode = $dom->appendChild($node); 
		
		header("Content-type: text/xml"); 
		
		// Iterate through the rows, adding XML nodes for each
		foreach($arrListings as $row) { //start at the second record instead
		  // ADD TO XML DOCUMENT NODE  
		  $node = $dom->createElement("marker");  
		  $newnode = $parnode->appendChild($node);   
		  $newnode->setAttribute("listingId", $row['ListingID']);
		  $newnode->setAttribute("lister", $row['UserID']);
		  $newnode->setAttribute("address1", $row['Address1']);
		  $newnode->setAttribute("address2", $row['Address2']);
		  $newnode->setAttribute("city", $row['City']);
		  $newnode->setAttribute("state", $row['State']);
		  $newnode->setAttribute("zipCode", $row['ZipCode']);
		  $newnode->setAttribute("country", $row['Country']);
		  $newnode->setAttribute("latitude", $row['Latitude']);
		  $newnode->setAttribute("longitude", $row['Longitude']);
		  $newnode->setAttribute("type", $row['Type']);
		  $newnode->setAttribute("bedrooms", $row['Bedrooms']);
		  $newnode->setAttribute("title", $row['Title']);
		  $newnode->setAttribute("description", $row['Description']);
		  $newnode->setAttribute("price", $row['Price']);
		  $newnode->setAttribute("lastUpdated", $row['LastUpdated']);
		  $newnode->setAttribute("active", $row['Active']);
		  $newnode->setAttribute("distance", $row['Distance']);
		  
		}
		//DISPLAY XML DATA
		echo $dom->saveXML();
	}
	
	
	public function buildFileCraigslistXML($searchId) {
	
		global $CFG;
		global $db; 
		
		//$this->justTesting();
		ignore_user_abort(true);
		
		//$searchId = (isset($_GET["sid"])) ? $_GET["sid"] : null;
		$common = new Common();
		$gmap = new GoogleMaps();
		$gmap->setDeveloperKey("ABQIAAAAClHoNqepNQQ1UvQeBHuC5xQLfRswG7le8poDJksQuZeisyscyxSMx0XT5lyHIIM8udwo4iSXjznl5Q");
		
		$spAddress1 = "";
		$spAddress2 = "";
		$spAddressName1 = "";
		$spAddressName2 = "";
		$bndPoint1 = "";
		$bndPoint2 = "";
		
		$url = "";
		$bounded = false;
		$arrListings = array();
		$arrNewListings = array();
		$strDirPoint1 = "";
		$strDirPoint2 = "";
		$today = date("Y-m-d");
		
		$dbSearchQuery = $db->query("SELECT * FROM Searches WHERE SearchID='$searchId' LIMIT 1") or die(mysql_error());  
		
		
		while($s = $db->fetch_object($dbSearchQuery)) {
			$clCity = (!isNull($s->City)) ? $s->City : null;
			$clQuery = (!isNull($s->Query)) ? $s->Query : null;
			$clMinAsk = (!isNull($s->MinRent)) ? $s->MinRent : null;
			$clMaxAsk = (!isNull($s->MaxRent)) ? $s->MaxRent : null;
			$clBedrooms = (!isNull($s->Bedrooms)) ? $s->Bedrooms : null;
			$clHasPic = ($s->HasPic == "Y") ? "1" : null;
			$clAddTwo = ($s->AllowCats == "Y") ? "purrr" : null;
			$clAddThree = ($s->AllowDogs == "Y") ? "wooof" : null;
			
			$arrSearchCity = array("lasvegas","losangeles","newyork","orangecounty","sandiego","sfbay","washingtondc");
			$arrReplaceCity = array("Las Vegas, NV","Los Angeles, CA","New York City, NY","Orange county, CA","San Diego, CA","San Francisco, CA","Washington DC");	
			$city = ucwords(str_replace($arrSearchCity, $arrReplaceCity, $s->City));
			
			$spAddress1 = (!isNull($s->POI1)) ? $s->POI1 : null;
			$spAddress2 = (!isNull($s->POI2)) ? $s->POI2 : null;
		
			$spAddressName1 = (!isNull($s->POIName1)) ? $s->POIName1 : null;
			$spAddressName2 = (!isNull($s->POIName2)) ? $s->POIName2 : null;
			
			$bndPoint1 = (!isNull($s->Boundary1)) ? $s->Boundary1 . " " . $city . " USA" : null;
			$bndPoint2 = (!isNull($s->Boundary2)) ? $s->Boundary2 . " " . $city . " USA" : null;
			
			$url = $this->getCraigslistUrl($clCity, $clQuery, $clMinAsk, $clMaxAsk, $clBedrooms, $clHasPic, $clAddTwo, $clAddThree);
		}
		
		
		//DETERMINE THE RELATIVE POSITION OF THE BOUNDING POINTS
		if($bndPoint1 != null && $bndPoint2 != null) {
			$arrBound1 = $gmap->getGeoCode($bndPoint1);
			$arrBound2 = $gmap->getGeoCode($bndPoint2);
			
			$boundLat1 = (isset($arrBound1["lat"])) ? $arrBound1["lat"] : null;
			$boundLng1 = (isset($arrBound1["lng"])) ? $arrBound1["lng"] : null;
			$boundLat2 = (isset($arrBound2["lat"])) ? $arrBound2["lat"] : null;
			$boundLng2 = (isset($arrBound2["lng"])) ? $arrBound2["lng"] : null;
			
			if(!isNull($boundLat1) && !isNull($boundLng1) && !isNull($boundLat2) && !isNull($boundLng2)) {
				if($boundLat1 > $boundLat2) {
					$strDirPoint1 = "north";
					$strDirPoint2 = "south";
				} else {
					$strDirPoint1 = "south";
					$strDirPoint2 = "north";
				}
				
				if($boundLng1 > $boundLng2) {
					$strDirPoint1 .= "east";
					$strDirPoint2 .= "west";
				} else {
					$strDirPoint1 .= "west";
					$strDirPoint2 .= "east";
				}
				
				$bounded = true;
			}
		}
		
		//START INITIAL SCRAPE TO ONLY GET TOTAL RESULTS
		$startingList = $common->scrapeUrl($url);
		$rgxListTotal = '/<b>Found: ([0-9]+) Displaying:/is';
		preg_match($rgxListTotal,$startingList,$matchListTotal);
		
		if(isset($matchListTotal[1])) {
			$totalPages = ceil($matchListTotal[1] / 100);
		} else {
			$totalPages = 0;
			
			$arrCoordinates = $gmap->getGeoCode("$city USA");
			
			$arrInfo["Address"] = $clCity;
			$arrInfo["Price"] = $clQuery;
			$arrInfo["Type"] = "no-results";
			$arrInfo["PostID"] = "No Listings Found";
			$arrInfo["Lat"] = $arrCoordinates["lat"];
			$arrInfo["Lng"] = $arrCoordinates["lng"];
			$arrInfo["PostDate"] = $today;
			$arrInfo["Link"] = "http://mapmasher.com/";
												
			array_push($arrListings, $arrInfo);
		}
		
		//if($totalPages > 2) { $totalPages = 3; }
		if($totalPages > 1) { $totalPages = 2; }
		//echo $totalPages .  " XXX<br />\r\n";
		
		$intPage = 0;
		$resetUrl = $url;
		
		for($count=0; $count < $totalPages; $count++) {
		
			$listings = $common->scrapeUrl($url);
		
			$rgxListRef = '/([a-zA-Z]{3}) \s?([0-9]{1,2}) - <a href="(.+?)">\$([0-9]{1,4})/is';
			preg_match_all($rgxListRef,$listings,$matchListRef);
			//preg_match_all($rgxListRef,$listings,$matchListRef,PREG_SET_ORDER)
		
			for($x=0; $x < count($matchListRef[3]); $x++) {
				$rgxPid = '/(.+?)\/([0-9]+)\.html/';
				preg_match($rgxPid,$matchListRef[3][$x],$matchPid);
				$pid = $matchPid[2];
				$source = "craigslist";
				
				$dbQuery = $db->query("SELECT * FROM Listings WHERE SourceID='$pid' AND Source='$source' LIMIT 1");
				$numRecords = $db->num_rows($dbQuery);
				
				if($numRecords > 0) {
					while($q = $db->fetch_object($dbQuery)) {
						//$arrInfo["Address"] = $q->Address . " USA";
						$arrInfo["Address"] = $q->Address;
						$arrInfo["Price"] = $q->Price;
						$arrInfo["Type"] = ($q->Bedrooms > 0) ? $q->Bedrooms." Bedroom" : "Studio";
						$arrInfo["PostID"] = $q->SourceID;
						$arrInfo["Lat"] = $q->Latitude;
						$arrInfo["Lng"] = $q->Longitude;
						$arrInfo["PostDate"] = $q->PostDate;
						$arrInfo["Link"] = $q->Link;
							
						if(!$bounded) { 						
							array_push($arrListings, $arrInfo);
							
						} else {
						
							switch($strDirPoint1) {
								case "northwest":
									if ($arrInfo["Lat"] >= $boundLat2 && $arrInfo["Lat"] <= $boundLat1 && $arrInfo["Lng"] >= $boundLng1 && $arrInfo["Lng"] <= $boundLng2) {
										array_push($arrListings, $arrInfo);
									}
									break;
								case "northeast":
									if ($arrInfo["Lat"] >= $boundLat2 && $arrInfo["Lat"] <= $boundLat1 && $arrInfo["Lng"] <= $boundLng1 && $arrInfo["Lng"] >= $boundLng2) {
										array_push($arrListings, $arrInfo);
									}
									break;
								case "southwest":
									if ($arrInfo["Lat"] <= $boundLat2 && $arrInfo["Lat"] >= $boundLat1 && $arrInfo["Lng"] >= $boundLng1 && $arrInfo["Lng"] <= $boundLng2) {
										array_push($arrListings, $arrInfo);
									}
									break;
								default: //southeast
									if ($arrInfo["Lat"] <= $boundLat2 && $arrInfo["Lat"] >= $boundLat1 && $arrInfo["Lng"] <= $boundLng1 && $arrInfo["Lng"] >= $boundLng2) {
										array_push($arrListings, $arrInfo);
									}
									break;
							}
						}
						
						
					}
				} else {
					array_push($arrNewListings,$matchListRef[3][$x]);
				}
			}
		
		
			//BEGIN SCRAPING NEW LISTING PAGES
			foreach($arrNewListings as $link) {
			
				$listing = $common->scrapeUrl($link);			
				
				//<!-- CLTAG GeographicArea=737 S. Kingsley Dr. LA, CA 90005 -->
				$rgxAddress = '/(<a target="_blank" href="http:\/\/maps\.google\.com\/\?q=loc%3A\+(.*?)"(.*?)>google map<\/a>)/is';
				preg_match($rgxAddress,$listing,$matchAddress);
				
				//<h2>$1250 NYC STYLISH Jr.Brm in 20's bldg. Hardwood/Brick/Stainless  (Miracle Mile) (map)</h2>
				//<h2>$1250 / 1br - 900ft&sup2; - Contemporary Bright New 1 Bed 1 Bath w/ balcony (Hancock Park Adj./Koretown) (map)</h2>
				$rgxPrice = '/<h2>\$([0-9]+)(.*)<\/h2>/is';
				preg_match($rgxPrice,$listing,$matchPrice);
				
				$rgxRooms = '/([0-9]+)(br|bedroom)/';
				preg_match($rgxRooms,$listing,$matchRooms);
				
				//PostingID: 2672241861<br>
				$rgxPostID = '/PostingID: ([0-9]+)<br>/is';
				preg_match($rgxPostID,$listing,$matchPostID);
				
				//Date: 2011-10-29
				$rgxPostDate = '/Date: ([0-9]{4}\-[0-9]{2}\-[0-9]{2})/is';
				preg_match($rgxPostDate,$listing,$matchPostDate);
				
				//var_dump($matchAddress);
			
				$strPrice = (isset($matchPrice[1])) ? $matchPrice[1] : null;
				$strRooms = (isset($matchRooms[1])) ? "$matchRooms[1] Bedroom" : "Studio";
				$strAddress = (isset($matchAddress[2])) ? $matchAddress[2] : null;
				$strPostID =  (isset($matchPostID[1])) ? $matchPostID[1] : null;
				$strPostDate = (isset($matchPostDate[1])) ? $matchPostDate[1] : null;
				$strAddress = str_replace("&amp;","and",$strAddress);
				$strAddress = urldecode($strAddress);
				
				
				if(isNull($strAddress)) {
					//$rgxAddress = '/>(([1-9]){1}([0-9]){0,5}\s(.*)([a-zA-Z]){2})</is';
					$rgxAddress = '/>(([1-9]){1}([0-9]){1,5}\s(.*)\s([A-Z]){2})</s';
					preg_match($rgxAddress,$listing,$matchAddress);
					$strAddress = (isset($matchAddress[1]) && strlen($matchAddress[1]) <= 100) ? strip_tags(str_replace("<br>"," ",$matchAddress[1])) : null;
				}
				
				if(isNull($strAddress)) {
					//$rgxAddress = '/>(([1-9]){1}([0-9]){0,5}\s(.*)([a-zA-Z]){2}\s([0-9]){5})/is';
					$rgxAddress = '/>(([1-9]){1}([0-9]){1,5}\s(.*),\s([A-Z]){2}\s([0-9]){5})</s';
					preg_match($rgxAddress,$listing,$matchAddress);
					$strAddress = (isset($matchAddress[1]) && strlen($matchAddress[1]) <= 100) ? strip_tags(str_replace("<br>"," ",$matchAddress[1])) : null;
				}
			
				if(isNull($strAddress)) {
					//<!-- CLTAG GeographicArea=737 S. Kingsley Dr. LA, CA 90005 -->
					//CLTAG GeographicArea=314 RAMSEY ROMEOVILLE IL -->
					//<li> <!-- CLTAG GeographicArea=314 RAMSEY ROMEOVILLE IL -->Location: 314 RAMSEY ROMEOVILLE IL</li>
					$rgxAddress = '/CLTAG GeographicArea=(.*) -->Location:/is';
					preg_match($rgxAddress,$listing,$matchAddress);
					$strAddress = (isset($matchAddress[1])) ? $matchAddress[1] : null;
					//var_dump($matchAddress);
					//echo $strAddress;
				}
				
				$strAddress = removeSpecialCharacters(cleanNonAscii($strAddress));
				
				if(!isNull($strAddress)) {
					$arrCoordinates = $gmap->getGeoCode($strAddress . " $city USA");
				
					//echo $city . "xxx<br />\r\n";
					//echo $strAddress . " $city USA<br />\r\n";
				
					//$arrInfo["Address"] = $strAddress . " USA";
					$arrInfo["Address"] = $strAddress;
					$arrInfo["Price"] = $strPrice;
					//$arrInfo["Rooms"] = $strRooms;
					$arrInfo["Type"] = $strRooms;
					$arrInfo["PostID"] = $strPostID;
					$arrInfo["Lat"] = (isset($arrCoordinates["lat"]) && !isNull($arrCoordinates["lat"])) ? $arrCoordinates["lat"] : null;
					$arrInfo["Lng"] = (isset($arrCoordinates["lng"]) && !isNull($arrCoordinates["lng"])) ? $arrCoordinates["lng"] : null;
					$arrInfo["PostDate"] = $strPostDate;
					$arrInfo["Link"] = $link;
					
					$dbBedrooms = ($strRooms != "Studio") ? str_replace(" Bedroom","",$strRooms) : "0" ;
					$dbBedrooms = ($strPrice >= 100000 && $dbBedrooms < 1) ? "99" : $dbBedrooms;
					
					//Bound by latitude (north to south) and longitude (east to west) 
					if(!isNull($arrInfo["Lat"]) && !isNull($arrInfo["Lng"])) { //if lat and lng of listing is not null
						$insertListing = $db->query("INSERT INTO Listings (Source, SourceID, Address, Price, 
							Bedrooms, Latitude, Longitude, PostDate, Link, Updated)
							VALUES ('craigslist', '$strPostID', '$strAddress', '$strPrice', '$dbBedrooms', 
							'$arrInfo[Lat]', '$arrInfo[Lng]', '$strPostDate', '$link', '$today')");
		
					
						if(!$bounded) { 
							array_push($arrListings, $arrInfo);
						} else {
						
						switch($strDirPoint1) {
							case "northwest":
								if ($arrInfo["Lat"] >= $boundLat2 && $arrInfo["Lat"] <= $boundLat1 && $arrInfo["Lng"] >= $boundLng1 && $arrInfo["Lng"] <= $boundLng2) {
									array_push($arrListings, $arrInfo);
								}
								break;
							case "northeast":
								if ($arrInfo["Lat"] >= $boundLat2 && $arrInfo["Lat"] <= $boundLat1 && $arrInfo["Lng"] <= $boundLng1 && $arrInfo["Lng"] >= $boundLng2) {
									array_push($arrListings, $arrInfo);
								}
								break;
							case "southwest":
								if ($arrInfo["Lat"] <= $boundLat2 && $arrInfo["Lat"] >= $boundLat1 && $arrInfo["Lng"] >= $boundLng1 && $arrInfo["Lng"] <= $boundLng2) {
									array_push($arrListings, $arrInfo);
								}
								break;
							default: //southeast
								if ($arrInfo["Lat"] <= $boundLat2 && $arrInfo["Lat"] >= $boundLat1 && $arrInfo["Lng"] <= $boundLng1 && $arrInfo["Lng"] >= $boundLng2) {
									array_push($arrListings, $arrInfo);
								}
								break;
							}
						}
					}	
				}	
			}
			
			$intPage = $intPage + 100; //increase page number to hit next set of results
			$url = $resetUrl . "&s=" . $intPage;
		}
		
		//PUSH STATIC POINTS
		if(!isNull($spAddress1)) {
			$arrStaticCoords = $gmap->getGeoCode($spAddress1);
			if(isset($arrStaticCoords["lat"]) && isset($arrStaticCoords["lng"])) {
				$spLat = $arrStaticCoords["lat"];
				$spLng = $arrStaticCoords["lng"];
				array_push($arrListings, array("Address"=>"$spAddress1", "Price"=>"-", "Type"=>"Point of Interest", "PostID"=>"$spAddressName1", "Lat"=>"$spLat", "Lng"=>"$spLng", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			}
		}
		
		if(!isNull($spAddress2)) {
			$arrStaticCoords = $gmap->getGeoCode($spAddress2);
			if(isset($arrStaticCoords["lat"]) && isset($arrStaticCoords["lng"])) {
				$spLat = $arrStaticCoords["lat"];
				$spLng = $arrStaticCoords["lng"];
				array_push($arrListings, array("Address"=>"$spAddress2", "Price"=>"-", "Type"=>"Point of Interest", "PostID"=>"$spAddressName2", "Lat"=>"$spLat", "Lng"=>"$spLng", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			}
		}
			
		
		//PUSH BOUNDED POINTS
		if($bounded) {
			array_push($arrListings, array("Address"=>"$bndPoint1", "Price"=>"-", "Type"=>"Boundary", "PostID"=>"Boundary", "Lat"=>"$boundLat1", "Lng"=>"$boundLng1", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			array_push($arrListings, array("Address"=>"Auto Boundary", "Price"=>"-", "Type"=>"Boundary", "PostID"=>"Boundary", "Lat"=>"$boundLat1", "Lng"=>"$boundLng2", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			array_push($arrListings, array("Address"=>"$bndPoint2", "Price"=>"-", "Type"=>"Boundary", "PostID"=>"Boundary", "Lat"=>"$boundLat2", "Lng"=>"$boundLng2", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			array_push($arrListings, array("Address"=>"Auto Boundary", "Price"=>"-", "Type"=>"Boundary", "PostID"=>"Boundary", "Lat"=>"$boundLat2", "Lng"=>"$boundLng1", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
		}
		
		
		//var_dump($arrListings);
			
		//Start XML file, create parent node
		$dom = new DOMDocument("1.0");
		$node = $dom->createElement("markers");
		$parnode = $dom->appendChild($node); 
		
		//header("Content-type: text/xml"); 
		
		// Iterate through the rows, adding XML nodes for each
		foreach($arrListings as $row) { //start at the second record instead
		  // ADD TO XML DOCUMENT NODE  
		  $node = $dom->createElement("marker");  
		  $newnode = $parnode->appendChild($node);   
		  $newnode->setAttribute("name", $row['PostID']);
		  $newnode->setAttribute("address", $row['Address']);
		  $newnode->setAttribute("price", $row['Price']);  
		  $newnode->setAttribute("lat", $row['Lat']);  
		  $newnode->setAttribute("lng", $row['Lng']);  
		  $newnode->setAttribute("type", $row['Type']); //# of bedrooms
		  $newnode->setAttribute("postdate", $row['PostDate']);
		  $newnode->setAttribute("link", $row['Link']);
		  
		}
		//DISPLAY XML DATA
		//echo $dom->saveXML();
		
		//$xmlData = $dom->saveXML();
		$fileName = $CFG->datadir . "xml/$searchId.xml";
		/*
		$fileHandle = fopen($fileName, 'w') or die("can't open file");
		fwrite($fileHandle, $xmlData);
		fclose($fileHandle);
		*/
		$dom->encoding = "utf-8";
		$dom->save($fileName);
		ignore_user_abort(false);
	}
	
	
	
	
	
	public function buildCraigslistXML($searchId) {
	
		global $CFG;
		global $db; 
		
		//$this->justTesting();
		ignore_user_abort(true);
		
		//$searchId = (isset($_GET["sid"])) ? $_GET["sid"] : null;
		$common = new Common();
		$gmap = new GoogleMaps();
		$gmap->setDeveloperKey("ABQIAAAAClHoNqepNQQ1UvQeBHuC5xQLfRswG7le8poDJksQuZeisyscyxSMx0XT5lyHIIM8udwo4iSXjznl5Q");
		
		$spAddress1 = "";
		$spAddress2 = "";
		$spAddressName1 = "";
		$spAddressName2 = "";
		$bndPoint1 = "";
		$bndPoint2 = "";
		
		$url = "";
		$bounded = false;
		$arrListings = array();
		$arrNewListings = array();
		$strDirPoint1 = "";
		$strDirPoint2 = "";
		$today = date("Y-m-d");
		
		$dbSearchQuery = $db->query("SELECT * FROM Searches WHERE SearchID='$searchId' LIMIT 1") or die(mysql_error());  
		
		
		while($s = $db->fetch_object($dbSearchQuery)) {
			$clCity = (!isNull($s->City)) ? $s->City : null;
			$clQuery = (!isNull($s->Query)) ? $s->Query : null;
			$clMinAsk = (!isNull($s->MinRent)) ? $s->MinRent : null;
			$clMaxAsk = (!isNull($s->MaxRent)) ? $s->MaxRent : null;
			$clBedrooms = (!isNull($s->Bedrooms)) ? $s->Bedrooms : null;
			$clHasPic = ($s->HasPic == "Y") ? "1" : null;
			$clAddTwo = ($s->AllowCats == "Y") ? "purrr" : null;
			$clAddThree = ($s->AllowDogs == "Y") ? "wooof" : null;
			
			$arrSearchCity = array("lasvegas","losangeles","newyork","orangecounty","sandiego","sfbay","washingtondc");
			$arrReplaceCity = array("Las Vegas, NV","Los Angeles, CA","New York City, NY","Orange county, CA","San Diego, CA","San Francisco, CA","Washington DC");	
			$city = ucwords(str_replace($arrSearchCity, $arrReplaceCity, $s->City));
			
			$spAddress1 = (!isNull($s->POI1)) ? $s->POI1 : null;
			$spAddress2 = (!isNull($s->POI2)) ? $s->POI2 : null;
		
			$spAddressName1 = (!isNull($s->POIName1)) ? $s->POIName1 : null;
			$spAddressName2 = (!isNull($s->POIName2)) ? $s->POIName2 : null;
			
			$bndPoint1 = (!isNull($s->Boundary1)) ? $s->Boundary1 . " " . $city . " USA" : null;
			$bndPoint2 = (!isNull($s->Boundary2)) ? $s->Boundary2 . " " . $city . " USA" : null;
			
			$url = $this->getCraigslistUrl($clCity, $clQuery, $clMinAsk, $clMaxAsk, $clBedrooms, $clHasPic, $clAddTwo, $clAddThree);
		}
		
		
		//DETERMINE THE RELATIVE POSITION OF THE BOUNDING POINTS
		if($bndPoint1 != null && $bndPoint2 != null) {
			$arrBound1 = $gmap->getGeoCode($bndPoint1);
			$arrBound2 = $gmap->getGeoCode($bndPoint2);
			
			$boundLat1 = (isset($arrBound1["lat"])) ? $arrBound1["lat"] : null;
			$boundLng1 = (isset($arrBound1["lng"])) ? $arrBound1["lng"] : null;
			$boundLat2 = (isset($arrBound2["lat"])) ? $arrBound2["lat"] : null;
			$boundLng2 = (isset($arrBound2["lng"])) ? $arrBound2["lng"] : null;
			
			if(!isNull($boundLat1) && !isNull($boundLng1) && !isNull($boundLat2) && !isNull($boundLng2)) {
				if($boundLat1 > $boundLat2) {
					$strDirPoint1 = "north";
					$strDirPoint2 = "south";
				} else {
					$strDirPoint1 = "south";
					$strDirPoint2 = "north";
				}
				
				if($boundLng1 > $boundLng2) {
					$strDirPoint1 .= "east";
					$strDirPoint2 .= "west";
				} else {
					$strDirPoint1 .= "west";
					$strDirPoint2 .= "east";
				}
				
				$bounded = true;
			}
		}
		
		//START INITIAL SCRAPE TO ONLY GET TOTAL RESULTS
		$startingList = $common->scrapeUrl($url);
		$rgxListTotal = '/<b>Found: ([0-9]+) Displaying:/is';
		preg_match($rgxListTotal,$startingList,$matchListTotal);
		
		if(isset($matchListTotal[1])) {
			$totalPages = ceil($matchListTotal[1] / 100);
		} else {
			$totalPages = 0;
			
			$arrCoordinates = $gmap->getGeoCode("$city USA");
			
			$arrInfo["Address"] = $clCity;
			$arrInfo["Price"] = $clQuery;
			$arrInfo["Type"] = "no-results";
			$arrInfo["PostID"] = "No Listings Found";
			$arrInfo["Lat"] = $arrCoordinates["lat"];
			$arrInfo["Lng"] = $arrCoordinates["lng"];
			$arrInfo["PostDate"] = $today;
			$arrInfo["Link"] = "http://mapmasher.com/";
												
			array_push($arrListings, $arrInfo);
		}
		
		//if($totalPages > 2) { $totalPages = 3; }
		if($totalPages > 1) { $totalPages = 2; }
		//echo $totalPages .  " XXX<br />\r\n";
		
		$intPage = 0;
		$resetUrl = $url;
		
		for($count=0; $count < $totalPages; $count++) {
		
			$listings = $common->scrapeUrl($url);
		
			$rgxListRef = '/([a-zA-Z]{3}) \s?([0-9]{1,2}) - <a href="(.+?)">\$([0-9]{1,4})/is';
			preg_match_all($rgxListRef,$listings,$matchListRef);
			//preg_match_all($rgxListRef,$listings,$matchListRef,PREG_SET_ORDER)
		
			for($x=0; $x < count($matchListRef[3]); $x++) {
				$rgxPid = '/(.+?)\/([0-9]+)\.html/';
				preg_match($rgxPid,$matchListRef[3][$x],$matchPid);
				$pid = $matchPid[2];
				$source = "craigslist";
				
				$dbQuery = $db->query("SELECT * FROM Listings WHERE SourceID='$pid' AND Source='$source' LIMIT 1");
				$numRecords = $db->num_rows($dbQuery);
				
				if($numRecords > 0) {
					while($q = $db->fetch_object($dbQuery)) {
						//$arrInfo["Address"] = $q->Address . " USA";
						$arrInfo["Address"] = $q->Address;
						$arrInfo["Price"] = $q->Price;
						$arrInfo["Type"] = ($q->Bedrooms > 0) ? $q->Bedrooms." Bedroom" : "Studio";
						$arrInfo["PostID"] = $q->SourceID;
						$arrInfo["Lat"] = $q->Latitude;
						$arrInfo["Lng"] = $q->Longitude;
						$arrInfo["PostDate"] = $q->PostDate;
						$arrInfo["Link"] = $q->Link;
							
						if(!$bounded) { 						
							array_push($arrListings, $arrInfo);
							
						} else {
						
							switch($strDirPoint1) {
								case "northwest":
									if ($arrInfo["Lat"] >= $boundLat2 && $arrInfo["Lat"] <= $boundLat1 && $arrInfo["Lng"] >= $boundLng1 && $arrInfo["Lng"] <= $boundLng2) {
										array_push($arrListings, $arrInfo);
									}
									break;
								case "northeast":
									if ($arrInfo["Lat"] >= $boundLat2 && $arrInfo["Lat"] <= $boundLat1 && $arrInfo["Lng"] <= $boundLng1 && $arrInfo["Lng"] >= $boundLng2) {
										array_push($arrListings, $arrInfo);
									}
									break;
								case "southwest":
									if ($arrInfo["Lat"] <= $boundLat2 && $arrInfo["Lat"] >= $boundLat1 && $arrInfo["Lng"] >= $boundLng1 && $arrInfo["Lng"] <= $boundLng2) {
										array_push($arrListings, $arrInfo);
									}
									break;
								default: //southeast
									if ($arrInfo["Lat"] <= $boundLat2 && $arrInfo["Lat"] >= $boundLat1 && $arrInfo["Lng"] <= $boundLng1 && $arrInfo["Lng"] >= $boundLng2) {
										array_push($arrListings, $arrInfo);
									}
									break;
							}
						}
						
						
					}
				} else {
					array_push($arrNewListings,$matchListRef[3][$x]);
				}
			}
		
		
			//BEGIN SCRAPING NEW LISTING PAGES
			foreach($arrNewListings as $link) {
			
				$listing = $common->scrapeUrl($link);			
				
				//<!-- CLTAG GeographicArea=737 S. Kingsley Dr. LA, CA 90005 -->
				$rgxAddress = '/(<a target="_blank" href="http:\/\/maps\.google\.com\/\?q=loc%3A\+(.*?)"(.*?)>google map<\/a>)/is';
				preg_match($rgxAddress,$listing,$matchAddress);
				
				//<h2>$1250 NYC STYLISH Jr.Brm in 20's bldg. Hardwood/Brick/Stainless  (Miracle Mile) (map)</h2>
				//<h2>$1250 / 1br - 900ft&sup2; - Contemporary Bright New 1 Bed 1 Bath w/ balcony (Hancock Park Adj./Koretown) (map)</h2>
				$rgxPrice = '/<h2>\$([0-9]+)(.*)<\/h2>/is';
				preg_match($rgxPrice,$listing,$matchPrice);
				
				$rgxRooms = '/([0-9]+)(br|bedroom)/';
				preg_match($rgxRooms,$listing,$matchRooms);
				
				//PostingID: 2672241861<br>
				$rgxPostID = '/PostingID: ([0-9]+)<br>/is';
				preg_match($rgxPostID,$listing,$matchPostID);
				
				//Date: 2011-10-29
				$rgxPostDate = '/Date: ([0-9]{4}\-[0-9]{2}\-[0-9]{2})/is';
				preg_match($rgxPostDate,$listing,$matchPostDate);
				
				//var_dump($matchAddress);
			
				$strPrice = (isset($matchPrice[1])) ? $matchPrice[1] : null;
				$strRooms = (isset($matchRooms[1])) ? "$matchRooms[1] Bedroom" : "Studio";
				$strAddress = (isset($matchAddress[2])) ? $matchAddress[2] : null;
				$strPostID =  (isset($matchPostID[1])) ? $matchPostID[1] : null;
				$strPostDate = (isset($matchPostDate[1])) ? $matchPostDate[1] : null;
				$strAddress = str_replace("&amp;","and",$strAddress);
				$strAddress = urldecode($strAddress);
				
				if(isNull($strAddress)) {
					//$rgxAddress = '/>(([1-9]){1}([0-9]){0,5}\s(.*)([a-zA-Z]){2})</is';
					$rgxAddress = '/>(([1-9]){1}([0-9]){1,5}\s(.*)\s([A-Z]){2})</s';
					preg_match($rgxAddress,$listing,$matchAddress);
					$strAddress = (isset($matchAddress[1]) && strlen($matchAddress[1]) <= 100) ? strip_tags(str_replace("<br>"," ",$matchAddress[1])) : null;
				}
				
				if(isNull($strAddress)) {
					//$rgxAddress = '/>(([1-9]){1}([0-9]){0,5}\s(.*)([a-zA-Z]){2}\s([0-9]){5})/is';
					$rgxAddress = '/>(([1-9]){1}([0-9]){1,5}\s(.*),\s([A-Z]){2}\s([0-9]){5})</s';
					preg_match($rgxAddress,$listing,$matchAddress);
					$strAddress = (isset($matchAddress[1]) && strlen($matchAddress[1]) <= 100) ? strip_tags(str_replace("<br>"," ",$matchAddress[1])) : null;
				}
				
				if(isNull($strAddress)) {
					//<!-- CLTAG GeographicArea=737 S. Kingsley Dr. LA, CA 90005 -->
					//CLTAG GeographicArea=314 RAMSEY ROMEOVILLE IL -->
					//<li> <!-- CLTAG GeographicArea=314 RAMSEY ROMEOVILLE IL -->Location: 314 RAMSEY ROMEOVILLE IL</li>
					$rgxAddress = '/CLTAG GeographicArea=(.*) -->Location:/is';
					preg_match($rgxAddress,$listing,$matchAddress);
					$strAddress = (isset($matchAddress[1])) ? $matchAddress[1] : null;
					//var_dump($matchAddress);
					//echo $strAddress;
				}
				
				$strAddress = removeSpecialCharacters(cleanNonAscii($strAddress));
				
				if(!isNull($strAddress)) {
					$arrCoordinates = $gmap->getGeoCode($strAddress . " $city USA");
				
					//echo $city . "xxx<br />\r\n";
					//echo $strAddress . " $city USA<br />\r\n";
				
					//$arrInfo["Address"] = $strAddress . " USA";
					$arrInfo["Address"] = $strAddress;
					$arrInfo["Price"] = $strPrice;
					//$arrInfo["Rooms"] = $strRooms;
					$arrInfo["Type"] = $strRooms;
					$arrInfo["PostID"] = $strPostID;
					$arrInfo["Lat"] = (isset($arrCoordinates["lat"]) && !isNull($arrCoordinates["lat"])) ? $arrCoordinates["lat"] : null;
					$arrInfo["Lng"] = (isset($arrCoordinates["lng"]) && !isNull($arrCoordinates["lng"])) ? $arrCoordinates["lng"] : null;
					$arrInfo["PostDate"] = $strPostDate;
					$arrInfo["Link"] = $link;
					
					$dbBedrooms = ($strRooms != "Studio") ? str_replace(" Bedroom","",$strRooms) : "0" ;
					$dbBedrooms = ($strPrice >= 100000 && $dbBedrooms < 1) ? "99" : $dbBedrooms;
					
					//Bound by latitude (north to south) and longitude (east to west) 
					if(!isNull($arrInfo["Lat"]) && !isNull($arrInfo["Lng"])) { //if lat and lng of listing is not null
						$insertListing = $db->query("INSERT INTO Listings (Source, SourceID, Address, Price, 
							Bedrooms, Latitude, Longitude, PostDate, Link, Updated)
							VALUES ('craigslist', '$strPostID', '$strAddress', '$strPrice', '$dbBedrooms', 
							'$arrInfo[Lat]', '$arrInfo[Lng]', '$strPostDate', '$link', '$today')");
		
					
						if(!$bounded) { 
							array_push($arrListings, $arrInfo);
						} else {
						
						switch($strDirPoint1) {
							case "northwest":
								if ($arrInfo["Lat"] >= $boundLat2 && $arrInfo["Lat"] <= $boundLat1 && $arrInfo["Lng"] >= $boundLng1 && $arrInfo["Lng"] <= $boundLng2) {
									array_push($arrListings, $arrInfo);
								}
								break;
							case "northeast":
								if ($arrInfo["Lat"] >= $boundLat2 && $arrInfo["Lat"] <= $boundLat1 && $arrInfo["Lng"] <= $boundLng1 && $arrInfo["Lng"] >= $boundLng2) {
									array_push($arrListings, $arrInfo);
								}
								break;
							case "southwest":
								if ($arrInfo["Lat"] <= $boundLat2 && $arrInfo["Lat"] >= $boundLat1 && $arrInfo["Lng"] >= $boundLng1 && $arrInfo["Lng"] <= $boundLng2) {
									array_push($arrListings, $arrInfo);
								}
								break;
							default: //southeast
								if ($arrInfo["Lat"] <= $boundLat2 && $arrInfo["Lat"] >= $boundLat1 && $arrInfo["Lng"] <= $boundLng1 && $arrInfo["Lng"] >= $boundLng2) {
									array_push($arrListings, $arrInfo);
								}
								break;
							}
						}
					}	
				}	
			}
			
			$intPage = $intPage + 100; //increase page number to hit next set of results
			$url = $resetUrl . "&s=" . $intPage;
		}
		
		//PUSH STATIC POINTS
		if(!isNull($spAddress1)) {
			$arrStaticCoords = $gmap->getGeoCode($spAddress1);
			if(isset($arrStaticCoords["lat"]) && isset($arrStaticCoords["lng"])) {
				$spLat = $arrStaticCoords["lat"];
				$spLng = $arrStaticCoords["lng"];
				array_push($arrListings, array("Address"=>"$spAddress1", "Price"=>"-", "Type"=>"Point of Interest", "PostID"=>"$spAddressName1", "Lat"=>"$spLat", "Lng"=>"$spLng", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			}
		}
		
		if(!isNull($spAddress2)) {
			$arrStaticCoords = $gmap->getGeoCode($spAddress2);
			if(isset($arrStaticCoords["lat"]) && isset($arrStaticCoords["lng"])) {
				$spLat = $arrStaticCoords["lat"];
				$spLng = $arrStaticCoords["lng"];
				array_push($arrListings, array("Address"=>"$spAddress2", "Price"=>"-", "Type"=>"Point of Interest", "PostID"=>"$spAddressName2", "Lat"=>"$spLat", "Lng"=>"$spLng", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			}
		}
			
		
		//PUSH BOUNDED POINTS
		if($bounded) {
			array_push($arrListings, array("Address"=>"$bndPoint1", "Price"=>"-", "Type"=>"Boundary", "PostID"=>"Boundary", "Lat"=>"$boundLat1", "Lng"=>"$boundLng1", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			array_push($arrListings, array("Address"=>"Auto Boundary", "Price"=>"-", "Type"=>"Boundary", "PostID"=>"Boundary", "Lat"=>"$boundLat1", "Lng"=>"$boundLng2", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			array_push($arrListings, array("Address"=>"$bndPoint2", "Price"=>"-", "Type"=>"Boundary", "PostID"=>"Boundary", "Lat"=>"$boundLat2", "Lng"=>"$boundLng2", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
			array_push($arrListings, array("Address"=>"Auto Boundary", "Price"=>"-", "Type"=>"Boundary", "PostID"=>"Boundary", "Lat"=>"$boundLat2", "Lng"=>"$boundLng1", "PostDate"=>"$today", "Link" => "http://mapmasher.com/"));
		}
		
		
		//var_dump($arrListings);
			
		//Start XML file, create parent node
		$dom = new DOMDocument("1.0");
		$node = $dom->createElement("markers");
		$parnode = $dom->appendChild($node); 
		
		//header("Content-type: text/xml"); 
		
		// Iterate through the rows, adding XML nodes for each
		foreach($arrListings as $row) { //start at the second record instead
		  // ADD TO XML DOCUMENT NODE  
		  $node = $dom->createElement("marker");  
		  $newnode = $parnode->appendChild($node);   
		  $newnode->setAttribute("name", $row['PostID']);
		  $newnode->setAttribute("address", $row['Address']);
		  $newnode->setAttribute("price", $row['Price']);  
		  $newnode->setAttribute("lat", $row['Lat']);  
		  $newnode->setAttribute("lng", $row['Lng']);  
		  $newnode->setAttribute("type", $row['Type']); //# of bedrooms
		  $newnode->setAttribute("postdate", $row['PostDate']);
		  $newnode->setAttribute("link", $row['Link']);
		  
		}
		//DISPLAY XML DATA
		echo $dom->saveXML();
		
		//$xmlData = $dom->saveXML();
		//$fileName = $CFG->datadir . "xml/$searchId.xml";
		/*
		$fileHandle = fopen($fileName, 'w') or die("can't open file");
		fwrite($fileHandle, $xmlData);
		fclose($fileHandle);
		*/
		//$dom->encoding = "utf-8";
		//$dom->save($fileName);
		ignore_user_abort(false);
	}
	
	
	public function getCraigslistUrl($clCity, $clQuery, $clMinAsk, $clMaxAsk, $clBedrooms, $clHasPic, $clAddTwo, $clAddThree) {
		$clUrl = "http://$clCity.craigslist.org/search/hhh?srchType=A";
		
		//$clUrl .= "?query=$clQuery&catAbb=hhh&srchType=A&minAsk=$clMinAsk&maxAsk=$clMaxAsk&bedrooms=$clBedrooms&hasPic=$clHasPic";
	
		if(!isNull($clQuery) && $clQuery != "Enter Search") { $clUrl .= "&query=".urlencode($clQuery); }
		if(!isNull($clMinAsk)) { $clUrl .= "&minAsk=$clMinAsk"; }
		if(!isNull($clMaxAsk)) { $clUrl .= "&maxAsk=$clMaxAsk"; }
		if(!isNull($clBedrooms)) { $clUrl .= "&bedrooms=$clBedrooms"; }
		if(!isNull($clHasPic)) { $clUrl .= "&hasPic=$clHasPic"; }
		if(!isNull($clAddTwo)) { $clUrl .= "&addTwo=$clAddTwo"; }
		if(!isNull($clAddThree)) { $clUrl .= "&addThree=$clAddThree"; }
		
		return $clUrl;
	}
	
	 	
}

?>