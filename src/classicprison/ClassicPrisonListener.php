<?php

/**
 * ClassicPrison – ClassicPrisonListener.php
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

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;

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

	public function onJoin(PlayerJoinEvent $event) {}

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

}