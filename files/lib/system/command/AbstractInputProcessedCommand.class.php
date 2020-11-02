<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-02
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\command;

use \wcf\system\exception\UserInputException;
use \wcf\system\bbcode\BBCodeHandler;
use \wcf\system\message\censorship\Censorship;
use \wcf\system\WCF;

/**
 * Represents a command that processes the input using HtmlInputProcessor.
 */
abstract class AbstractInputProcessedCommand extends AbstractCommand {
	/**
	 * HtmlInputProcessor to use.
	 * @var	\wcf\system\html\input\HtmlInputProcessor
	 */
	protected $processor = null;

	/**
	 * The text processed last.
	 * @var	string
	 */
	private $text = null;

	public function __construct(\wcf\data\DatabaseObject $object) {
		parent::__construct($object);

		$this->processor = new \wcf\system\html\input\HtmlInputProcessor();
		$this->setDisallowedBBCodes();
	}

	private function setDisallowedBBCodes() {
		BBCodeHandler::getInstance()->setDisallowedBBCodes(explode(',', WCF::getSession()->getPermission('user.chat.disallowedBBCodes')));
	}

	public function setText($text) {
		if ($this->text === $text) return;

		$this->text = $text;
		$this->setDisallowedBBCodes();
		$this->processor->process($text, 'be.bastelstu.chat.message', 0);
	}

	public function validateText() {
		if ($this->processor->appearsToBeEmpty()) {
			throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.global.form.error.empty'));
		}

		$message = $this->processor->getTextContent();

		// validate message length
		if (mb_strlen($message) > CHAT_MAX_LENGTH) {
			throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.message.error.tooLong', [ 'maxTextLength' => CHAT_MAX_LENGTH ]));
		}

		// search for disallowed bbcodes
		$this->setDisallowedBBCodes();
		$disallowedBBCodes = $this->processor->validate();
		if (!empty($disallowedBBCodes)) {
			throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.message.error.disallowedBBCodes', [ 'disallowedBBCodes' => $disallowedBBCodes ]));
		}

		// search for censored words
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($message);
			if ($result) {
				throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.message.error.censoredWordsFound', [ 'censoredWords' => $result ]));
			}
		}
	}
}
