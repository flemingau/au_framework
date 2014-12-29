<?php

class User {
	
	function __construct() {
		global $CFG;
		global $db;
		
		$this->currentUserID	= (isset($_SESSION["LOGIN"]["ID"]) ? $_SESSION["LOGIN"]["ID"] : null); //currentUser ID
		
		$query = $db->query("SELECT U.*, U.ID, U.FirstName, U.LastName, U.Email, U.LastLogin, U.Registered, U.AcceptTerms,
				U.Type, U.ValidationKey, U.Active, U.HomeCity, U.HomeState, U.SearchCity, U.SearchState, 
				U.CurrentCity, U.CurrentState, U.oauthProvider, U.oauthUserID, U.MoveStartDate, U.MoveEndDate, 
				U.SearchBudgetMin, U.SearchBudgetMax, U.Flexibility, U.ShowInSearch, U.LoggedIn
			FROM Users U WHERE ID='$this->currentUserID' LIMIT 1");
		
		if ($u = $db->fetch_object($query)) {
			$this->ID = $u->ID;
			$this->FirstName = $u->FirstName;
			$this->LastName = $u->LastName;
			$this->Email = $u->Email;
			$this->LastLogin = $u->LastLogin;
			$this->Registered = $u->Registered;
			$this->AcceptTerms = $u->AcceptTerms;
			$this->Type = $u->Type;
			$this->ValidationKey = $u->ValidationKey;
			$this->Active = $u->Active;
			$this->HomeCity = $u->HomeCity;
			$this->HomeState = $u->HomeState;
			$this->SearchCity = $u->SearchCity;
			$this->SearchState = $u->SearchState;
			$this->CurrentCity = $u->CurrentCity;
			$this->CurrentState = $u->CurrentState;
			$this->MoveStartDate = $u->MoveStartDate;
			$this->MoveEndDate = $u->MoveEndDate;
			$this->Flexibility = $u->Flexibility;
			$this->SearchBudgetMin = $u->SearchBudgetMin;
			$this->SearchBudgetMax = $u->SearchBudgetMax;
			$this->ShowInSearch = $u->ShowInSearch;
			$this->oauthProvder = $u->oauthProvider;
			$this->oauthUserID = $u->oauthUserID;
			$this->LoggedIn = $u->LoggedIn;
			//$this->TwitterID = $u->TwitterID;
			//$this->TwitterPassword = $u->TwitterPassword;
		}
		
		//$this->FirstName = (isset($this->FirstName)) ? $this->FirstName : "fb first";
		//$this->LastName = (isset($this->LastName)) ? $this->LastName : "fb last";
	}
	
	/*
	 * Checks to see if basic required information is filled
	 */
	public function checkProfileRequirement() 
	{
		global $CFG;
		global $db;
		
		$page = new Page();
		
		$arrProtected = $CFG->protected;
		$boolProtected = false;
		
		if(in_array(trim($page->Section),$arrProtected)) {
			$boolProtected = true;
		}
		
		if($page->Section != "user-setup" && $boolProtected) {
			$selUserGoals = $db->query("SELECT UG.* FROM UserGoals UG
				WHERE UG.UserID='$this->ID' LIMIT 1");
			
			if($g = $db->fetch_object($selUserGoals)) {
				$findSharedSpace = $g->FindSharedSpace;
				$findEntireSpace = $g->FindEntireSpace;
				$findRoommate = $g->FindRoommate;
				$listSpace = $g->ListSpace;
				$vouching = $g->Vouching;
				$livingThere = $g->LivingThere;
				
				if(!$findSharedSpace && !$findEntireSpace && !$findRoommate && !$listSpace && !$vouching) { //user has not indicated how they plan to use the site
					header("Location: $CFG->root" . "user/setup/");
				} elseif(!$findSharedSpace && !$findEntireSpace && !$findRoommate && !$listSpace && $vouching) { //user only here to vouch
					if($page->Section != "user-vouch" && $page->Section != "user-messages" && $page->Section != "user-favorites") {
						header("Location: $CFG->root" . "user/vouch/");
					}
				} elseif(!$findSharedSpace && !$findEntireSpace && !$findRoommate && $listSpace && !$vouching && !$livingThere) { //user only here to list a space they are not living in
					if($page->Section != "user-listing" && $page->Section != "listing-setup" && $page->Section != "user-account") { //prevent redirect loop when hitting user-listing page
						header("Location: $CFG->root" . "user/listing/");
					}
				} else {
				
				}
				
			} else {
				header("Location: $CFG->root" . "user/setup/");
			}
		}
	}
	
	

}

?>