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
use classicprison\kits\ClassicPrisonKit;
use classicprison\kits\ClassicPrisonKitManager;
use classicprison\Main;
use core\command\CoreUserCommand;
use core\CorePlayer;

class Kit extends CoreUserCommand {

	/** @var ClassicPrisonKitManager */
	private $manager;

	public function __construct(Main $plugin, ClassicPrisonKitManager $manager) {
		$this->manager = $manager;
		parent::__construct($plugin->getCore(), "kit", "The main command for choosing a kit", "/kit <kitName>", []);
	}

	public function onRun(CorePlayer $player, array $args) {
		/** @var ClassicPrisonPlayer $player */
		switch(count($args)) {
			case 0:
				$this->manager->sendAvailableKits($player);
				break;
			case 1:
				if($this->manager->kitExists($args[0])) {
					/** @var ClassicPrisonKit $kit */
					$kit = $this->manager->getKit($args[0]);
					if($kit != null) {
						$kit->requestKit($player);
					} else {
						$player->sendTranslatedMessage("KIT_NULL", [$args[0]], true);
					}
				}
				break;
		}
	}

}