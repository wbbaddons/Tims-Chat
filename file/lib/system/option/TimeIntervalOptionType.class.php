<?php
namespace wcf\system\option;

/**
 * TimeIntervalOptionType is an implementation of IOptionType for time intervals.
 *
 * @author	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.option
 */
class TimeIntervalOptionType extends TextOptionType {
	/**
	 * @see wcf\system\option\IOptionType::getData()
	 */
	public function getData(\wcf\data\option\Option $option, $newValue) {
		return \wcf\util\ChatUtil::timeModifier($newValue);
	}
	
	/**
	 * @see wcf\system\option\TextOptionType::getFormElement()
	 */
	public function getFormElement(\wcf\data\option\Option $option, $value) {
		$tmp = (int) ($value / 60);
		if ($value % 60 != 0) {
			$tmp .= ','.($value % 60).'s';
		}
		return parent::getFormElement($option, $tmp);
	}
}
