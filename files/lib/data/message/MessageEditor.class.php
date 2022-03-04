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

namespace chat\data\message;

use wcf\data\DatabaseObjectEditor;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\WCF;

/**
 * Represents a chat message editor.
 */
class MessageEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Message::class;

    /**
     * @inheritDoc
     */
    public static function deleteAll(array $messageIDs = [])
    {
        WCF::getDB()->beginTransaction();

        $result = parent::deleteAll($messageIDs);
        if ($messageIDs !== []) {
            AttachmentHandler::removeAttachments('be.bastelstu.chat.message', $messageIDs);
        }

        WCF::getDB()->commitTransaction();

        return $result;
    }
}
