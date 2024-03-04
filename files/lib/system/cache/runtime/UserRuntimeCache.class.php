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

namespace chat\system\cache\runtime;

use chat\data\user\UserList as ChatUserList;
use wcf\system\cache\runtime\AbstractRuntimeCache;

/**
 * Runtime cache implementation for chat users.
 */
class UserRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = ChatUserList::class;
}
