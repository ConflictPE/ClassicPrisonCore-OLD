<?php

/**
 * ClassicPrisonCore – MineCommand.php
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
 * Created on 18/10/17 at 5:51 PM
 *
 */

namespace classicprison\command;

use conflict\classicprison\Main;
use conflict\classicprison\mine\Mine;
use conflict\classicprison\util\traits\ClassicPrisonPluginReference;
use core\command\CoreUserCommand;
use core\CorePlayer;
use core\language\LanguageUtils;

class MineCommand extends CoreUserCommand {

	use ClassicPrisonPluginReference;

	public function __construct(Main $plugin) {
		$this->setClassicPrison($plugin);
		parent::__construct($plugin->getCore(), "warp", "Warp command", "/warp [mines|list|{warp name}]", ["warps"]);
	}

	public function onRun(CorePlayer $player, array $args) {
		$mineManager = $this->getClassicPrison()->getMineManager();
		if(isset($args[0])) { // targeting a specific mine
			$mine = $mineManager->getMine(strtolower($args[0]));
			if($mine instanceof Mine) {
				$player->teleport($mine->getWarp()->getPosition());
				$player->sendMessage(LanguageUtils::translateColors("&6- &aWarp you to {$mine->getDisplay()} successfully!"));
			} else {
				$player->sendMessage(LanguageUtils::translateColors("&aThat mine does not exist!"));
			}
		} else { // teleport to their current mine
			$mine = $mineManager->getMine("a");
			if($mine instanceof Mine) {
				$player->teleport($mine->getWarp()->getPosition());
				$player->sendMessage(LanguageUtils::translateColors("&6- &aWarp you to {$mine->getDisplay()} successfully!"));
			} else {
				$player->sendMessage(LanguageUtils::translateColors("&aThat mine does not exist!"));
			}
		}
		return true;
	}

}