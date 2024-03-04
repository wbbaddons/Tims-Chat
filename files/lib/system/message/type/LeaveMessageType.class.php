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

/**
 * LeaveMessageType represents a leave message.
 */
final class LeaveMessageType implements IMessageType
{
    use TCanSeeInSameRoom;
    use TDefaultPayload;

    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName(): string
    {
        return 'Bastelstu.be/Chat/MessageType/Leave';
    }
}
