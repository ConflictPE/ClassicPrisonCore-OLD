<?php

/**
 * ClassicPrisonCore – BaseLoot.php
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
 * Created on 11/12/17 at 8:59 PM
 *
 */

namespace conflict\classicprison\crate\loot;

use conflict\classicprison\ClassicPrisonPlayer;
use pocketmine\item\Item;

abstract class BaseLoot {

	/** @var string */
	private $name;

	/** @var Item */
	private $displayItem;

	public function __construct(string $name, Item $displayItem) {
		$this->name = $name;
		$this->displayItem = $displayItem;
	}

	/**
	 * Name of the loot
	 *
	 * @return string
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * Get the type of loot
	 *
	 * @return string
	 */
	public abstract function getLootType() : string;

	/**
	 * Item to be displayed when 'rolling' the crate
	 *
	 * @return Item
	 */
	public function getDisplayItem() : Item {
		return $this->displayItem;
	}

	/**
	 * Give the loot to a player
	 *
	 * @param ClassicPrisonPlayer $player
	 *
	 * @return bool
	 */
	abstract public function applyLoot(ClassicPrisonPlayer $player) : bool;

}