<?php
// wcf imports
require_once (WCF_DIR.'lib/system/event/EventHandler.class.php');

class SoundcloudConnect{

	protected $userId = null;

	// authorize with soundcloud etc.
	public function authorize() {
		// ..received user ID
		$userId = 32457; // example
		
		$classname = 'SoundcloudConnect';
		echo "$classname: firing Action...</br>";
		// Authorization successfull, fire Event
		EventHandler::firecAction($this, 'connect');
		echo "$classname: Finished.</br>";
		return;
	}
	
	// return Soundcloud user ID
	public function getUserId() {
		return $userId;
	}

}
?>