<?php

/**
 * ClassicPrisonCore – ItemLoot.php
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
 * Created on 11/12/17 at 9:03 PM
 *
 */

namespace conflict\classicprison\crate\loot;

use conflict\classicprison\ClassicPrisonPlayer;
use pocketmine\item\Item;

class ItemLoot extends BaseLoot {

	/** @var Item */
	protected $loot;

	public function __construct($name, Item $loot) {
		$this->loot = $loot;
		$display = clone $loot;
		parent::__construct($name, $display);
	}

	public function getLootType() : string {
		return "Item";
	}

	public function applyLoot(ClassicPrisonPlayer $player) : bool {
		$player->getInventory()->addItem($this->loot);
		return true;
	}

}