<?php

/**
 * ClassicPrisonCore – EffectLoot.php
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
use pocketmine\entity\Effect;
use pocketmine\item\Item;

class EffectLoot extends BaseLoot {

	/** @var Effect */
	private $effect;

	public function __construct($name, Effect $effect, Item $display) {
		$this->effect = $effect;
		parent::__construct($name, $display);
	}

	public function getLootType() : string {
		return "Effect";
	}

	public function applyLoot(ClassicPrisonPlayer $player) : bool {
		$player->addEffect($this->effect);
		return true;
	}

}