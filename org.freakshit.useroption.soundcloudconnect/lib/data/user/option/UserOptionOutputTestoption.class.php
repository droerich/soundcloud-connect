<?php

require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * @author  Deve <deve@webbeatz.de>
 * @package org.deve.useroption.testoption
 * @license CC BY-SA <http://creativecommons.org/licenses/by-sa/3.0/>
 */
class UserOptionOutputTestoption implements UserOptionOutput {

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
		
				

			// create div for styling
			$return = '<div id="Testoption">';
			
			$return .= "Der Benutzer hat <i>$value</i> eingegeben. </br>";
			$return .= "<tt>\$optionData</tt> hat " . count($optionData, 1) . " Elemente:</br>";
			foreach ( $optionData as $x ) {
			  $return .= "<tt>$x</tt></br>";
			}

			// finish it
			return $return.'</div>';
		}
	}

?>