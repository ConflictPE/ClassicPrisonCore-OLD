<?php

/**
 * ClassicPrisonCore – MoneyLoot.php
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
use pocketmine\utils\TextFormat;

class MoneyLoot extends BaseLoot {

	/** @var int */
	private $money = 0;

	public function __construct($name, int $money) {
		$this->money = $money;
		$display = Item::get(Item::PAPER);
		$display->setCustomName(TextFormat::GREEN . "${$money}");
		parent::__construct($name, $display);
	}

	public function getLootType() : string {
		return "Money";
	}

	public function applyLoot(ClassicPrisonPlayer $player) : bool {
		return true;
	}

}