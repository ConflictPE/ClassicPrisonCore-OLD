<?php

/**
 * ClassicPrisonCore – WarpCommand.php
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
 * Created on 15/9/17 at 4:37 PM
 *
 */

namespace conflict\classicprison\command;

use conflict\classicprison\Main;
use conflict\classicprison\util\traits\ClassicPrisonPluginReference;
use conflict\classicprison\warp\Warp;
use core\command\CoreUserCommand;
use core\CorePlayer;
use core\language\LanguageUtils;

class WarpCommand extends CoreUserCommand {

	use ClassicPrisonPluginReference;

	public function __construct(Main $plugin) {
		$this->setClassicPrison($plugin);
		parent::__construct($plugin->getCore(), "warp", "Warp command", "/warp [mines|list|{warp name}]", ["warps"]);
	}

	public function onRun(CorePlayer $player, array $args) {
		$warpManager = $this->getClassicPrison()->getWarpManager();
		if(isset($args[0])) { // targeting a specific warp
			switch($warpName = strtolower($args[0])) {
				case "mines":
					// TODO: Add warp mine GUI + UI
					$player->sendMessage(LanguageUtils::translateColors(str_replace(["- {BLANK}&r\n", "&r\n- {BLANK}"], "","&6=-----= &l&eAvailable warps&r &6=-----=&r\n- " . implode("&r\n- ", array_map(function(Warp $warp) {
						if($warp->isMineWarp()) return $warp->getDisplay();
						return "{BLANK}";
					},$warpManager->getWarps())))));
					break;
				case "list":
					// TODO: Add warp list GUI + UI
					$player->sendMessage(LanguageUtils::translateColors(str_replace(["- {BLANK}&r\n", "&r\n- {BLANK}"], "","&6=-----= &l&eAvailable warps&r &6=-----=&r\n- " . implode("&r\n- ", array_map(function(Warp $warp) {
						if($warp->isGenericWarp()) return $warp->getDisplay();
						return "{BLANK}";
					},$warpManager->getWarps())))));
					break;
				default:
					if($warpManager->warpExists($warpName)) {
						$warpManager->getWarp($warpName)->warpPlayer($player);
					} else {
						$player->sendMessage(LanguageUtils::translateColors("&aThat warp does not exist!"));
					}
					break;
			}
		} else { // list warps
			// TODO: Add warp list GUI + UI
			$player->sendMessage(LanguageUtils::translateColors("&6=-----= &l&eAvailable warps&r &6=-----=&r\n- " . implode("&r\n- ", array_map(function(Warp $warp) {
				return $warp->getDisplay();
			},$warpManager->getWarps()))));
		}
		return true;
	}

}