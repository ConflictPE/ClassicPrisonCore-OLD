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
use core\language\LanguageUtils;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\network\protocol\v120\InventoryContentPacket;
use pocketmine\network\protocol\v120\InventorySlotPacket;
use pocketmine\Player;
use pocketmine\utils\PluginException;
use pocketmine\utils\TextFormat;

class ClassicPrisonPlayer extends CorePlayer {

	/** @var Main */
	private $plugin = null;

	/** @var string */
	private $lastChatFormat = "";

	/**
	 * @return Main
	 */
	public function getPlugin() {
		return $this->plugin;
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

	public function setAuthenticated($authenticated = true) {
		parent::setAuthenticated($authenticated);

		$this->sendAllInventories();

		$this->setStatus(self::STATE_PLAYING); // let other plugins handle things like damage once authenticated
	}

	public function messageCheckCallback(string $message) {
		$baseMessage = $this->lastChatFormat;
		$params = [$this->getDisplayName(), $message];

		foreach($params as $i => $p){
			$baseMessage = str_replace("{%$i}", (string) $p, $baseMessage);
		}

		$baseMessage = str_replace("%0", "", $baseMessage); //fixes a client bug where %0 in translation will cause freeze

		foreach($this->getServer()->getOnlinePlayers() as $p) {
			$p->sendMessage($baseMessage);
		}
	}

	public function afterAuthCheck() {
		$this->addTitle(LanguageUtils::translateColors("&eWelcome to &r&l&6Conflict&7PE &fPrison&r&e!"), TextFormat::GRAY . ($this->isAuthenticated() ? "Do /mines to start mining!" : ($this->isRegistered() ? "Login to start playing!" : "Follow the prompts to register!")), 10, 100, 10);

		$pk = new LevelEventPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->evid = LevelEventPacket::EVENT_SOUND_CLICK_FAIL;
		$pk->data = 0;
		$this->dataPacket($pk);
	}

	public function onChat(PlayerChatEvent $event) {
		parent::onChat($event);

		$this->lastChatFormat = $event->getFormat();
	}

	public function onMove(PlayerMoveEvent $event) {
		if(!$this->isAuthenticated()) {
			$event->setCancelled(true);
			return;
		}

		parent::onMove($event);
	}

	public function onDrop(PlayerDropItemEvent $event) {
		if(!$this->isAuthenticated()) {
			$event->setCancelled(true);
		}
	}

	public function onBreak(BlockBreakEvent $event) {
		if(!$this->isAuthenticated()) {
			$event->setCancelled(true);
		}
	}

	public function onPlace(BlockPlaceEvent $event) {
		if(!$this->isAuthenticated()) {
			$event->setCancelled(true);
		}
	}

	public function kill($forReal = false) {
		return Player::kill();
	}

	/**
	 * Mask the players inventory until they're authenticated
	 *
	 * @param DataPacket $packet
	 * @param bool $needACK
	 *
	 * @return bool|int
	 */
	public function dataPacket(DataPacket $packet, $needACK = false) {
		if(!$this->isAuthenticated() and ($packet instanceof ContainerSetContentPacket or $packet instanceof InventoryContentPacket or $packet instanceof ContainerSetSlotPacket or $packet instanceof InventorySlotPacket)) {
			return true;
		}

		return parent::dataPacket($packet, $needACK);
	}

	public function sendCommandData() {
		Player::sendCommandData();
	}
}