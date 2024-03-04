<?php

/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-03-14
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\message\type;

use chat\data\message\Message;
use wcf\data\user\UserProfile;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Default implementation for 'getPayload'.
 */
trait TDefaultPayload
{
    /**
     * @see \chat\system\message\type\IMessageType::getPayload()
     */
    public function getPayload(Message $message, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $payload = $message->payload;

        $parameters = [
            'message' => $message,
            'user' => $user,
            'payload' => $payload,
        ];
        EventHandler::getInstance()->fireAction($this, 'getPayload', $parameters);

        return $parameters['payload'];
    }
}
