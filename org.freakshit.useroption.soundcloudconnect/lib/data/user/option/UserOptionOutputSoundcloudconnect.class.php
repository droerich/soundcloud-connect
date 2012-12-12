<?php

require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * Returns HTML code for Soundcloud player widget of the given user
 *
 * @author  Deve <deve@webbeatz.de>
 * @package org.deve.useroption.testoption
 * 
 */
class UserOptionOutputSoundcloudconnect implements UserOptionOutput {

	/**
	 * @see UserOptionOutput::getShortOutput()
	 */
	public function getShortOutput(User $user, $optionData, $value) {
		return $this->getOutput($user, $optionData, $value);
	}

	/**
	 * @see UserOptionOutput::getMediumOutput()
	 */
	public function getMediumOutput(User $user, $optionData, $value) {
		return $this->getOutput($user, $optionData, $value);
	}

	/**
	 * @see UserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, $optionData, $value) {
	
		$return = '';
		$sc_userId = $this->fetchSoundcloudId( $user->userID );
		if ( !empty($sc_userId) ){
			$return .= '<iframe width="100%" height="450" scrolling="no" frameborder="no" ';
			$return .= 'src="http://w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Fusers%2F' . $sc_userId;
			$return .= '&show_artwork=true"></iframe>';
		} else {
			$return .= 'Der Benutzer hat noch kein Soundcloud-Profil angegeben.';
		}
		return $return;
	}
	
	private function fetchSoundcloudId( $wcf_userID ){
		$sqlQuery  = 'SELECT soundcloudID ';
		$sqlQuery .= 'FROM wcf' . WCF_N . '_user_soundcloud_connect ';
		$sqlQuery .= 'WHERE userID = ' . intval( $wcf_userID );
		$row = WCF::getDB()->getFirstRow( $sqlQuery );
		
		return $row['soundcloudID'];
	}
}

?>