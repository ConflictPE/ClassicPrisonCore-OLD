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
 * @author Jack Noordhuis
 *
 * Created on 14/10/17 at 9:11 AM
 *
 */

namespace conflict\classicprison\mine;

use conflict\classicprison\area\types\MineArea;
use conflict\classicprison\Main;
use conflict\classicprison\warp\Warp;
use core\exception\InvalidConfigException;
use core\language\LanguageUtils;
use core\Utils;
use pocketmine\level\Level;
use pocketmine\level\Position;

class Mine {

	/**
	 * Load a mine from an array
	 *
	 * @param string $name
	 * @param array $data
	 *
	 * @return Mine
	 */
	public static function fromData(string $name, array $data) : Mine {
		try {
			return new Mine($name = strtolower($name), $display = LanguageUtils::translateColors($data["display"]), new MineArea(Main::getInstance()->getAreaManager(), Utils::parseLevel($data["level"]), Utils::parseVector($data["a"]), Utils::parseVector($data["b"]), $name), new Warp($name, $display, Utils::parsePosition($data["warp"])), $data["ratios"]);
		} catch(\Throwable $e) {
			throw new InvalidConfigException("Could not load mine from data! Error: ". (new \ReflectionObject($e))->getShortName());
		}
	}

	/** @var string */
	private $name;

	/** @var string */
	private $display;

	/** @var MineArea */
	private $area;

	/** @var Warp */
	private $warp;

	/** @var array */
	private $ratios = [];

	public function __construct(string $name, string $display, MineArea $area, Warp $warp, array $ratios) {
		$this->name = $name;
		$this->display = $display;
		$this->area = $area;
		$this->warp = $warp;
		$this->ratios = $ratios;
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDisplay() : string {
		return $this->display;
	}

	/**
	 * @return MineArea
	 */
	public function getArea() : MineArea {
		return $this->area;
	}

	/**
	 * @return Warp
	 */
	public function getWarp() : Warp {
		return $this->warp;
	}

	/**
	 * @return Level|null
	 */
	public function getLevel() : ?Level {
		return $this->area->getLevel();
	}

	/**
	 * @return Position
	 */
	public function getA() : Position {
		return $this->area->getA();
	}

	/**
	 * @return Position
	 */
	public function getB() : Position {
		return $this->area->getB();
	}

	/**
	 * @return array
	 */
	public function getRatios() : array {
		return $this->ratios;
	}

}