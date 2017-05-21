<?php

/**
 * ClassicPrison – ClassicPrisonPlayer.php
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

namespace classicprison;

use core\CorePlayer;
use core\language\LanguageManager;
use classicprison\arena\Arena;
use core\Utils;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\item\Potion;
use pocketmine\plugin\PluginException;

class ClassicPrisonPlayer extends CorePlayer {

	/** @var int */
	private $killStreak = 0;

	/** @var ClassicPrisonPlayer */
	private $lastPlayerDamager;

	/** @var float */
	private $lastPlayerDamageTime;

	/** @var Main */
	private $plugin = null;

	/**
	 * @return int
	 */
	public function getKillStreak() {
		return $this->killStreak;
	}

	/**
	 * Add a kill to the kill streak and give rewards
	 */
	public function updateKillStreak() {
		$this->killStreak++;
		$this->sendTranslatedMessage("KILL_STREAK_UPDATE", [$this->getKillStreak()], true);
		$killMessage = $this->getCore()->getLanguageManager()->translateForPlayer($this, "KILL_MESSAGE_" . (string)mt_rand(1, 5));
		if($this->killStreak < 3) {
			$health = 4 + (1 * $this->getKillStreak());
			$this->sendTranslatedMessage("HEALTH_GAIN", [$killMessage, $health / 2], true);
			$this->heal($health, ($ev = new EntityRegainHealthEvent($this, $health, EntityRegainHealthEvent::CAUSE_CUSTOM)));
			$this->server->getPluginManager()->callEvent($ev);
		} elseif($this->killStreak >= 3 and $this->killStreak < 10) {
			if(rand(1, 6) >= 5) {
				$this->getInventory()->addItem(Item::get(Item::SPLASH_POTION, Potion::HEALING, 1));
				$this->sendTranslatedMessage("BONUS_ITEM", [$killMessage, "1", Utils::translateColors("&l&4Health Potion")], true);
			} else {
				$amount = mt_rand(1, 6);
				$this->getInventory()->addItem(Item::get(Item::ARROW, 0, $amount));
				$this->sendTranslatedMessage("BONUS_ITEM", [$killMessage, "{$amount}", Utils::translateColors("&l&7Arrow")], true);
			}
		} elseif($this->killStreak >= 10) {
			if(rand(1, 6) >= 5) {
				$this->getInventory()->addItem(Item::get(Item::GOLDEN_APPLE, 0, 1));
				$this->sendTranslatedMessage("BONUS_ITEM", [$killMessage, "1", Utils::translateColors("&l&6Golden Apple")], true);
			} else {
				$amount = mt_rand(1, 4);
				$this->getInventory()->addItem(Item::get(Item::STEAK, 0, $amount));
				$this->sendTranslatedMessage("BONUS_ITEM", [$killMessage, "{$amount}", Utils::translateColors("&l&5Steak")], true);
			}
		}
	}

	/**
	 * Set the kill streak back to 0
	 */
	public function resetKillStreak() {
		$this->killStreak = 0;
	}

	public function attack($damage, EntityDamageEvent $source) {
		parent::attack($damage, $source);
	}

	public function kill($forReal = false) {
		$this->lastPlayerDamager = null;
		$this->removeAllEffects();
		$this->extinguish();
		$this->resetKillStreak();
		parent::kill($forReal);
	}

	public function initEntity() {
		parent::initEntity();
		$plugin = $this->server->getPluginManager()->getPlugin("ClassicPrison");
		if($plugin instanceof Main) {
			$this->plugin = $plugin;
		} else {
			throw new PluginException("ClassicPrison plugin isn't loaded!");
		}
	}
}