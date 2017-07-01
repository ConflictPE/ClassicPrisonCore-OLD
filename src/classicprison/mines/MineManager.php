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
 * @author JackNoordhuis
 *
 * Created on 1/7/2017 at 9:28 PM
 *
 */

namespace classicprison\mines;

use classicprison\Main;
use pocketmine\level\Position;
use pocketmine\scheduler\FileWriteTask;

class MineManager {

	/** @var Main */
	private $plugin;

	/** @var string */
	private $minesDataPath = "";

	/** @var Mine[] */
	private $mines = [];

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$this->plugin->saveResource(Main::MINES_DATA_FILE);
		$this->minesDataPath = $plugin->getDataFolder() . Main::MINES_DATA_FILE;
	}

	/**
	 * @return Main
	 */
	public function getPlugin() : Main {
		return $this->plugin;
	}

	/**
	 * Save all the mines to the
	 *
	 * @var $async bool
	 */
	public function saveMines($async = true) {
		$data = [];
		foreach($this->mines as $mine) {
			$data[] = $mine->getSaveData();
		}
		if(!empty($data)) {
			$data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
			if($async) {
				$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new FileWriteTask($this->minesDataPath, $data));
			} else {
				file_put_contents($this->minesDataPath, $data);
			}
		}
	}

	/**
	 * Load the mines from the save data
	 */
	public function loadMines() {
		foreach(json_decode(file_get_contents($this->minesDataPath, true)) as $data) {
			try { // make sure the mine data save was loaded correctly
				$mine = Mine::fromSaveData($this, $data);
			} catch(\Throwable $e) { // uh oh, looks like there was a problem
				$this->plugin->getLogger()->error("Encountered malformed mine data while attempting to load mines! Error: {$e->getMessage()}"); // alert the console of malformed data
				$this->plugin->getLogger()->debug("Mine data: " . json_encode($data)); // dump the mine data to debug channel
				continue; // skip this mine
			}

			$this->mines[strtolower($mine->getName())] = $mine;
		}
	}

	/**
	 * @return Mine[]
	 */
	public function getMines() : array {
		return $this->mines;
	}

	/**
	 * @param Position $position
	 *
	 * @return bool|Mine
	 */
	public function isMineResettingAtPosition(Position $position) : bool {
		foreach($this->mines as $mine) {
			if($mine->isResetting() and $mine->isPointInside($position)) {
				return $mine;
			}
		}
		return false;
	}

}