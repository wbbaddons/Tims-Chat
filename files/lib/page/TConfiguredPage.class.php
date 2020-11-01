<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-01
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\page;

use \chat\data\command\Command;
use \chat\data\command\CommandCache;
use \wcf\data\object\type\ObjectTypeCache;
use \wcf\data\package\PackageCache;

/**
 * Provides a getConfig() method, returning the JSON configuration
 * for the chat's JavaSCript.
 */
trait TConfiguredPage {
	/**
	 * Returns the configuration for the chat's JavaScript.
	 */
	public function getConfig() {
		$triggers = CommandCache::getInstance()->getTriggers();

		$commands = array_map(function (Command $item) {
			$package = PackageCache::getInstance()->getPackage($item->packageID)->package;
			return [ 'package'     => $package
			       , 'identifier'  => $item->identifier
			       , 'commandID'   => $item->commandID
			       , 'module'      => $item->getProcessor()->getJavaScriptModuleName()
			       , 'isAvailable' => $item->getProcessor()->isAvailable($this->room) && ($item->hasTriggers() || $item->getProcessor()->allowWithoutTrigger())
			       ];
		}, CommandCache::getInstance()->getCommands());

		$messageTypes = array_map(function ($item) {
			return [ 'module'     => $item->getProcessor()->getJavaScriptModuleName()
			       ];
		}, ObjectTypeCache::getInstance()->getObjectTypes('be.bastelstu.chat.messageType'));

		$config = [ 'clientVersion' => 1
		          , 'reloadTime'    => (int) CHAT_RELOADTIME
		          , 'autoAwayTime'  => (int) CHAT_AUTOAWAYTIME
		          , 'commands'      => $commands
		          , 'triggers'      => $triggers
		          , 'messageTypes'  => $messageTypes
		          ];

		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'config', $config);
		
		return \wcf\util\JSON::encode($config);
	}
}
