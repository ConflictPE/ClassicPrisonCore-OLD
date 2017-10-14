<?php

/**
 * ClassicPrisonCore – MineArea.php
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
 * Created on 14/10/17 at 9:46 AM
 *
 */

namespace classicprison\area\types;

use classicprison\area\AreaManager;
use classicprison\area\BaseArea;
use classicprison\ClassicPrisonPlayer;
use core\language\LanguageUtils;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class MineArea extends SafeArea {

	/** @var string */
	private $mineDisplay;

	public function __construct(AreaManager $manager, Level $level, Vector3 $a, Vector3 $b, string $mineDisplay) {
		parent::__construct($manager, $level, $a, $b);
	}

	public function getMineDisplay() : string {
		return $this->mineDisplay;
	}

	public function onAreaEnter(ClassicPrisonPlayer $player, BaseArea $oldArea = null) {
		if(!($oldArea instanceof MineArea and $oldArea->getMineDisplay() === $this->getMineDisplay())) {
			$player->sendMessage(LanguageUtils::translateColors("&6- &aYou have entered &r{$this->mineDisplay}&r&a mine!"));
		}
	}

}