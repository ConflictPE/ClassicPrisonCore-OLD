<?php

/**
 * ClassicPrisonCore – KitCommand.php
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
 * Created on 14/9/17 at 11:21 PM
 *
 */

namespace conflict\classicprison\command;

use conflict\classicprison\ClassicPrisonPlayer;
use conflict\classicprison\kit\Kit;
use conflict\classicprison\Main;
use conflict\classicprison\util\traits\ClassicPrisonPluginReference;
use core\command\CoreUserCommand;
use core\CorePlayer;
use core\language\LanguageUtils;

class KitCommand extends CoreUserCommand {

	use ClassicPrisonPluginReference;

	public function __construct(Main $plugin) {
		$this->setClassicPrison($plugin);
		parent::__construct($plugin->getCore(), "kit", "Main kit command", "/kit [kit name]", ["kits"]);
	}

	public function onRun(CorePlayer $player, array $args) {
		$kitManager = $this->getClassicPrison()->getKitManager();
		/** @var ClassicPrisonPlayer $player */
		if(isset($args[0])) { // user is trying to apply a kit
			if($kitManager->kitExists(strtolower($args[0]))) {
				$kitManager->getKit($args[0])->applyTo($player);
			} else {
				$player->sendMessage(LanguageUtils::translateColors("&aThat kit does not exist!"));
			}
		} else { // list kits
			$player->sendMessage(LanguageUtils::translateColors("&6=-----= &l&eAvailable kits&r &6=-----=&r\n- " . implode("&r\n- ", array_map(function(Kit $kit) {
				return $kit->getDisplay();
			}, $kitManager->getKits()))));
		}
		return true;
	}

}