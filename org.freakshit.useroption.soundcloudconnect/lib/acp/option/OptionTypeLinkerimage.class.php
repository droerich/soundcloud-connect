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

	
	/**
	 * Returns the html code for the form element of this option.
	 * 
	 * @param	array		$optionData
	 * @return	string		html
	 */
	public function getFormElement(&$optionData) {
	
		// create SC client object with app credentials
		$sc_client = new Services_Soundcloud(CLIENT_ID, CLIENT_SECRET, REDIRECT_URL);
		
		// path to Soundcloud icons
		$connectImageUrl = PAGE_URL . '/wcf/icon/btn-connect-m.png';
		$disconnectImageUrl = PAGE_URL . '/wcf/icon/btn-disconnect-m.png';
		
		// Get SC authorize URL and send the URL parameter status along 
		// with it. It will be sent back with the redirect URI.
		$sc_connectUrl = $sc_client->getAuthorizeUrl();
		$sc_connectUrl .= '&state=soundcloudConnect'; // will be sent back by Soundcloud
		$sc_connectUrl .= '&scope=non-expiring'; // request non-expiring auth token
		
		$sc_disconnectUrl = PAGE_URL . '/index.php?form=UserProfileEdit&state=soundcloudDisconnect';
		
		if (isset($_GET['state']) && $_GET['state'] == 'soundcloudConnect') { // Aufruf kommt von Soundcloud (Redirect URI) -> Autorisierungsvorgang
	
			$return = 'Habe code-Parameter empfangen. Starte Decodierung...</br>';
			
			// exchange authorization code for access token
			$sc_code = $_GET['code'];
			$sc_token = $sc_client->accessToken($sc_code);
			
			// save auth token to database
			$this->saveSoundcloudAccessToken( $sc_token );
	
			// make an authenticated call
			try {
				$sc_response = json_decode($sc_client->get('me'), true);
			} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
				$return .= "Soundcloud-Fehler: " . $e->getMessage() . '</br>';
				return $return;
			}
			// Save Soundcloud user ID
			$sc_userId = $sc_response['id'];
			$sc_username = $sc_response['username'];
			
			// Display disconnect-button
			$return  = "<a href=\"$sc_disconnectUrl\" target=\"_self\">";
			$return .= "<img src=\"$disconnectImageUrl\">";
			$return .= '</a>';
			$return .= '</br>';
			
			// Save the soundcloud data to the database
			$this->saveSoundcloudUser( $sc_userId );
			
			$return .= 'Soundcloud-Autorisierung war erfolgreich.</br>';
		
		} elseif ( isset($_GET['state']) && $_GET['state'] == 'soundcloudDisconnect' ) { // Aufruf kommt von dieser Seite -> Soundlcoud-Daten löschen
			if ( !$this->isUserConnected( WCF::getUser()->userID ) ) { // Benutzer ist noch nicht mit SC verbunden
				return "Fehler: Lösen der Verbindung nicht möglich: Benutzer ist noch nicht mit Soundcloud verbunden.</br>";
			}
			$this->deleteSoundcloudUser( WCF::getUser()->userID );
			
			// Display Soundcloud connect-button
			$return  = "<a href=\"$sc_connectUrl\" target=\"_self\">";
			$return .= "<img src=\"$connectImageUrl\">";
			$return .= '</a>';
			$return .= '</br>';
			$return .= 'Soundcloud-Verbindung wurde erfolgreich gelöst.</br>';
			
		} else { // Nicht im Autorisierungs-Vorgang, nicht im Lösch-Vorgang
			if ( $this->isUserConnected( WCF::getUser()->userID ) ) { // Benutzer is bereits mit SC verbunden
				// Get Soundcloud username from Soundcloud				
				$sc_token = $this->fetchSoundcloudAccessToken( intval(WCF::getUser()->userID) );
				$sc_client->setAccessToken( $sc_token );
				try {
					$sc_response = json_decode($sc_client->get('me'), true);
				} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
					$return = "Soundcloud-Fehler: " . $e->getMessage() . '</br>';
					return $return;
				}			
				$sc_username = $sc_response['username'];
				
				// Display disconnect-button
				$return  = "<a href=\"$sc_disconnectUrl\" target=\"_self\">";
				$return .= "<img src=\"$disconnectImageUrl\">";
				$return .= '</a>';
				$return .= '</br>';
				$return .= "Dein Profil ist mit dem Soundcloud-Benutzer <b>${sc_username}</b> verbunden.</br>";
			} else { // Benutzer ist noch nicht mit SC verbunden und befindet sich auch nicht im Autorisierungs-Vorgang
			
				// Display Soundcloud connect-button
				$return  = "<a href=\"$sc_connectUrl\" target=\"_self\">";
				$return .= "<img src=\"$connectImageUrl\">";
				$return .= '</a>';
				$return .= '</br>';
				$return .= 'Du bist noch nicht mit Soundcloud verbunden.</br>';
			}
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
		
		return '';
	}
	
	private function saveSoundcloudUser( $sc_userId ) {
		$table_name = 'wcf' . WCF_N . '_user_soundcloud_connect';
		$sqlQuery  = 'UPDATE ' . $table_name . ' ';
		$sqlQuery .= 'SET `soundcloudID` = \'' . $sc_userId . '\' ';
		$sqlQuery .= 'WHERE `' . $table_name .'`.`userID` = ' . intval(WCF::getUser()->userID); 
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
	
	private function saveSoundcloudAccessToken( $sc_token ) {
		// DEBUG
		// die( "Soundcloud access token: ${sc_token['access_token']}, expires ${sc_token['scope']} " );
		$sqlQuery = 'REPLACE INTO	wcf' . WCF_N . '_user_soundcloud_connect
				(userID, accessToken)
					VALUES (' . intval(WCF::getUser()->userID) . ", '" . $sc_token['access_token'] . "')";
		// DEBUG
		// die( "In saveSoundcloudAccessToken(): $sqlQuery" );
		WCF::getDB()->sendQuery($sqlQuery);		
	}
	
	private function fetchSoundcloudAccessToken( $wcf_userID ){
		$sqlQuery  = 'SELECT accessToken ';
		$sqlQuery .= 'FROM wcf' . WCF_N . '_user_soundcloud_connect ';
		$sqlQuery .= 'WHERE userID = ' . intval( $wcf_userID );
		$row = WCF::getDB()->getFirstRow( $sqlQuery );
		
		return $row['accessToken'];
	}
	
	private function deleteSoundcloudUser( $wcf_userID ) {
		$sqlQuery  = 'DELETE FROM wcf' . WCF_N . '_user_soundcloud_connect ';
		$sqlQuery .= 'WHERE userID = ' . intval( $wcf_userID );
		WCF::getDB()->sendQuery( $sqlQuery );
	}
}
?>