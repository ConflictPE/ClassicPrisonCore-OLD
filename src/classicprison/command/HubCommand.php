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
use classicprison\util\traits\ClassicPrisonPluginReference;
use core\command\CoreUserCommand;
use core\CorePlayer;

class HubCommand extends CoreUserCommand {

	use ClassicPrisonPluginReference;

	public function __construct(Main $plugin) {
		$this->setClassicPrison($plugin);
		parent::__construct($plugin->getCore(), "hub", "Returns you to the hub", "/hub", ["spawn", "lobby"]);
	}

	public function onRun(CorePlayer $player, array $args) {
		/** @var ClassicPrisonPlayer $player */
		$player->teleport($this->getClassicPrison()->getServer()->getDefaultLevel()->getSafeSpawn());
		$player->sendTranslatedMessage("HUB_COMMAND", [], true);
		return true;
	}

}