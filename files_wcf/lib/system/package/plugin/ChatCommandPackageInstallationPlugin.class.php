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

namespace wcf\system\package\plugin;

use \wcf\system\exception\SystemException;
use \wcf\system\WCF;

/**
 * Installs, updates and deletes chat commands.
 */
class ChatCommandPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements \wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = \chat\data\command\CommandEditor::class;

	/**
	 * @inheritDoc
	 */
	public $application = 'chat';

	/**
	 * Removing this and relying on table name guessing breaks uninstallation
	 * as the application autoloader is unavailable there.
	 *
	 * @inheritDoc
	 */
	public $tableName = 'command';

	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM ".$this->application.WCF_N."_".$this->tableName."
		        WHERE           packageID = ?
		                    AND identifier = ?";
		$statement = WCF::getDB()->prepareStatement($sql);

		WCF::getDB()->beginTransaction();
		foreach ($items as $item) {
			$statement->execute([
				$this->installation->getPackageID(),
				$item['attributes']['name']
			]);
		}
		WCF::getDB()->commitTransaction();
	}

	/**
	 * @inheritDoc
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;

		if ($element->tagName === 'triggers') {
			$nodeValue = [ ];
			$triggers = $xpath->query('child::*', $element);

			foreach ($triggers as $trigger) {
				$nodeValue[] = $trigger->nodeValue;
			}
		}

		$elements[$element->tagName] = $nodeValue;
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		return [
			'identifier' => $data['attributes']['name'],
			'className' => $data['elements']['classname'],
			'triggers' => isset($data['elements']['triggers']) ? $data['elements']['triggers'] : [ ]
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function validateImport(array $data) {
		if ($data['identifier'] === '') {
			throw new SystemException('Command identifier (name attribute) may not be empty');
		}
		if (!class_exists($data['className'])) {
			throw new SystemException("'".$data['className']."' does not exist.");
		}
		if (!\wcf\util\ClassUtil::isInstanceOf($data['className'], \chat\system\command\ICommand::class)) {
			throw new SystemException("'".$data['className']."' does not implement '\chat\system\command\ICommand.'");
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT *
		        FROM   ".$this->application.WCF_N."_".$this->tableName."
		        WHERE      packageID = ?
		              AND identifier = ?";
		$parameters = [
			$this->installation->getPackageID(),
			$data['identifier']
		];

		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		$triggers = $data['triggers'];
		unset($data['triggers']);

		$result = parent::import($row, $data);

		if (empty($row)) {
			// import initial triggers
			$sql = "INSERT INTO ".$this->application.WCF_N."_command_trigger (commandTrigger, commandID)
			        VALUES      (?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);

			try {
				WCF::getDB()->beginTransaction();

				foreach ($triggers as $trigger) {
					try {
						$statement->execute(array(
							$trigger,
							$result->commandID
						));
					}
					catch (\wcf\system\database\DatabaseException $e) {
						// Duplicate key errors don't cause harm.
						if ((string) $e->getCode() !== '23000') throw $e;
					}
				}

				WCF::getDB()->commitTransaction();
			}
			catch (\Exception $e) {
				WCF::getDB()->rollBackTransaction();
				throw $e;
			}
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public static function getSyncDependencies() {
		return [ 'file' ];
	}
}
