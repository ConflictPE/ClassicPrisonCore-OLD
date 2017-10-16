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
use classicprison\mine\Mine;
use core\language\LanguageUtils;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class MineArea extends SafeArea {

	/** @var string */
	private $mineName;

	public function __construct(AreaManager $manager, Level $level, Vector3 $a, Vector3 $b, string $mineName) {
		$this->mineName = $mineName;
		parent::__construct($manager, $level, $a, $b);
	}

	public function getMine() : Mine{
		return $this->getManager()->getPlugin()->getMineManager()->getMine($this->mineName);
	}

	public function onAreaEnter(ClassicPrisonPlayer $player, BaseArea $oldArea = null) {
		if(!($oldArea instanceof MineArea and $oldArea->getMine() === $this->getMine())) {
			$player->sendMessage(LanguageUtils::translateColors("&6- &aYou have entered &r{$this->getMine()->getDisplay()}&r&a mine!"));
		}
	}

}