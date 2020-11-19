<?php
/*
 * Copyright (c) 2010-2020 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\command;

use \chat\data\message\MessageAction;
use \chat\data\message\MessageEditor;
use \chat\data\room\Room;
use \wcf\data\user\UserProfile;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;

/**
 * The whisper command creates a private message
 * between two chat users.
 */
class WhisperCommand extends AbstractInputProcessedCommand implements ICommand {
	use TNeedsUser;

	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/Command/Whisper';
	}

	/**
	 * @inheritDoc
	 */
	public function validate($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		$recipient = new UserProfile($this->assertUser($this->assertParameter($parameters, 'username')));
		if ($recipient->isIgnoredUser($user->userID)) throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('chat.error.userIgnoresYou', [ 'user' => $recipient ]));

		$this->setText($this->assertParameter($parameters, 'text'));
		$this->validateText();
	}

	/**
	 * @inheritDoc
	 */
	public function execute($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		$objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.whisper');
		$recipient = $this->assertUser($this->assertParameter($parameters, 'username'));
		$this->setText($this->assertParameter($parameters, 'text'));

		WCF::getDB()->beginTransaction();
		$message = (new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
		                                                         , 'userID'       => $user->userID
		                                                         , 'username'     => $user->username
		                                                         , 'time'         => TIME_NOW
		                                                         , 'objectTypeID' => $objectTypeID
		                                                         , 'payload'      => serialize([ 'message'       => $this->processor->getHtml()
		                                                                                       , 'recipient'     => $recipient->userID
		                                                                                       , 'recipientName' => $recipient->username
		                                                                                       ])
		                                                         ]
		                                             , 'updateTimestamp' => true
		                                             ]
		                             )
		           )->executeAction()['returnValues'];

		$this->processor->setObjectID($message->messageID);
		if (\wcf\system\message\embedded\object\MessageEmbeddedObjectManager::getInstance()->registerObjects($this->processor)) {
			(new MessageEditor($message))->update([
				'hasEmbeddedObjects' => 1
			]);
		}
		WCF::getDB()->commitTransaction();
	}
}
