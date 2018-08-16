<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-08-16
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system;

class CHATCore extends \wcf\system\application\AbstractApplication {
	/**
	 * @inheritDoc
	 */
	protected $primaryController = \chat\page\RoomListPage::class;

	/**
	 * @inheritDoc
	 */
	public function __run() {
		$route = new \wcf\system\request\route\StaticRequestRoute();
		$route->setStaticController('chat', 'Log');
		$route->setBuildSchema('/{controller}/{id}-{title}/{messageid}');
		$route->setPattern('~^/?(?P<controller>[^/]+)/(?P<id>\d+)(?:-(?P<title>[^/]+))?/(?P<messageid>\d+)~x');
		$route->setRequiredComponents([ 'id' => '~^\d+$~'
		                              , 'messageid' => '~^\d+$~'
		                              ]);
		$route->setMatchController(true);

		\wcf\system\request\RouteHandler::getInstance()->addRoute($route);
	}
}
