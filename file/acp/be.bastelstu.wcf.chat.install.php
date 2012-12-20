<?php
namespace be\bastelstu\wcf\chat;

/**
 * Handles installation of Tims Chat.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 */
// @codingStandardsIgnoreFile
final class Install {
	/**
	 * Contains all the styles the current installation has.
	 * 
	 * @var array<\wcf\data\style\Style>
	 */
	private $styles = null;
	
	public function __construct() {
		$this->styles = \wcf\system\style\StyleHandler::getInstance()->getAvailableStyles();
	}
	
	/**
	 * Resets styles.
	 */
	public function execute() {
		foreach ($this->styles as $style) {
			\wcf\system\style\StyleHandler::getInstance()->resetStylesheet($style);
		}
	}
}
$install = new Install();
$install->execute();
