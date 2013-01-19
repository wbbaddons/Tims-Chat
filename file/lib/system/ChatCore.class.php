<?php
namespace wbb\system;

/**
 * Chat core
 *
 * @author	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system
 */
class ChatCore extends \wcf\system\application\AbstractApplication {
	/**
	 * @see	wcf\system\application\AbstractApplication::$abbreviation
	 */
	protected $abbreviation = 'chat';
	
	/**
	 * @see	wcf\system\application\IApplication::__run()
	 */
	public function __run() {}
}
