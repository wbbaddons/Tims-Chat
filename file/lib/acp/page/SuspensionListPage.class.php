<?php
namespace chat\acp\page;
use \wcf\system\WCF;

/**
 * Lists chat suspensions.
 * 
 * @author 	Maximilian Mader
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	acp.page
 */
class SuspensionListPage extends \wcf\page\SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'chat.acp.menu.link.suspension.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array();
	
	/**
	* @see wcf\page\SortablePage::$defaultSortField
	*/
	public $defaultSortField = 'username';
	
	/**
	* @see wcf\page\MultipleLinkPage::$itemsPerPage
	*/
	public $itemsPerPage = 50;
	
	/**
	* @see wcf\page\SortablePage::$validSortFields
	*/
	public $validSortFields = array('suspensionID', 'userID', 'username', 'roomID', 'type', 'expires');
	
	/**
	* @see wcf\page\MultipleLinkPage::$objectListClassName
	*/
	public $objectListClassName = 'chat\data\suspension\SuspensionList';
	
	/**
	 * @see wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects .= "user.username";
		$this->objectList->sqlJoins .= "LEFT JOIN
						wcf".WCF_N."_user AS user
						ON suspension.userID = user.userID";
	}
}
