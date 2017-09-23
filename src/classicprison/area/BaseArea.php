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

use classicprison\ClassicPrisonPlayer;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

abstract class BaseArea {

	/** @var AreaManager */
	private $manager;

	/** @var int */
	private $id;

	/** @var Level */
	private $level;

	/** @var Position */
	private $a;

	/** @var Position */
	private $b;

	public function __construct(AreaManager $manager, Level $level, Vector3 $a, Vector3 $b) {
		$this->manager = $manager;
		$this->id = AreaManager::$areaCount++;
		$this->level = $level;
		$this->a = $a;
		$this->b = $b;
	}

	public function getManager() : AreaManager {
		return $this->manager;
	}

	public function getId() : int {
		return $this->id;
	}

	public function getLevel() : Level {
		return $this->level;
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
		if($checkLevel and $pos->getLevel() !== $this->level) {
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