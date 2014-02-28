<?php
namespace chat\data\suspension;

/**
 * Represents a list of chat suspensions.
 * 
 * @author 	Maximilian Mader
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.suspension
 */
class SuspensionList extends \wcf\data\DatabaseObjectList {
	/**
	 * @see wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'chat\data\suspension\Suspension';
}
