<?php

/**
 * ClassicPrisonCore – Warp.php
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
 * Created on 15/9/17 at 4:07 PM
 *
 */

namespace classicprison\warp;

use core\language\LanguageUtils;
use pocketmine\level\Position;
use pocketmine\Player;

class Warp {

	const WARP_TYPE_GENERIC = 0x00;
	const WARP_TYPE_MINE = 0x010;

	/** @var string */
	private $name;

	/** @var string */
	private $display;

	/** @var Position */
	private $pos;

	/** @var string[] */
	private $aliases = [];

	/** @var bool */
	private $public;

	/** @var int */
	private $warpType;

	public function __construct(string $name, string $display, Position $pos, array $aliases = [], bool $public = true, int $warpType = self::WARP_TYPE_GENERIC) {
		$this->name = $name;
		$this->display = $display;
		$this->pos = $pos;
		$this->aliases = $aliases;
		$this->public = $public;
		$this->warpType = $warpType;
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
	 * @return Position
	 */
	public function getPosition() : Position {
		return $this->pos;
	}

	/**
	 * @return array
	 */
	public function getAliases() : array {
		return $this->aliases;
	}

	/**
	 * @param string $alias
	 */
	public function addAlias(string $alias) {
		$this->aliases[] = strtolower($alias);
	}

	/**
	 * @return bool
	 */
	public function isPublic() : bool {
		return $this->public;
	}

	/**
	 * @param bool $value
	 */
	public function setPublic(bool $value = true) {
		$this->public = $value;
	}

	/**
	 * @return int
	 */
	public function getWarpType() : int {
		return $this->warpType;
	}

	/**
	 * @return bool
	 */
	public function isGenericWarp() : bool {
		return ($this->warpType & 0xF0) === self::WARP_TYPE_GENERIC;
	}

	/**
	 * @return bool
	 */
	public function isMineWarp() : bool {
		return ($this->warpType & 0xF0) === self::WARP_TYPE_MINE;
	}

	/**
	 * @param Player $player
	 */
	public function warpPlayer(Player $player) {
		$player->sendMessage(LanguageUtils::translateColors("&6- &aWarping to {$this->getDisplay()}&r&a..."));
		$player->teleport($this->pos);
	}

}