<?php
/**
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 */

require_once(__DIR__.'/global.php');
\wcf\system\request\RequestHandler::getInstance()->handle('chat');
