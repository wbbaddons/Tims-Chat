<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2027-02-22
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\message\type;

use chat\data\message\Message;
use chat\data\room\Room;
use wcf\data\user\UserProfile;
use wcf\system\event\EventHandler;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\WCF;

/**
 * WhisperMessageType represents a whispered message.
 */
final class WhisperMessageType implements IMessageType
{
    /**
     * HtmlOutputProcessor to use.
     * @var \wcf\system\html\output\HtmlOutputProcessor
     */
    protected $processor;

    public function __construct()
    {
        $this->processor = new HtmlOutputProcessor();
    }

    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName(): string
    {
        return 'Bastelstu.be/Chat/MessageType/Whisper';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(Message $message, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $payload = $message->payload;
        $payload['formattedMessage'] = null;
        $payload['plaintextMessage'] = null;

        $parameters = [
            'message' => $message,
            'user' => $user,
            'payload' => $payload,
        ];
        EventHandler::getInstance()->fireAction($this, 'getPayload', $parameters);

        if ($parameters['payload']['formattedMessage'] === null) {
            $this->processor->process(
                $parameters['payload']['message'],
                'be.bastelstu.chat.message',
                $message->messageID
            );
            $parameters['payload']['formattedMessage'] = $this->processor->getHtml();
        }

        if ($parameters['payload']['plaintextMessage'] === null) {
            $this->processor->setOutputType('text/plain');
            $this->processor->process(
                $parameters['payload']['message'],
                'be.bastelstu.chat.message',
                $message->messageID
            );
            $parameters['payload']['plaintextMessage'] = $this->processor->getHtml();
        }

        return $parameters['payload'];
    }

    /**
     * @inheritDoc
     */
    public function canSee(Message $message, Room $room, ?UserProfile $user = null): bool
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $parameters = [
            'message' => $message,
            'room' => $room,
            'user' => $user,
            'canSee' => $user->userID === $message->userID || $user->userID === $message->payload['recipient'],
        ];
        EventHandler::getInstance()->fireAction($this, 'canSee', $parameters);

        return $parameters['canSee'];
    }

    /**
     * @inheritDoc
     */
    public function canSeeInLog(Message $message, Room $room, ?UserProfile $user = null): bool
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $parameters = [
            'message' => $message,
            'room' => $room,
            'user' => $user,
            'canSee' => false,
        ];
        EventHandler::getInstance()->fireAction($this, 'canSeeInLog', $parameters);

        return $parameters['canSee'];
    }

    /**
     * @inheritDoc
     */
    public function supportsFastSelect(): bool
    {
        return false;
    }
}
