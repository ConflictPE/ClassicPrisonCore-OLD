<?php

/**
 * ClassicPrisonCore – KitManager.php
 *
 * Copyright (C) 2017 Jack Noordhuis
 *
 * This is private software, you cannot redistribute and/or modify it in any way
 * unless given explicit permission to do so. If you have not been given explicit
 * permission to view or modify this software you should take the appropriate actions
 * to remove this software from your device immediately.
 *
 * @author Jack Noordhuis
 *
 * Created on 14/9/17 at 10:06 PM
 *
 */

namespace classicprison\kit;

use classicprison\Main;
use core\exception\InvalidConfigException;
use core\language\LanguageUtils;
use pocketmine\entity\Effect;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\scheduler\FileWriteTask;

class KitManager {

	/** @var Main */
	private $plugin;

	/** @var Kit[] */
	private $kits = [];

	/* Path to where the kit data file is stored */
	const KITS_DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "kits.json";

	/* Path to where the kit data file is stored */
	const COOLDOWN_DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "cooldowns.sl";

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;

		$this->registerFromData();
		$this->loadKitCooldowns();
	}

	/**
	 * @return Main
	 */
	public function getPlugin() : Main {
		return $this->plugin;
	}

	/**
	 * Attempt to load the cooldowns for all the currently active kits and backup data for non-existent kits
	 */
	public function loadKitCooldowns() {
		$path = $this->plugin->getDataFolder() . self::COOLDOWN_DATA_FILE_PATH;
		if(file_exists($path)) {
			foreach(unserialize(file_get_contents($this->plugin->getDataFolder() . self::COOLDOWN_DATA_FILE_PATH)) as $kitName => $activeCooldowns) {
				if($this->kitExists($kitName)) {
					$this->getKit($kitName)->setActiveCooldowns($activeCooldowns);
				} else {
					$path = $this->plugin->getDataFolder() . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "{$kitName}.bak.sl";
					if(!file_exists($path)) {
						file_put_contents($path, serialize($activeCooldowns));
						$this->plugin->getLogger()->warning("Attempted to load cooldown data for unknown kit, data has been backed up to {$kitName}.bak.sl.");
					}
				}
			}
		}
	}

	/**
	 * Save all the current kit cooldowns
	 *
	 * @param bool $async
	 */
	public function saveKitCooldowns($async = true) {
		$data = [];
		foreach($this->kits as $kit) {
			$data[$kit->getName()] = $kit->getActiveCooldowns();
		}
		$data = serialize($data);
		$path = $this->plugin->getDataFolder() . self::COOLDOWN_DATA_FILE_PATH;
		if($async) {
			$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new FileWriteTask($path, $data));
		} else {
			file_put_contents($path, $data);
		}
	}

	/**
	 * Registers the kit data from kits.json
	 */
	private function registerFromData() {
		$this->plugin->saveResource(self::KITS_DATA_FILE_PATH);
		$data = json_decode(file_get_contents($this->plugin->getDataFolder() . self::KITS_DATA_FILE_PATH), true);
		foreach($data as $kitName => $kitData) {
			try {
				$this->addKit(Kit::fromData($kitName, $kitData));
			} catch(InvalidConfigException $e) {
				$this->plugin->getLogger()->warning("Could not load kit {$kitName} due to invalid config! Message: {$e->getMessage()}");
			}
		}
	}

	/**
	 * @param Kit $kit
	 */
	public function addKit(Kit $kit) {
		$this->kits[$kit->getName()] = $kit;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function kitExists(string $name) {
		return isset($this->kits[$name]) and $this->kits[$name] instanceof Kit;
	}

	/**
	 * @param string $name
	 *
	 * @return Kit|null
	 */
	public function getKit(string $name) {
		$name = strtolower($name);
		if($this->kitExists($name)) {
			return $this->kits[$name];
		}
		return null;
	}

	/**
	 * @return Kit[]
	 */
	public function getKits() : array {
		return $this->kits;
	}

}