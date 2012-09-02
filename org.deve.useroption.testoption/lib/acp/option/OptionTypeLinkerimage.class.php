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
		
		// Simulate event
		$classname = 'OptionTypeLinkerimage';
		$connector = new SoundcloudConnect();
		echo "$classname: Start authorization...</br>";		
		$connector->authorize();
		echo "$classname: Finished.</br>";
		
		
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