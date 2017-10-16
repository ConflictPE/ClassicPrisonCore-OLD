<?php

/**
 * ClassicPrisonCore – AreaUpdateTask.php
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
 * Created on 23/9/17 at 10:39 PM
 *
 */

namespace classicprison\area;

use classicprison\ClassicPrisonPlayer;
use pocketmine\scheduler\PluginTask;

/**
 * Simple task to keep track of which area a player is in
 */
class AreaUpdateTask extends PluginTask {

	/** @var AreaManager */
	private $manager;

	public function __construct(AreaManager $manager) {
		$this->manager = $manager;
		parent::__construct($manager->getPlugin());
		$manager->getPlugin()->getServer()->getScheduler()->scheduleRepeatingTask($this, 20); // execute this task every second
	}

	public function getManager() : AreaManager {
		return $this->manager;
	}

	public function onRun(int $currentTick) {
		$time = microtime(true);
		/** @var ClassicPrisonPlayer $player */
		foreach($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
			if($time - $player->getLastMoveTime() <= 1) { // only check a players area if they have moved within the last second
				foreach($this->manager->getAreas() as $area) {
					if($area->getLevel() === $player->getLevel()) { // make sure player is in same level as area first
						if($area->within($player->getPosition(), false)) {
							$player->setArea($area);
							break; // move on to next player
						}
					}
				}
			}
		}
	}

}