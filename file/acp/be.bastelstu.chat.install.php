<?php
namespace be\bastelstu\chat;

/**
 * Handles installation of Tims Chat.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 */
// @codingStandardsIgnoreFile
final class Install {
	/**
	 * Contains all the styles the current installation has.
	 * 
	 * @var array<\wcf\data\style\Style>
	 */
	private $styles = null;
	
	/**
	 * Do we need to update the page title?
	 * 
	 * @var boolean
	 */
	private $updateTitle = false;
	
	public function __construct() {
		$this->styles = \wcf\system\style\StyleHandler::getInstance()->getAvailableStyles();
		if (!defined('PAGE_TITLE') || !PAGE_TITLE) $this->updateTitle = true;
	}
	
	/**
	 * Resets styles.
	 */
	public function execute() {
		foreach ($this->styles as $style) {
			\wcf\system\style\StyleHandler::getInstance()->resetStylesheet($style);
		}
		
		if ($this->updateTitle) {
				$sql = "UPDATE
						wcf".WCF_N."_option
					SET
						optionValue = ?
					WHERE
						optionName = ?";
				$stmt = \wcf\system\WCF::getDB()->prepareStatement($sql);
				$stmt->execute(array('Tims Chat 3', 'page_title'));
				\wcf\data\option\OptionEditor::resetCache();
		}
		
		\wcf\system\dashboard\DashboardHandler::setDefaultValues('com.woltlab.wcf.user.DashboardPage', array(
			// content
			'be.bastelstu.chat.onlineList' => 1
		));
	}
}

$install = new Install();
$install->execute();
