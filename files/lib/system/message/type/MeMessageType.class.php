<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\message\type;

use chat\data\message\Message;
use wcf\data\user\UserProfile;
use wcf\system\WCF;

/**
 * MeMessageType represents an action message.
 */
final class MeMessageType implements IMessageType, IDeletableMessageType
{
    use TCanSeeInSameRoom;
    use TDefaultPayload;

    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName()
    {
        return 'Bastelstu.be/Chat/MessageType/Me';
    }

    /**
     * @inheritDoc
     */
    public function canDelete(Message $message, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        return $user->getPermission('mod.chat.canDelete');
    }
}
