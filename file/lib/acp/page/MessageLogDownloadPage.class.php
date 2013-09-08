<?php
namespace chat\acp\page;
use \wcf\system\WCF;

/**
 * Handles text/plain download of chat log.
 * 
 * @author 	Maximilian Mader
 * @copyright	2010-2013 Tim Düsterhus
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
	
	public $tmpFile = '';
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if ($this->date > TIME_NOW) {
			throw new \wcf\system\exception\IllegalLinkException();
		}
		
		if (CHAT_LOG_ARCHIVETIME !== -1 && $this->date < strtotime('today 00:00:00 -'.ceil(CHAT_LOG_ARCHIVETIME / 1440).'day')) {
			throw new \wcf\system\exception\IllegalLinkException();
		}
		
		$this->tmpFile = \wcf\util\FileUtil::getTemporaryFilename();
		touch($this->tmpFile);
		\wcf\util\FileUtil::makeWritable($this->tmpFile);
		
		if (is_writable($this->tmpFile)) {
			$file = new \wcf\system\io\File($this->tmpFile);
			$file->write(WCF::getLanguage()->get('chat.acp.log.title') . ': ' . (string) $this->room . "\n");
			
			for ($start = $this->date, $end = $start + 86399; $start < $end; $start += 1800) {
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
		
		$this->date = @strtotime($date);
		if ($this->date === false) throw new \wcf\system\exception\IllegalLinkException();
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		parent::show();
		
		$dateTime = \wcf\util\DateUtil::getDateTimeByTimestamp($this->date);
		
		$fileReader = new \wcf\util\FileReader($this->tmpFile, array('mimeType' => 'text/plain', 'filename' => str_replace(' ', '-', WCF::getLanguage()->get('chat.acp.log.title') . ' ' . $this->room.'-'.\wcf\util\DateUtil::format($dateTime, 'Y-m-d').'.txt')));
		$fileReader->send();
		
		exit;
	}
}
