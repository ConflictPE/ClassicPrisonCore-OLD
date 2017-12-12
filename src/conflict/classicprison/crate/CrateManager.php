<?php

/**
 * ClassicPrisonCore – CrateManager.php
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
 * Created on 23/9/17 at 10:17 PM
 *
 */

namespace conflict\classicprison\crate;

use conflict\classicprison\crate\loot\BaseLoot;
use conflict\classicprison\crate\loot\EffectLoot;
use conflict\classicprison\crate\loot\ItemLoot;
use conflict\classicprison\crate\loot\MoneyLoot;
use conflict\classicprison\Main;
use conflict\classicprison\util\traits\ClassicPrisonPluginReference;
use core\exception\InvalidConfigException;
use core\Utils;

class CrateManager {

	use ClassicPrisonPluginReference;

	/** @var string[] */
	private $knownLootTypes = [];

	/** @var Crate[] */
	private $cratesPool = [];

	const CRATES_DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "crates.json";

	public function __construct(Main $plugin) {
		$this->setClassicPrison($plugin);

		$this->registerLootType(EffectLoot::class, true); // register the default effect loot type
		$this->registerLootType(ItemLoot::class, true); // register the default item loot type
		$this->registerLootType(MoneyLoot::class, true); // register the default money loot type

		$this->registerFromData();
	}

	/**
	 * Register a loot type
	 *
	 * @param string $class
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function registerLootType(string $class, bool $force = false) : bool {
		if(is_a($class, BaseLoot::class, true) and !($reflection = new \ReflectionClass($class))->isAbstract()) {
			if(isset($this->knownTypes[$shortName = $reflection->getShortName()]) and !$force) {
				return false;
			}
			$this->knownLootTypes[$reflection[$shortName]] = $class;
		}
		return false;
	}

	/**
	 * @param string $type
	 *
	 * @return null|string
	 */
	public function getKnownLootType(string $type) : ?string {
		return $this->knownLootTypes[$type] ?? null;
	}

	protected function registerFromData() {
		$plugin = $this->getClassicPrison();
		$plugin->saveResource(self::CRATES_DATA_FILE_PATH);
		foreach(json_decode(file_get_contents($plugin->getDataFolder() . self::CRATES_DATA_FILE_PATH), true) as $configId => $crateData) {
			try {
				$this->addCrate(Crate::fromData($crateData));
			} catch(InvalidConfigException $e) {
				$plugin->getLogger()->warning("Could not load area #{$configId} due to invalid config! Message: {$e->getMessage()}");
				$plugin->getLogger()->logException($e);
			}
		}
	}

	public function addCrate() {

	}

	public function getCrates() {

	}

	public function getCrate() {

	}

	public function crateExists() {

	}

	/**
	 * Try and get a loots type from a string
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function getType(string $text) : string {
		switch(Utils::stripWhiteSpace(strtolower($text))) {
			case "effect":
				return "EffectLoot";
			case "item":
			default:
				return "ItemLoot";
			case "money":
				return "MoneyLoot";
		}
	}

}