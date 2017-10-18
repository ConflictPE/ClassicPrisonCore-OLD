<?php

/**
 * ClassicPrisonCore – BaseArea.php
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
 * Created on 23/9/17 at 10:22 PM
 *
 */

namespace classicprison\area;

use classicprison\area\types\MineArea;
use classicprison\area\types\PvPArea;
use classicprison\area\types\SafeArea;
use classicprison\ClassicPrisonPlayer;
use classicprison\Main;
use core\exception\InvalidConfigException;
use core\Utils;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

abstract class BaseArea {

	/**
	 * Load an area from an array
	 *
	 * @param array $data
	 *
	 * @return BaseArea|MineArea|PvPArea|SafeArea
	 */
	public static function fromData(array $data) : BaseArea {
		try {
			$manager = Main::getInstance()->getAreaManager();

			if(($type = $manager->getKnownType($data["type"])) === null) {
				throw new InvalidConfigException("Attempted to add arena with an unknown type! Type: {$data["type"]}");
			}

			return new $type($manager, Utils::parseLevel($data["level"]), Utils::parseVector($data["a"]), Utils::parseVector($data["b"]));
		} catch(\Throwable $e) {
			throw new InvalidConfigException("Could not load area from data! Error: ". (new \ReflectionObject($e))->getShortName());
		}
	}

	/** @var AreaManager */
	private $manager;

	/** @var int */
	private $id;

	/** @var int */
	private $levelId;

	/** @var Position */
	private $a;

	/** @var Position */
	private $b;

	public function __construct(AreaManager $manager, Level $level, Vector3 $a, Vector3 $b) {
		$this->manager = $manager;
		$this->id = AreaManager::$areaCount++;
		$this->levelId = $level->getId();
		$this->a = $a;
		$this->b = $b;
	}

	public function getManager() : AreaManager {
		return $this->manager;
	}

	public function getId() : int {
		return $this->id;
	}

	public function getLevel() : ?Level {
		return $this->manager->getClassicPrison()->getServer()->getLevel($this->levelId);
	}

	public function getA() : Position {
		return $this->a;
	}

	public function getB() : Position {
		return $this->b;
	}

	/**
	 * Check if a position is within an area
	 *
	 * @param Position $pos
	 * @param bool $checkLevel
	 *
	 * @return bool
	 */
	public function within(Position $pos, bool $checkLevel = true) {
		if($checkLevel and $pos->getLevel()->getId() !== $this->getLevel()->getId()) {
			return false;
		}
		return (max($this->a->x, $this->b->x) <= $pos->x and min($this->a->x, $this->b->x) >= $pos->x) and (max($this->a->y, $this->b->y) <= $pos->y and min($this->a->y, $this->b->y) >= $pos->y) and (max($this->a->z, $this->b->z) <= $pos->z and min($this->a->z, $this->b->z) >= $pos->z);
	}

	/**
	 * Called when a player enters the area
	 *
	 * @param ClassicPrisonPlayer $player
	 * @param BaseArea|null $oldArea
	 *
	 * @return mixed
	 */
	abstract public function onAreaEnter(ClassicPrisonPlayer $player, BaseArea $oldArea = null);

}