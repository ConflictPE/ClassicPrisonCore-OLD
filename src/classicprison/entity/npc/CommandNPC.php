<?php
/**
 * ClassicPrison – CommandNPC.php
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

namespace classicprison\entity\npc;

use classicprison\ClassicPrisonPlayer;
use core\entity\npc\HumanNPC;
use core\Utils;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;

class CommandNPC extends HumanNPC {

	/** @var ConsoleCommandSender */
	protected $commandSender;
	/** @var string[] */
	private $commands = [];

	/**
	 * @param string $shortName
	 * @param Location $pos
	 * @param string $name
	 * @param string $skin
	 * @param string $skinName
	 * @param CompoundTag $nbt
	 * @param string[] $commands
	 *
	 * @return CommandNPC|HumanNPC
	 */
	public static function spawn($shortName, Location $pos, $name, $skin, $skinName, CompoundTag $nbt, array $commands = []) {
		$entity = parent::spawn($shortName, $pos, $name, $skin, $skinName, $nbt);
		if($entity instanceof CommandNPC)
			$entity->commands = $commands;
		$entity->commandSender = new ConsoleCommandSender();
		return $entity;
	}

	/**
	 * Add a command to execute when a player taps the npc
	 *
	 * @param string $command
	 */
	public function addCommand($command = "") {
		$this->commands[] = $command;
	}

	/**
	 * Set the commands to execute when a player taps the bpc
	 *
	 * @param array $commands
	 */
	public function setCommands(array $commands) {
		$this->commands = $commands;
	}

	/**
	 * Get the commands to execute when a player taps an npc
	 *
	 * @return string
	 */
	public function getCommands() {
		return $this->commands;
	}

	public function attack($damage, EntityDamageEvent $source) {
		parent::attack($damage, $source);
		if($source instanceof EntityDamageByEntityEvent) {
			$attacker = $source->getDamager();
			if($attacker instanceof ClassicPrisonPlayer) {
				if($attacker->isAuthenticated()) {
					foreach($this->commands as $command) {
						$this->getCore()->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace([
							"{name}",
							"{player}",
						], [
							$attacker->getName(),
							$attacker->getName(),
						], $command));
					}
				} else {
					$attacker->sendTranslatedMessage("MUST_AUTHENTICATE_FIRST", [], true);
				}
			}
		}
	}

}