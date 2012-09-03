<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionType.class.php');
require_once(WCF_DIR.'lib/data/soundcloud/SoundcloudConnect.class.php');

// Souncloud API
require_once 'Services/Soundcloud.php';

// Define constants for Souncloud API registration
define('CLIENT_ID', 'd3b9d13d8379b6c19044e4ec6a321102');
define('CLIENT_SECRET', '9c3b63959977bf61404110fc603427f2');
define('REDIRECT_URL', 'http://www.meniunddeve.de/sclapi/callback.php');

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
		
		// URL to SC-connect-image
		$buttonImageUrl = 'http://connect.soundcloud.com/2/btn-connect-l.png';
		
		// Get SC authorize URL
		$sc_authorizeUrl = $sc_client->getAuthorizeUrl();
		
		$return = 'Übrigens ist der Wert von WCF_DIR ' . WCF_DIR . '</br>';
		$return .= "<a href=\"$sc_authorizeUrl\" target=\"_blank\">";
		$return .= "<img src=\"$buttonImageUrl\">";
		$return .= '</a>';
		$return .= '</br>';
		if (isset($_REQUEST['action'])) {
			$return .= 'Habe action ' . $_REQUEST['action'] . ' empfangen.</br>';
			if( $_REQUEST['action'] == 'soundcloudConnect' ) {
				// exchange authorization code for access token
				$sc_code = $_GET['code'];
				$sc_token = $sc_client->accessToken($sc_code);
		
				// make an authenticated call
				try {
					$sc_response = json_decode($sc_client->get('me'), true);
				} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
					exit($e->getMessage());
				}
				// Save Soundcloud user ID
				$sc_userId = $sc_response['id'];		
				$return . "Deine Soundcloud User-ID lautet $sc_userId</br>";
			} else {
				$return . "Action " . $_REQUEST['action'] . " ist unbekannt.</br>";
			}
		} else {
			$return .= 'Habe keine action empfangen.</br>';
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
		//return $newValue;
		return "Hallo Horst </br>";
	}
}
?>