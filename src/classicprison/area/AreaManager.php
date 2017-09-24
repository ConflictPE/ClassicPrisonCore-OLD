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

use classicprison\area\types\PvPArea;
use classicprison\area\types\SafeArea;
use classicprison\Main;
use core\Utils;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

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

		$this->registerArea(SafeArea::class, true); // register the 'SafeArea' type
		$this->registerArea(PvPArea::class, true); // register the 'PvPArea' type

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
	public function registerArea(string $class, bool $force = false) {
		if(is_a($class, BaseArea::class, true) and !($reflection = new \ReflectionClass($class))->isAbstract()) {
			if(isset($this->knownTypes[$shortName = $reflection->getShortName()]) and !$force) {
				return false;
			}
			$this->knownTypes[$reflection[$shortName]] = $class;
		}
		return false;
	}

	public function registerFromData() {
		$this->plugin->saveResource(self::AREAS_DATA_FILE_PATH);
		foreach(json_decode(file_get_contents($this->plugin->getDataFolder() . self::AREAS_DATA_FILE_PATH), true) as $id => $data) {
			try {
				$this->addArea(self::getType($data["type"]), $this->plugin->getServer()->getLevel($data["level"]) ?? $this->plugin->getServer()->getDefaultLevel(), Utils::parseVector($data["a"]), Utils::parseVector($data["b"]));
			} catch(AreaException $e) {
				$this->plugin->getLogger()->warning("Couldn't load area {$id}! Message: {$e->getMessage()}");
			}
		}
	}

	public function addArea(string $type, Level $level, Vector3 $a, Vector3 $b) {
		if(isset($this->knownTypes[$type])) {
			$class = $this->knownTypes[$type];
			/** @var BaseArea $area */
			$area = new $class($this, $level, $a, $b);
			$this->areaPool[$area->getId()] = $area;
		} else {
			throw new AreaException("Attempted to add arena with an unknown type! Type: {$type}");
		}
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
			default:
				return "SafeArea";
		}
	}

}