<?php

/**
 * ClassicPrisonCore – WarpManager.php
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
 * Created on 15/9/17 at 4:06 PM
 *
 */

namespace classicprison\warp;

use classicprison\Main;
use core\exception\InvalidConfigException;
use core\Utils;
use pocketmine\level\Position;

class WarpManager {

	/** @var Main */
	private $plugin;

	/** @var Warp[] */
	private $warps = [];

	/* Path to where the warps data file is stored */
	const WARPS_DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "warps.json";

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;

		$this->registerFromData();
	}

	/**
	 * @return Main
	 */
	public function getPlugin() : Main {
		return $this->plugin;
	}

	private function registerFromData() {
		$this->plugin->saveResource(self::WARPS_DATA_FILE_PATH);
		foreach(json_decode(file_get_contents($this->plugin->getDataFolder() . self::WARPS_DATA_FILE_PATH), true) as $warpName => $warpData) {
			try {
				$this->addWarp($warpName, $warpData["display"] ?? $warpName, Utils::parsePosition($warpData["pos"]), $warpData["aliases"] ?? [], $warpData["public"] ?? true, $this->parseWarpType($warpData["type"] ?? ""));
			} catch(InvalidConfigException $e) { // if there is an error loading a warp from the config data
				$this->plugin->getLogger()->warning("Could not load warp {$warpName} due to invalid config! Message: {$e->getMessage()}");
			} catch(WarpException $e) { // if there is a problem registering the warp
				$this->plugin->getLogger()->debug($e->getMessage());
			}
		}
	}

	/**
	 * @param string $name
	 * @param string $display
	 * @param Position $pos
	 * @param array $aliases
	 * @param bool $public
	 * @param int $type
	 *
	 * @throws WarpException
	 */
	public function addWarp(string $name, string $display, Position $pos, array $aliases, bool $public, int $type) {
		$this->warps[$name] = $warp = new Warp($name, $display, $pos, $aliases, $public, $type);
		foreach($aliases as $alias) {
			if($alias === $name) {
				throw new WarpException("Tried to register an alias with the same name as the warp! Warp: {$name} Alias: {$alias}");
			} elseif(isset($this->warps[$alias])) {
				throw new WarpException("Tried to register an alias for a warp that already exists! Warp: {$name} Alias: {$alias}");
			} else {
				$this->warps[$alias] = $warp;
			}
		}
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function warpExists(string $name) {
		return isset($this->warps[$name]) and $this->warps[$name] instanceof Warp;
	}

	/**
	 * @param string $name
	 *
	 * @return Warp|null
	 */
	public function getWarp(string $name) {
		$name = strtolower($name);
		if($this->warpExists($name)) {
			return $this->warps[$name];
		}
		return null;
	}

	/**
	 * @return Warp[]
	 */
	public function getWarps() : array {
		return $this->warps;
	}

	/**
	 * @param string $value
	 *
	 * @return int
	 */
	protected function parseWarpType(string $value) : int {
		switch(strtolower($value)) {
			case "mine":
				return Warp::WARP_TYPE_MINE;
			default:
				return Warp::WARP_TYPE_GENERIC;
		}
	}

}