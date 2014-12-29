<?php 
class Communication {

	public function sendEmail($arrRecipients, $strSubject, $strContent) {
		global $CFG;
		
		$emailBoundary = "0__=" . md5(uniqid());
		$strFrom = "$CFG->fullname <$CFG->noreply>";
		
		$strHeader = "From: " . $strFrom . "\r\nReply-To: " . $strFrom . "\r\n";
		$strHeader .= "MIME-Version: 1.0" . "\r\n";
		$strHeader .= "Content-Type: multipart/alternative;boundary=\"".$emailBoundary."\"" . "\r\n";
		$strHeader .= "Content-Class: urn:content-classes:message" . "\r\n";
		
		$strContent = ereg_replace( '/\n\r|\r\n/', '', $strContent);
		
		$strMessage = $this->formatEmailMessage($strContent, $emailBoundary);
		
		if(is_array($arrRecipients)) { //if sending to multiple users
			foreach($arrRecipients as $strRecipient) {
				mail($strRecipient, $strSubject, $strMessage, $strHeader);
			}
		} else { //else send to a single email
			mail($arrRecipients, $strSubject, $strMessage, $strHeader);
		}
	}
	
	
	public function sendEmailFrom($strName, $strEmail, $arrRecipients, $strSubject, $strContent) {
		global $CFG;
		
		$emailBoundary = "0__=" . md5(uniqid());
		$strFrom = "$strName <$strEmail>";
		
		$strHeader = "From: " . $strFrom . "\r\nReply-To: " . $strFrom . "\r\n";
		$strHeader .= "MIME-Version: 1.0" . "\r\n";
		$strHeader .= "Content-Type: multipart/alternative;boundary=\"".$emailBoundary."\"" . "\r\n";
		$strHeader .= "Content-Class: urn:content-classes:message" . "\r\n";
		
		$strContent = ereg_replace( '/\n\r|\r\n/', '', $strContent);
		
		$strMessage = $this->formatEmailMessage($strContent, $emailBoundary);
		
		if(is_array($arrRecipients)) { //if sending to multiple users
			foreach($arrRecipients as $strRecipient) {
				mail($strRecipient, $strSubject, $strMessage, $strHeader);
			}
		} else { //else send to a single email
			mail($arrRecipients, $strSubject, $strMessage, $strHeader);
		}
	}
	

	private function formatEmailMessage($strContent, $emailBoundary) {
		
		$strContentEmailBoundary = "--" . $emailBoundary . "\r\n";
		$strContentEmailBoundaryEnd = "\r\n" . "--" . $emailBoundary . "--";
		$strContentTransferEncoding = "Content-Transfer-Encoding: 7bit" . "\r\n\r\n";
		$strContentTypeText = "Content-Type: text/plain;charset=ISO-8859-1" . "\r\n";
		$strContentTypeHTML = "Content-Type: text/html;charset=ISO-8859-1" . "\r\n";
		 	
		/* Format Text E-mail */
		$strMessage = $strContentEmailBoundary;
		$strMessage .= $strContentTypeText;
		$strMessage .= $strContentTransferEncoding;	
		$strMessage .= self::formatTextEmailMessage($strContent) . "\r\n\r\n";
		
		/* Format HTML E-mail */		
		$strMessage .= $strContentEmailBoundary;
		$strMessage .= $strContentTypeHTML;
		$strMessage .= $strContentTransferEncoding;
		$strMessage .= self::formatHTMLEmailMessage($strContent) . "\r\n\r\n";
		
		/* Output ending boundary */
		$strMessage .= $strContentEmailBoundaryEnd;	
		
		return $strMessage;		
	}
	
	
	private function formatTextEmailMessage($strPost) {
		$strPostText = str_replace("</p>","\r\n\r\n",$strPost);
		$strPostText = str_replace("&quot;","\"",$strPostText);
		$strPostText = str_replace("&nbsp;"," ",$strPostText);
		$strPostText = str_replace("<br />","\r\n",$strPostText);
		$strPostText = str_replace("<br>","\r\n",$strPostText);
		$strPostText = strip_tags($strPostText);
		return $strPostText;
	}
	
	
	private function formatHTMLEmailMessage($strPost) {
		$strPostHTML = "<html><body>";
		$strPostHTML .= $strPost;
		$strPostHTML = str_replace("<p>","",$strPostHTML);
		$strPostHTML = str_replace("</p>","<br /><br />",$strPostHTML);
		$strPostHTML .= "</body></html>";
		return $strPostHTML;
	}
	
	public function createChainId($id1, $id2, $roomID=null) {
		$id1 = trim($id1);
		$id2 = trim($id2);
		$roomID = trim($roomID);
		
		if((int)$id1 < (int)$id2) {
			$chainID = $id1."n"."$id2";
		} else {
			$chainID = $id2."n"."$id1";
		}
		
		$chainID .= "r$roomID";
		
		return $chainID;
	}
	
}

?>