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

namespace chat\system;

use chat\page\RoomListPage;
use wcf\system\application\AbstractApplication;
use wcf\system\request\route\StaticRequestRoute;
use wcf\system\request\RouteHandler;

class CHATCore extends AbstractApplication
{
    /**
     * @inheritDoc
     */
    protected $primaryController = RoomListPage::class;

    /**
     * @inheritDoc
     */
    public function __run()
    {
        $route = new StaticRequestRoute();
        $route->setStaticController('chat', 'Log');
        $route->setBuildSchema('/{controller}/{id}-{title}/{messageid}');
        $route->setPattern('~^/?(?P<controller>[^/]+)/(?P<id>\d+)(?:-(?P<title>[^/]+))?/(?P<messageid>\d+)~x');
        $route->setRequiredComponents([
            'id' => '~^\d+$~',
            'messageid' => '~^\d+$~',
        ]);
        $route->setMatchController(true);

        RouteHandler::getInstance()->addRoute($route);
    }
}
