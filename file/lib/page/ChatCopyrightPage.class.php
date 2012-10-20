<?php
namespace wcf\page;
use \wcf\system\exception\IllegalLinkException;
use \wcf\system\WCF;

/**
 * Shows information about Tims chat.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	page
 */
class ChatCopyrightPage extends AbstractPage {
	/**
	 * @see \wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('CHAT_ACTIVE');
	
	/**
	 * @see \wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array();
	
	/**
	 * shortcut for the active request
	 * @see wcf\system\request\Request::getRequestObject()
	 */
	public $request = null;
	
	/**
	 * Disallows direct access.
	 * 
	 * @see wcf\page\IPage::__run()
	 */
	public function __run() {
		if (($this->request = \wcf\system\request\RequestHandler::getInstance()->getActiveRequest()->getRequestObject()) === $this) throw new IllegalLinkException();
		
		parent::__run();
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		//     ###
		//   ##   ##
		//  #       #
		//  # ##### #
		// # #     # #
		// # # * * # #
		// # #     # #
		// #  #   #  #
		//  #  ###  #
		//  #       #
		//   #######
		//   # # # #
		//   # # # #
		//   # # # #
		
		if (isset($_GET['sheep'])) $this->useTemplate = false;
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		// guests are not supported
		if (!WCF::getUser()->userID) {
			throw new \wcf\system\exception\PermissionDeniedException();
		}
		
		parent::show();
		if ($this->useTemplate) exit;
		@header('Content-type: image/png');
		\wcf\util\HeaderUtil::sendNoCacheHeaders();
		$images = explode("\n\n", file_get_contents(__FILE__, null, null, __COMPILER_HALT_OFFSET__+2));
		echo base64_decode($images[array_rand($images)]);
		exit;
	}
}
__halt_compiler();/*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
TnGiAAAAAElFTkSuQmCC
