<?php

/**
 * ClassicPrisonCore – Mine.php
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

use core\Utils;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;

class Mine extends PluginTask {

	/** @var MineManager */
	private $manager;

	/** @var Vector3 */
	private $pointA;

	/** @var Vector3 */
	private $pointB;

	/** @var Level */
	private $level;

	/** @var Position */
	private $mineSpawn;

	/** @var array */
	private $data;

	/** @var string */
	private $name;

	/** @var bool */
	private $isResetting;

	/** @var int */
	private $resetInterval;

	/**
	 * Create a new mine instance from the save data
	 *
	 * @param MineManager $manager
	 * @param array $data
	 *
	 * @return Mine
	 */
	public static function fromSaveData(MineManager $manager, array $data) {
		return new Mine($manager, Utils::parseVector($data["a"]), Utils::parseVector($data["b"]), $data["level_name"], $data["name"], Utils::parsePosition($data["mine_spawn"]), $data["data"], $data["reset_interval"]);
	}

	public function __construct(MineManager $manager, Vector3 $pointA, Vector3 $pointB, $levelName, string $name, Position $mineSpawn = null, array $data = [], int $resetInterval = -1) {
		parent::__construct($manager->getPlugin());
		$this->manager = $manager;
		$this->pointA = $pointA;
		$this->pointB = $pointB;
		if(!$manager->getPlugin()->getServer()->isLevelLoaded($levelName)) {
			$manager->getPlugin()->getServer()->loadLevel($levelName);
		}
		$this->level = $levelName;
		if($mineSpawn instanceof Position) {
			$mineSpawn = $manager->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn();
		}
		$this->mineSpawn = $mineSpawn;
		$this->data = $data;
		$this->name = $name;
		$this->resetInterval = $resetInterval;
		$this->isResetting = false;
		$this->setHandler($manager->getPlugin()->getServer()->getScheduler()->scheduleRepeatingTask($this, 20 * $resetInterval));
	}

	/**
	 * @return MineManager
	 */
	public function getManager() : MineManager {
		return $this->manager;
	}

	/**
	 * INTERNAL USE ONLY
	 */
	public function destroy() {
		if($this->getHandler() !== null) {
			$this->manager->getPlugin()->getServer()->getScheduler()->cancelTask($this->getTaskId());
		}
	}

	public function onRun($currentTick) {
		$this->reset();
	}

	/**
	 * @return Vector3
	 */
	public function getPointA() : Vector3 {
		return $this->pointA;
	}

	/**
	 * @return Vector3
	 */
	public function getPointB() : Vector3 {
		return $this->pointB;
	}

	/**
	 * Check if a position intercepts with the mine
	 *
	 * @param Position $position
	 *
	 * @return bool
	 */
	public function isPointInside(Position $position) : bool {
		if(!($this->getLevel() instanceof Level) and $position->getLevel()->getId() !== $this->getLevel()->getId()) {
			return false;
		}
		return $position->getX() >= $this->getPointA()->getX()
			and $position->getX() <= $this->getPointB()->getX()
			and $position->getY() >= $this->getPointA()->getY()
			and $position->getY() <= $this->getPointB()->getY()
			and $position->getZ() >= $this->getPointA()->getZ()
			and $position->getZ() <= $this->getPointB()->getZ();
	}

	/**
	 * @return Level|null
	 */
	public function getLevel() {
		return $this->manager->getPlugin()->getServer()->getLevelByName($this->level);
	}

	/**
	 * @return string
	 */
	public function getLevelName() : string {
		return $this->level;
	}

	/**
	 * @return Position
	 */
	public function getMineSpawn() : Position {
		return $this->mineSpawn;
	}

	/**
	 * @return array
	 */
	public function getData() : array {
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData(array $data) {
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * @return bool
	 */
	public function isResetting() {
		return $this->isResetting;
	}

	/**
	 * Reset the mine
	 *
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function reset($force = false) {
		if((!$this->isResetting or $force) and $this->level !== null) {
			$this->isResetting = true;
			$chunks = [];
			$chunkClass = Chunk::class;

			// TODO: Cache hash of chunks that need to be reset so we don't need to execute this ugly loop every reset
			for($x = $this->getPointA()->getX(); $x - 16 <= $this->getPointB()->getX(); $x += 16) {
				for($z = $this->getPointA()->getZ(); $z - 16 <= $this->getPointB()->getZ(); $z += 16) {
					$chunk = $this->getLevel()->getChunk($x >> 4, $z >> 4, true);
					$chunkClass = get_class($chunk);
					$chunks[Level::chunkHash($x >> 4, $z >> 4)] = $chunk->fastSerialize();
				}
			}
			$resetTask = new ResetTask($this->getName(), $chunks, $this->getPointA(), $this->getPointB(), $this->data, $this->getLevel()->getId(), $chunkClass);
			$this->manager->getPlugin()->getServer()->getScheduler()->scheduleAsyncTask($resetTask);
			return true;
		}
		return false;
	}

	/**
	 * Teleport all players that are inside the mine to the designated safe position or the level spawn
	 */
	protected function removePlayers() {
		foreach($this->manager->getPlugin()->getServer()->getOnlinePlayers() as $player) {
			if($this->isPointInside($player->getPosition())) {

			}
		}
	}

	/**
	 * @return int
	 */
	public function getResetInterval() : int {
		return $this->resetInterval;
	}

	/**
	 * @param int $resetInterval
	 */
	public function setResetInterval(int $resetInterval) {
		$this->resetInterval = $resetInterval;
		$this->destroy();
		$this->setHandler($this->manager->getPlugin()->getServer()->getScheduler()->scheduleRepeatingTask($this, 20 * $resetInterval));
	}

	public function doneReset() {
		$this->isResetting = false;
	}

	/**
	 * Returns an array of data the mine can be recreated from
	 *
	 * @return array
	 */
	public function getSaveData() {
		return [
				"name" => $this->name,
				"a" => "{$this->pointA->x}, {$this->pointA->y}, {$this->pointA->z}",
				"b" => "{$this->pointB->x}, {$this->pointB->y}, {$this->pointB->z}",
				"data" => $this->data,
				"level_name" => $this->level,
				"reset_interval" => $this->resetInterval,
				"mine_spawn" => "{$this->mineSpawn->x}, {$this->mineSpawn->y}, {$this->mineSpawn->z}, {$this->mineSpawn->getLevel()->getName()}",
			];
	}

}