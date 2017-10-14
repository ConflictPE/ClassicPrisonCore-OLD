<?php

/**
 * ClassicPrisonCore – AreaManager.php
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

namespace classicprison\area;

use classicprison\area\types\MineArea;
use classicprison\area\types\PvPArea;
use classicprison\area\types\SafeArea;
use classicprison\Main;
use core\exception\InvalidConfigException;
use core\Utils;

class AreaManager {

	/** @var Main */
	private $plugin;

	/** @var string[] */
	private $knownTypes = [];

	/** @var BaseArea[] */
	private $areaPool = [];

	/** @var AreaUpdateTask */
	private $updateTask ;

	/** @var int */
	public static $areaCount = 0;

	const AREAS_DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "areas.json";

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;

		$this->registerAreaType(MineArea::class, true); // register the 'MineArea' type
		$this->registerAreaType(SafeArea::class, true); // register the 'SafeArea' type
		$this->registerAreaType(PvPArea::class, true); // register the 'PvPArea' type

		$this->registerFromData();

		$this->updateTask = new AreaUpdateTask($this);
	}

	/**
	 * @return Main
	 */
	public function getPlugin() : Main {
		return $this->plugin;
	}

	/**
	 * Register an area type
	 *
	 * @param string $class
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function registerAreaType(string $class, bool $force = false) {
		if(is_a($class, BaseArea::class, true) and !($reflection = new \ReflectionClass($class))->isAbstract()) {
			if(isset($this->knownTypes[$shortName = $reflection->getShortName()]) and !$force) {
				return false;
			}
			$this->knownTypes[$reflection[$shortName]] = $class;
		}
		return false;
	}

	/**
	 * @param string $type
	 *
	 * @return string|null
	 */
	public function getKnownType(string $type) : ?string {
		return $this->knownTypes[$type] ?? null;
	}

	public function registerFromData() {
		$this->plugin->saveResource(self::AREAS_DATA_FILE_PATH);
		foreach(json_decode(file_get_contents($this->plugin->getDataFolder() . self::AREAS_DATA_FILE_PATH), true) as $configId => $areaData) {
			try {
				$this->addArea(BaseArea::fromData($areaData));
			} catch(InvalidConfigException $e) {
				$this->plugin->getLogger()->warning("Could not load area #{$configId} due to invalid config! Message: {$e->getMessage()}");
				$this->plugin->getLogger()->logException($e);
			}
		}
	}

	public function addArea(BaseArea $area) {
		$this->areaPool[$area->getId()] = $area;
	}

	/**
	 * @return BaseArea[]
	 */
	public function getAreas() : array {
		return $this->areaPool;
	}

	/**
	 * Get an area using it's ID
	 *
	 * @param int $id
	 *
	 * @return BaseArea|null
	 */
	public function getArea(int $id) {
		if($this->areaExists($id)) {
			return $this->areaPool[$id];
		}
		return null;
	}

	/**
	 * Check if an area exists
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function areaExists(int $id) : bool {
		return isset($this->areaPool[$id]) and $this->areaPool[$id] instanceof BaseArea;
	}

	/**
	 * Try and get an areas type from a string
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function getType(string $text) : string {
		switch(Utils::stripWhiteSpace(strtolower($text))) {
			case "pvp":
			case "pvpzone":
			case "pvparea":
				return "PvPArea";
			case "mine":
				return "MineArea";
			default:
				return "SafeArea";
		}
	}

}