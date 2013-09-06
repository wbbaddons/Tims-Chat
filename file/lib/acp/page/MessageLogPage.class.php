<?php
namespace chat\acp\page;
use \wcf\system\WCF;

/**
 * Lists chat log.
 * 
 * @author 	Maximilian Mader
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	acp.page
 */
class MessageLogPage extends \wcf\page\AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'chat.acp.menu.link.log';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array(
		'admin.char.canReadLog'
	);
	
	/**
	 * name of error field
	 * @var	string
	 */
	public $errorField = '';
	
	/**
	 * error type
	 * @var	string
	 */
	public $errorType = '';
	
	/**
	 * messages for the given day
	 * 
	 * @var	array<\chat\data\message\Message>
	 */
	public $messages = array();
	
	/**
	 * given roomID
	 * 
	 * @var	integer
	 */
	public $roomID = 0;
	
	/**
	 * given date
	 * 
	 * @var integer
	 */
	public $date = 0;
	
	/**
	 * active room
	 * 
	 * @var	\chat\data\room\Room
	 */
	public $room = null;
	
	/**
	 * available rooms
	 *  
	 * @var	array<\chat\data\room\Room>
	 */
	public $rooms = array();
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		try {
			if ($this->date > TIME_NOW) {
				throw new \wcf\system\exception\UserInputException('date', 'inFuture');
			}
		}
		catch (\wcf\system\exception\UserInputException $e) {
			$this->errorField = $e->getField();
			$this->errorType = $e->getType();
			
			return;
		}
		
		try {
			if ($this->date < strtotime('today 00:00:00 -'.ceil(CHAT_LOG_ARCHIVETIME / 1440).'day')) {
				throw new \wcf\system\exception\UserInputException('date', 'tooLongAgo');
			}
		}
		catch (\wcf\system\exception\UserInputException $e) {
			$this->errorField = $e->getField();
			$this->errorType = $e->getType();
			
			return;
		}
		
		$messages = \chat\data\message\ViewableMessageList::getMessagesBetween($this->room, $this->date, $this->date + 1799);
		
		foreach ($messages as $message) {
			$hour = ((int) date('H', $message->time)) * 2;
			$minutes = (int) date('i', $message->time);
			
			if ($minutes >= 30) {
				$hour += 1;
			}
			
			$this->messages[$hour][] = $message;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->rooms = \chat\data\room\RoomCache::getInstance()->getRooms();
		
		foreach ($this->rooms as $id => $room) {
			if (!$room->permanent)
					unset($this->rooms[$key]);
		}
		
		if (isset($_REQUEST['id'])) $this->roomID = intval($_REQUEST['id']);
		else $this->roomID = reset($this->rooms)->roomID;
		
		$this->room = \chat\data\room\RoomCache::getInstance()->getRoom($this->roomID);
		
		if (!$this->room) throw new \wcf\system\exception\IllegalLinkException();

		if (isset($_REQUEST['date'])) $date = $_REQUEST['date'].' 00:00:00';
		else $date = 'today 00:00:00';
		$this->date = @strtotime($date);
		if ($this->date === false) throw new \wcf\system\exception\IllegalLinkException();
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'messages' => $this->messages,
			'rooms' => $this->rooms,
			'room' => $this->room,
			'date' => $this->date,
			'errorField' => $this->errorField,
			'errorType' => $this->errorType
		));
	}
}
