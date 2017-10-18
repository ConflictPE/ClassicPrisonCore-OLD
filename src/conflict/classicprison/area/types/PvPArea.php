<?php

/**
 * ClassicPrisonCore – PvPArea.php
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
 * Created on 23/9/17 at 10:23 PM
 *
 */

namespace conflict\classicprison\area\types;

use conflict\classicprison\area\BaseArea;
use conflict\classicprison\ClassicPrisonPlayer;
use core\language\LanguageUtils;

class PvPArea extends BaseArea {

	public function onAreaEnter(ClassicPrisonPlayer $player, BaseArea $oldArea = null) {
		if(!($oldArea instanceof PvPArea)) {
			if($oldArea instanceof SafeArea) {
				$player->sendMessage(LanguageUtils::translateColors("&6- &cYou have entered a pvp area!"));
			}
		}
	}

}