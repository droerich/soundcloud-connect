<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionType.class.php');

// Souncloud API
require_once (WCF_DIR . 'lib/data/soundcloud/Soundcloud.php');

// Define constants for Souncloud API registration
define('CLIENT_ID', 'd3b9d13d8379b6c19044e4ec6a321102');
define('CLIENT_SECRET', '9c3b63959977bf61404110fc603427f2');
define('REDIRECT_URL', 'http://www.meniunddeve.de/wbb-test/index.php?form=UserProfileEdit');

/**
 * My implementation of a user option type
 *
 */
class OptionTypeLinkerimage implements OptionType{

	/*
	 * The Soundcloud User ID
	 */
	private $sc_userId = null;

	/**
	 * Returns the html code for the form element of this option.
	 * 
	 * @param	array		$optionData
	 * @return	string		html
	 */
	public function getFormElement(&$optionData) {
		// DEBUG
		$return = "<h3>Inhalt von optionData</h3>";
		foreach ( $optionData as $x ) {
			  $return .= "<tt>$x</tt></br>";
		}
		$return .= 'Der Wert von WCF_N ist ' . WCF_N . '</br>';
		if ( $this->isUserConnected( WCF::getUser()->userID ) ) {
			$return .= 'Du bist bereits mit Soundcloud verbunden.</br>';
		} else {
			$return .= 'Du bist noch nicht mit Soundcloud verbunden.</br>';
		}
		
		// create SC client object with app credentials
		$sc_client = new Services_Soundcloud(CLIENT_ID, CLIENT_SECRET, REDIRECT_URL);
		
		// URL to SC-connect-image
		$buttonImageUrl = 'http://connect.soundcloud.com/2/btn-connect-l.png';
		
		// Get SC authorize URL and send the URL parameter status along 
		// with it. It will be sent back with the redirect URI.
		$sc_authorizeUrl = $sc_client->getAuthorizeUrl() . '&state=soundcloudConnect';
		
		// DEBUG: Punkt in nächster Zeile entfernen
		$return .= "<a href=\"$sc_authorizeUrl\" target=\"_self\">";
		$return .= "<img src=\"$buttonImageUrl\">";
		$return .= '</a>';
		$return .= '</br>';
		if (isset($_GET['state']) && $_GET['state'] == 'soundcloudConnect') {
		
			$return .= 'Habe code-Parameter empfangen. Starte Decodierung...</br>';
			
			// exchange authorization code for access token
			$sc_code = $_GET['code'];
			$sc_token = $sc_client->accessToken($sc_code);
	
			// make an authenticated call
			try {
				$sc_response = json_decode($sc_client->get('me'), true);
			} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
// 				exit($e->getMessage());
				$return .= "Soundcloud-Fehler: " . $e->getMessage() . '</br>';
				return $return;
			}
			// Save Soundcloud user ID
			$this->sc_userId = $sc_response['id'];
			$sc_username = $sc_response['username'];
			$return .= 'Deine Soundcloud User-ID lautet <b>' . $this->sc_userId . '</b>';
			$return .= "und dein Benutzername $sc_username</br>";
			
			// Save the soundcloud data to the database
			$this->saveSoundcloudUser( $this->sc_userId );
			
		} else {
			$return .= 'Habe keinen code empfangen.</br>';
		}
		
		return $return;
	}
	
	/**
	 * Validates the form input for this option.
	 * Throws an exception, if validation fails.
	 * 
	 * @param	array		$optionData
	 * @param	string		$newValue
	 */
	public function validate($optionData, $newValue) {
	}
	
	/**
	 * Returns the value of this option for saving in the database.
	 * 
	 * @param	array		$optionData
	 * @param	string		$newValue
	 * @return	string
	 */
	public function getData($optionData, $newValue){
		//return user ID
// 		echo 'this->sc_userId: ' . $this->sc_userId . "\n\n";
// 		echo "Hallo alle!\n";
// 		return $this->sc_userId;
		return $newValue;
	}
	
	private function saveSoundcloudUser( $sc_userId ) {
		$sqlQuery = 'REPLACE INTO	wcf' . WCF_N . '_user_soundcloud_connect
				(userID, soundcloudID)
					VALUES (' . intval(WCF::getUser()->userID) . ", '" . $sc_userId . "')";
		WCF::getDB()->sendQuery($sqlQuery);
	}
	
	private function isUserConnected( $wcf_userID ) {
		$sqlQuery = 'SELECT soundcloudID
			     FROM wcf' . WCF_N . '_user_soundcloud_connect
			     WHERE userID = ' . intval($wcf_userID);
		$row = WCF::getDB()->getFirstRow( $sqlQuery );
		
		$sc_userId = $row['soundcloudID'];
		return !empty($sc_userId);
	}
}
?>