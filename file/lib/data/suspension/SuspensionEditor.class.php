<?php
namespace chat\data\suspension;
use \wcf\system\WCF;

/**
 * Provides functions to edit chat suspensions.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.chat.suspension
 */
class SuspensionEditor extends \wcf\data\DatabaseObjectEditor implements \wcf\data\IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = '\chat\data\suspension\Suspension';
	
	/**
	 * Clears the suspension cache.
	 */
	public static function resetCache() {
		$ush = \wcf\system\user\storage\UserStorageHandler::getInstance();
		
		$ush->resetAll('chatSuspensions');
	}
}
