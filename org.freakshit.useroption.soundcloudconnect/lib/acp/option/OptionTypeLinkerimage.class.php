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

		// Build Souncdloud disconnect URL. It needs the 'www'
		$pageUrl = parse_url(PAGE_URL);
		$sc_disconnectUrl = $pageUrl["scheme"] . '://www.' . $pageUrl["host"] . $pageUrl["path"] . '/index.php?form=UserProfileEdit&state=soundcloudDisconnect';
		
		if (isset($_GET['state']) && $_GET['state'] == 'soundcloudConnect') { // Aufruf kommt von Soundcloud (Redirect URI) -> Autorisierungsvorgang			
			
			// exchange authorization code for access token
			$sc_code = $_GET['code'];
			$sc_token = $sc_client->accessToken($sc_code);			
				
			// make an authenticated call to the Soundcloud API
			try {
				$sc_response = json_decode($sc_client->get('me'), true);
			} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
				WCF::getTPL()->assign(array(
					'status' => 'error_soundcloud',
					'connectUrl' => $sc_connectUrl,
					'error_message' => $e->getMessage
				));
				return WCF::getTPL()->fetch('optionTypeLinkerImage');
			}
			
			// Get Soundlcoud user ID
			$sc_userId = $sc_response['id'];
			
			if( !($this->doesSoundcloudIdExist($sc_userId)) ){ // Souncdloud ID doesn't exist in database yet
			
				// Store auth token in database
				$this->saveSoundcloudAccessToken( $sc_token );
				
				
				// Save the soundcloud data to the database
				$this->saveSoundcloudUser( $sc_userId );
				
				WCF::getTPL()->assign(array(
					'status' => 'has_connected',
					'disconnectUrl' => $sc_disconnectUrl,
				));
			} else { // Soundcloud ID already exists in database -> Refuse Soundcloud connection
				WCF::getTPL()->assign(array(
					'status' => 'error_account_exists',
					'connectUrl' => $sc_connectUrl
				));
			}
		
		} elseif ( isset($_GET['state']) && $_GET['state'] == 'soundcloudDisconnect' ) { // Aufruf kommt von dieser Seite -> Soundlcoud-Daten löschen
			if ( !$this->isUserConnected( WCF::getUser()->userID ) ) { // Benutzer ist noch nicht mit SC verbunden
				WCF::getTPL()->assign(array(
					'status' => 'error_account_does_not_exist',
					'connectUrl' => $sc_connectUrl
				));
				return WCF::getTPL()->fetch('optionTypeLinkerImage');
			}
			$this->deleteSoundcloudUser( WCF::getUser()->userID );
			
			WCF::getTPL()->assign(array(
				'status' => 'has_disconnected',
				'connectUrl' => $sc_connectUrl,
			));
		} else { // Nicht im Autorisierungs-Vorgang, nicht im Lösch-Vorgang
			if ( $this->isUserConnected( WCF::getUser()->userID ) ) { // Benutzer is bereits mit SC verbunden
				// Get Soundcloud username from Soundcloud				
				$sc_token = $this->fetchSoundcloudAccessToken( intval(WCF::getUser()->userID) );
				$sc_client->setAccessToken( $sc_token );
				try {
					$sc_response = json_decode($sc_client->get('me'), true);
				} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
// 					$return = "Soundcloud-Fehler: " . $e->getMessage() . '</br>';
// 					return $return;
					WCF::getTPL()->assign(array(
						'status' => 'error_soundcloud',
						'disconnectUrl' => $sc_disconnectUrl,
						'error_message' => $e->getMessage()
					));
					return WCF::getTPL()->fetch('optionTypeLinkerImage');
				}
				$sc_username = $sc_response['username'];
				
				WCF::getTPL()->assign(array(
					'status' => 'is_connected',
					'disconnectUrl' => $sc_disconnectUrl,
					'sc_username' => $sc_username
				));
			} else { // Benutzer ist noch nicht mit SC verbunden und befindet sich auch nicht im Autorisierungs-Vorgang			
				
				WCF::getTPL()->assign(array(
					'status' => 'is_not_connected',
					'connectUrl' => $sc_connectUrl,
				));
			}
		}
		return WCF::getTPL()->fetch('optionTypeLinkerImage');
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
	
	// Returns true, if an Soundcloud ID is associated to the current WCF user
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
	
	// Returns true if the given Soundcloud ID is already in the database
	private function doesSoundcloudIdExist( $sc_userId ) {
		$sqlQuery  = 'SELECT `userID` FROM `wcf' . WCF_N . '_user_soundcloud_connect` ';
		$sqlQuery .= 'WHERE `soundcloudID` = ' . intval($sc_userId);
		$queryResource = WCF::getDB()->sendQuery( $sqlQuery );
		return ( WCF::getDB()->countRows( $queryResource ) > 0 );
	}
}
?>