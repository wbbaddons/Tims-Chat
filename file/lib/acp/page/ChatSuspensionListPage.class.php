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
class ChatSuspensionListPage extends \wcf\page\SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'chat.acp.menu.link.suspension.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	// TODO: Permissions
	public $neededPermissions = array();
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'expires';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 30;
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('suspensionID', 'userID', 'username', 'roomID', 'type', 'expires');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'chat\data\suspension\SuspensionList';
	
	/**
	 * type filter
	 * 
	 * @var	integer
	 */
	public $filterType = null;
	
	/**
	 * user filter
	 * 
	 * @var	integer
	 */
	public $filterUserID = null;
	
	/**
	 * room filter
	 * 
	 * @var	integer
	 */
	public $filterRoomID = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['type'])) $this->filterType = intval($_REQUEST['type']);
		if (isset($_REQUEST['userID'])) $this->filterUserID = intval($_REQUEST['userID']);
		if (isset($_REQUEST['roomID'])) $this->filterRoomID = intval($_REQUEST['roomID']);
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects .= "user_table.username, room_table.title AS roomTitle";
		$this->objectList->sqlJoins .= "
						LEFT JOIN	wcf".WCF_N."_user user_table
						ON		suspension.userID = user_table.userID";
		$conditionJoins = "	LEFT JOIN	chat".WCF_N."_room room_table
					ON		suspension.roomID = room_table.roomID";
		$this->objectList->sqlConditionJoins .= $conditionJoins;
		$this->objectList->sqlJoins .= $conditionJoins;
		
		$this->objectList->getConditionBuilder()->add('expires >= ?', array(TIME_NOW));
		$this->objectList->getConditionBuilder()->add('room_table.permanent = ?', array(1));
		if ($this->filterType !== null) $this->objectList->getConditionBuilder()->add('suspension.type = ?', array($this->filterType));
		if ($this->filterUserID !== null) $this->objectList->getConditionBuilder()->add('suspension.userID = ?', array($this->filterUserID));
		if ($this->filterRoomID !== null) {
			if ($this->filterRoomID === 0) {
				$this->objectList->getConditionBuilder()->add('suspension.roomID IS NULL', array());
			}
			else {
				$this->objectList->getConditionBuilder()->add('suspension.roomID = ?', array($this->filterRoomID));
			}
		}
	}
}
