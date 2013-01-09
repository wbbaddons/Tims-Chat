<?php
namespace wcf\data\chat\suspension;
use \wcf\system\WCF;

/**
 * Provides functions to edit chat suspensions.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	data.chat.suspension
 */
class ChatSuspensionEditor extends \wcf\data\DatabaseObjectEditor implements \wcf\data\IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = '\wcf\data\chat\suspension\ChatSuspension';
	
	/**
	 * Clears the suspension cache.
	 */
	public static function resetCache() {
		$ush = \wcf\system\user\storage\UserStorageHandler::getInstance();
		
		$ush->resetAll('suspensions');
	}
}
