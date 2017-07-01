<?php

/**
 * ClassicPrisonCore – HubCommand.php
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
 * Created on 29/01/2017 at 4:46 PM
 *
 */

namespace classicprison\command;

use classicprison\ClassicPrisonPlayer;
use classicprison\Main;
use core\command\CoreUserCommand;
use core\CorePlayer;

class HubCommand extends CoreUserCommand {

	public function __construct(Main $plugin) {
		parent::__construct($plugin->getCore(), "hub", "Returns you to the hub", "/hub", ["spawn", "lobby"]);
	}

	public function onRun(CorePlayer $player, array $args) {
		/** @var ClassicPrisonPlayer $player */
		$player->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
		$player->sendTranslatedMessage("HUB_COMMAND", [], true);
		return true;
	}

}