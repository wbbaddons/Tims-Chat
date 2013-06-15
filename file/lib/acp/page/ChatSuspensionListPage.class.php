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
	public $validSortFields = array('suspensionID', 'userID', 'username', 'roomID', 'type', 'expires', 'issuer', 'time');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'chat\data\suspension\SuspensionList';
	
	/**
	 * type filter
	 * 
	 * @var	integer
	 */
	public $filterSuspensionType = null;
	
	/**
	 * user filter
	 * 
	 * @var	integer
	 */
	public $filterUserID = null;
	
	/*
	 * username
	 *
	 * @var String
	 */
	public $filterUsername = null;
	
	/**
	 * issuer filter
	 * 
	 * @var	integer
	 */
	public $filterIssuerUserID = null;
	
	/*
	 * issuer username
	 *
	 * @var String
	 */
	public $filterIssuerUsername = null;
	
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
		
		if (isset($_REQUEST['username']) && !empty($_REQUEST['username'])) $this->filterUsername = \wcf\util\StringUtil::trim($_REQUEST['username']);
		if (isset($_REQUEST['issuerUsername']) && !empty($_REQUEST['issuerUsername'])) $this->filterIssuerUsername = \wcf\util\StringUtil::trim($_REQUEST['issuerUsername']);
		
		if ($this->filterUsername != null) $this->filterUserID = \wcf\data\user\UserProfile::getUserProfileByUsername($this->filterUsername)->userID;
		if ($this->filterIssuerUsername != null) $this->filterIssuerUserID = \wcf\data\user\UserProfile::getUserProfileByUsername($this->filterIssuerUsername)->userID;
		
		if ($this->filterUserID === null && isset($_REQUEST['userID']) 	&& !empty($_REQUEST['userID'])) $this->filterUserID = 	intval($_REQUEST['userID']);
		if ($this->filterIssuerUserID === null && isset($_REQUEST['issuerUserID']) && !empty($_REQUEST['issuerUserID'])) $this->filterIssuerUserID = intval($_REQUEST['issuerUserID']);
		
		if (isset($_REQUEST['roomID']) && $_REQUEST['roomID'] != -1) $this->filterRoomID = intval($_REQUEST['roomID']);
		if (isset($_REQUEST['suspensionType']) && !empty($_REQUEST['suspensionType'])) $this->filterSuspensionType = intval($_REQUEST['suspensionType']);
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'availableRooms' => \chat\data\room\RoomCache::getInstance()->getRooms(),
			'filterRoomID' => ($this->filterRoomID !== null) ? $this->filterRoomID : -1,
			'filterUsername' => $this->filterUsername,
			'filterIssuerUsername' => $this->filterIssuerUsername,
			'filterSuspensionType' => $this->filterSuspensionType
		));
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects .= "user_table.username, user_table2.username as issuerUsername, room_table.title AS roomTitle";
		$this->objectList->sqlJoins .= "
						LEFT JOIN	wcf".WCF_N."_user user_table
						ON		suspension.userID = user_table.userID
						LEFT JOIN	wcf".WCF_N."_user user_table2
						ON		suspension.issuer = user_table2.userID";
		$conditionJoins = "	LEFT JOIN	chat".WCF_N."_room room_table
					ON		suspension.roomID = room_table.roomID";
		$this->objectList->sqlConditionJoins .= $conditionJoins;
		$this->objectList->sqlJoins .= $conditionJoins;
		
		$this->objectList->getConditionBuilder()->add('expires >= ?', array(TIME_NOW));
		$this->objectList->getConditionBuilder()->add('(room_table.permanent = ? OR suspension.roomID IS NULL)', array(1));
		if ($this->filterSuspensionType !== null) $this->objectList->getConditionBuilder()->add('suspension.type = ?', array($this->filterSuspensionType));
		if ($this->filterUserID !== null) $this->objectList->getConditionBuilder()->add('suspension.userID = ?', array($this->filterUserID));
		if ($this->filterIssuerUserID !== null) $this->objectList->getConditionBuilder()->add('suspension.issuer = ?', array($this->filterIssuerUserID));
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
