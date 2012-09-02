<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

class SoundcloudConnectListener implements EventListener {

	public function execute($eventObj, $className, $eventName) {
		//$scUserId = $eventObj->getUserId();
		echo "Received user ID $scUserId </br>";
		return;
	}
}
?>