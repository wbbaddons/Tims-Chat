<?php

/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-01-13
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\system\event\listener;

use chat\data\suspension\Suspension;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Denies access to banned users.
 */
final class RoomCanJoinBanListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
            'be.bastelstu.chat.suspension',
            'be.bastelstu.chat.suspension.ban'
        );
        \assert($objectTypeID !== null);

        $suspensions = Suspension::getActiveSuspensionsByTriple(
            $objectTypeID,
            $parameters['user']->getDecoratedObject(),
            $eventObj
        );
        if ($suspensions !== []) {
            $parameters['result'] = new PermissionDeniedException(
                WCF::getLanguage()->getDynamicVariable('chat.suspension.info.be.bastelstu.chat.suspension.ban')
            );
        }
    }
}
