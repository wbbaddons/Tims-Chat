<?php
namespace chat\system\option;

/**
 * TimeIntervalOptionType is an implementation of IOptionType for time intervals.
 *
 * @author	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.option
 */
class TimeIntervalOptionType extends \wcf\system\option\TextOptionType {
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(\wcf\data\option\Option $option, $newValue) {
		return \chat\util\ChatUtil::timeModifier($newValue);
	}
	
	/**
	 * @see	\wcf\system\option\TextOptionType::getFormElement()
	 */
	public function getFormElement(\wcf\data\option\Option $option, $value) {
		$tmp = '';
		if ($value > 86400) {
			$tmp = floor($value / 86400).'d';
			$value -= floor($value / 86400) * 86400;
		}
		if ($value > 3600) {
			$tmp .= floor($value / 3600).'h';
			$value -= floor($value / 3600) * 3600;
		}
		$tmp .= floor($value / 60);
		if ($value % 60 != 0) {
			$tmp .= ','.($value % 60).'s';
		}
		
		return parent::getFormElement($option, $tmp);
	}
}
