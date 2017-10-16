<?php

/**
 * ClassicPrisonCore – MineManager.php
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
 * Created on 23/9/17 at 10:18 PM
 *
 */

namespace classicprison\mine;

use classicprison\Main;
use core\exception\InvalidConfigException;

class MineManager {

	const MINES_DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "mines.json";

	/** @var Main */
	private $plugin;

	/** @var Mine[] */
	private $minePool = [];

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * @return Main
	 */
	public function getPlugin() : Main {
		return $this->plugin;
	}

	public function registerFromData() {
		$this->plugin->saveResource(self::MINES_DATA_FILE_PATH);
		$data = json_decode(file_get_contents($this->plugin->getDataFolder() . self::MINES_DATA_FILE_PATH), true);
		foreach($data as $mineName => $mineData) {
			try {
				$this->addMine(Mine::fromData(strtolower($mineName), $mineData));
			} catch(InvalidConfigException $e) {
				$this->plugin->getLogger()->warning("Could not load mine {$mineName} due to invalid config! Message: {$e->getMessage()}");
				$this->plugin->getLogger()->logException($e);
			}
		}
	}

	public function addMine(Mine $mine) {
		$this->minePool[$mine->getName()] = $mine;
		$this->plugin->getAreaManager()->addArea($mine->getArea());
		$this->plugin->getWarpManager()->addWarp($mine->getWarp());
	}

	public function getMine(string $name) : ?Mine {
		return $this->minePool[strtolower($name)] ?? null;
	}

}