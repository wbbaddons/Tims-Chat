<?php
namespace chat\system;

/**
 * Chat core
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system
 */
class CHATCore extends \wcf\system\application\AbstractApplication {
	/**
	 * @see	wcf\system\application\AbstractApplication::$abbreviation
	 */
	protected $abbreviation = 'chat';
	
	/**
	 * @see	wcf\system\application\AbstractApplication::$primaryController
	 */
	protected $primaryController = 'chat\\page\\ChatPage';
}
