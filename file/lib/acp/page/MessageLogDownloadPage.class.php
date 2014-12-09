<?php
namespace chat\acp\page;
use \wcf\system\WCF;

/**
 * Handles text/plain download of chat log.
 * 
 * @author 	Maximilian Mader
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	acp.page
 */
class MessageLogDownloadPage extends \wcf\page\AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'chat.acp.menu.link.log';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array(
		'admin.chat.canReadLog'
	);
	
	public $useTemplate = false;
	
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
	 * @var \DateTime
	 */
	public $date = null;
	
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
	
	public $tmpFile = '';
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$now = new \DateTime('now', WCF::getUser()->getTimeZone());
		if ($this->date->getTimestamp() > $now->getTimestamp()) {
			throw new \wcf\system\exception\IllegalLinkException();
		}
		
		$oldest = new \DateTime('today -'.ceil(CHAT_LOG_ARCHIVETIME / 1440).'day', WCF::getUser()->getTimeZone());
		if (CHAT_LOG_ARCHIVETIME !== -1 && $this->date->getTimestamp() < $oldest->getTimestamp()) {
			throw new \wcf\system\exception\IllegalLinkException();
		}
		
		$this->tmpFile = \wcf\util\FileUtil::getTemporaryFilename();
		touch($this->tmpFile);
		\wcf\util\FileUtil::makeWritable($this->tmpFile);
		
		if (is_writable($this->tmpFile)) {
			$file = new \wcf\system\io\File($this->tmpFile);
			$file->write(WCF::getLanguage()->get('chat.acp.log.title') . ': ' . (string) $this->room . "\n");
			
			for ($start = $this->date->getTimestamp(), $end = $this->date->add(\DateInterval::createFromDateString('1day'))->sub(\DateInterval::createFromDateString('1second'))->getTimestamp(); $start < $end; $start += 1800) {
				$file->write(WCF::getTpl()->fetch('messageLogDownload', 'chat', array('messages' => \chat\data\message\MessageList::getMessagesBetween($this->room, $start, $start + 1799))));
			}
			
			$file->close();
		}
		else {
			throw new \wcf\system\exception\IllegalLinkException();
		}
		
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) {
			$this->roomID = intval($_REQUEST['id']);
		}
		else {
			throw new \wcf\system\exception\IllegalLinkException();
		}
		
		$this->room = \chat\data\room\RoomCache::getInstance()->getRoom($this->roomID);
		if (!$this->room) throw new \wcf\system\exception\IllegalLinkException();
		if (!$this->room->permanent) throw new \wcf\system\exception\PermissionDeniedException();
		
		if (isset($_REQUEST['date'])) $date = $_REQUEST['date'].' 00:00:00';
		else $date = 'today 00:00:00';
		
		try {
			$this->date = new \DateTime($date, WCF::getUser()->getTimeZone());
		}
		catch (\Exception $e) {
			throw new \wcf\system\exception\IllegalLinkException();
		}
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		parent::show();
		
		$fileReader = new \wcf\util\FileReader($this->tmpFile, array('mimeType' => 'text/plain', 'filename' => str_replace(' ', '-', WCF::getLanguage()->get('chat.acp.log.title') . ' ' . $this->room.'-'.\wcf\util\DateUtil::format($this->date, 'Y-m-d').'.txt')));
		$fileReader->send();
		
		exit;
	}
}
