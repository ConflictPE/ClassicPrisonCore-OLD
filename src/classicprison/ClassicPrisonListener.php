<?php

/**
 * ClassicPrisonCore – ClassicPrisonListener.php
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

use classicprison\mines\Mine;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Location;
use pocketmine\utils\TextFormat;

class ClassicPrisonListener implements Listener {

	/** @var Main */
	private $plugin;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	/**
	 * @return Main
	 */
	public function getPlugin() {
		return $this->plugin;
	}

	public function onJoin(PlayerJoinEvent $event) {
	}

	/**
	 * @param PlayerCreationEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onPlayerCreation(PlayerCreationEvent $event) {
		$event->setPlayerClass(ClassicPrisonPlayer::class);
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function onDeath(PlayerDeathEvent $event) {
		$event->setDeathMessage("");
		$event->setDrops([]);
	}

	public function onQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$player->kill();
	}

	public function onKick(PlayerKickEvent $event) {
		$player = $event->getPlayer();
		$player->kill();
	}

	/**
	 * @priority HIGH
	 *
	 * @param PlayerMoveEvent $event
	 */
	public function onMove(PlayerMoveEvent $event) {
		/** @var $mine Mine */
		if(($mine = $this->plugin->getMineManager()->isMineResettingAtPosition($event->getTo())) instanceof Mine) {
			$event->getPlayer()->sendMessage(TextFormat::RED . "A mine is currently resetting in this area. You may not move here." . TextFormat::RESET);
			$event->setTo(new Location($mine->getMineSpawn()->x, $mine->getMineSpawn()->y, $mine->getMineSpawn()->z, $event->getTo()->yaw, $event->getTo()->pitch, $mine->getMineSpawn()->getLevel()));
		}
	}

	/**
	 * @priority HIGH
	 *
	 * @param BlockPlaceEvent $event
	 */
	public function onBlockPlace(BlockPlaceEvent $event) {
		if($this->plugin->getMineManager()->isMineResettingAtPosition($event->getBlock()) instanceof Mine) {
			$event->getPlayer()->sendMessage(TextFormat::RED . "A mine is currently resetting in this area. You may not place blocks." . TextFormat::RESET);
			$event->setCancelled();
		}
	}

	/**
	 * @priority HIGH
	 *
	 * @param BlockBreakEvent $event
	 */
	public function onBlockDestroy(BlockBreakEvent $event) {
		if($this->plugin->getMineManager()->isMineResettingAtPosition($event->getBlock()) instanceof Mine) {
			$event->getPlayer()->sendMessage(TextFormat::RED . "A mine is currently resetting in this area. You may not break blocks." . TextFormat::RESET);
			$event->setCancelled();
		}
	}

}