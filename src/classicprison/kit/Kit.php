<?php

/**
 * ClassicPrisonCore – Kit.php
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
 * Created on 14/9/17 at 10:06 PM
 *
 */

namespace classicprison\kit;

use core\exception\InvalidConfigException;
use core\language\LanguageUtils;
use core\Utils;
use pocketmine\entity\Effect;
use pocketmine\item\Item;
use pocketmine\Player;

class Kit {

	/**
	 * Load a kit from an array
	 *
	 * @param string $name
	 * @param array $data
	 *
	 * @return Kit
	 */
	public static function fromData(string $name, array $data) : Kit {
		try {
			return new Kit($name, $data["display"] ?? $name, array_map("core\Utils::parseItem", $data["items"]), Utils::parseItem($data["helmet"]), Utils::parseItem($data["chestplate"]), Utils::parseItem($data["leggings"]), Utils::parseItem($data["boots"]), array_map("core\Utils::parseEffect", $data["effects"]), Utils::parseCooldown($data["cooldown"]));
		} catch(\Throwable $e) {
			throw new InvalidConfigException("Could not load kit from data! Error: ". (new \ReflectionObject($e))->getShortName());
		}
	}

	/** @var string */
	private $name = "";

	/** @var string */
	private $display = "";

	/** @var Item[] */
	private $items = [];

	/** @var Item|null */
	private $helmet;

	/** @var Item|null */
	private $chestplate;

	/** @var Item|null */
	private $leggings;

	/** @var Item|null */
	private $boots;

	/** @var Effect[] */
	private $effects = [];

	/** @var int */
	private $cooldown = 0;

	/** @var int[] */
	private $activeCooldowns = [];

	public function  __construct(string $name, string $display, array $items, Item $helmet = null, Item $chestplate = null, Item $leggings = null, Item $boots = null, array $effects = [], int $cooldown = 0) {
		$this->name = $name;
		$this->display = $display;
		$this->items = $items;
		$this->helmet = $helmet;
		$this->chestplate = $chestplate;
		$this->leggings = $leggings;
		$this->boots = $boots;
		$this->effects = $effects;
		$this->cooldown = $cooldown;
	}

	/**
	 * @param array $value
	 */
	public function setActiveCooldowns(array $value = []) {
		$this->activeCooldowns = $value;
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDisplay() : string {
		return $this->display;
	}

	/**
	 * @return int[]
	 */
	public function getActiveCooldowns() : array {
		return $this->activeCooldowns;
	}

	public function applyTo(Player $player) {
		$name = strtolower($player->getName());
		$time = time();
		if(!($hasCooldown = isset($this->activeCooldowns[$name])) or ($hasCooldown and $this->activeCooldowns[$name] + $this->cooldown < $time)) {
			$player->sendMessage(LanguageUtils::translateColors("&6- &aApplying {$this->getDisplay()}&r kit!"));
			$this->activeCooldowns[$name] = $time;
			$inv = $player->getInventory();
			$itemsToDrop = [];
			foreach($this->items as $item) {
				if($inv->canAddItem($item)) {
					$inv->addItem(clone $item);
				} else {
					$itemsToDrop[] = $item;
				}
			}
			if($inv->getHelmet() instanceof Item and $inv->getHelmet()->getId() !== Item::AIR) {
				if($inv->canAddItem($this->helmet)) {
					$inv->addItem(clone $this->helmet);
				} else {
					$itemsToDrop[] = $this->helmet;
				}
			} else {
				$inv->setHelmet(clone $this->helmet);
			}
			if($inv->getChestplate() instanceof Item and $inv->getChestplate()->getId() !== Item::AIR) {
				if($inv->canAddItem($this->chestplate)) {
					$inv->addItem(clone $this->chestplate);
				} else {
					$itemsToDrop[] = $this->chestplate;
				}
			} else {
				$inv->setChestplate(clone $this->chestplate);
			}
			if($inv->getLeggings() instanceof Item and $inv->getLeggings()->getId() !== Item::AIR) {
				if($inv->canAddItem($this->leggings)) {
					$inv->addItem(clone $this->leggings);
				} else {
					$itemsToDrop[] = $this->leggings;
				}
			} else {
				$inv->setLeggings(clone $this->leggings);
			}
			if($inv->getBoots() instanceof Item and $inv->getBoots()->getId() !== Item::AIR) {
				if($inv->canAddItem($this->boots)) {
					$inv->addItem(clone $this->boots);
				} else {
					$itemsToDrop[] = $this->boots;
				}
			} else {
				$inv->setBoots(clone $this->boots);
			}
			if(count($itemsToDrop) > 0) {
				foreach($itemsToDrop as $item) {
					$player->getLevel()->dropItem($player->asVector3(), clone $item);
				}
				$player->sendMessage(LanguageUtils::translateColors("&c- &6Some items could not fit into your inventory so they were dropped at your feet!"));
			}
			foreach($this->effects as $effect) {
				$player->addEffect(clone $effect);
			}
		} else {
			$dateTime = new \DateTime("NOW", new \DateTimeZone("GMT"));
			$dateTime->setTimestamp($this->activeCooldowns[$name] + $this->cooldown - $time);
			$weeks = floor($dateTime->getTimestamp() / 604800); // get weeks by: timestamp / (60 * 60 * 24 * 7)
			$days = floor($dateTime->getTimestamp() / 86400 -  ($weeks > 0 ? $weeks * 7 : 0)); // get days by: (timestamp / (60 * 60 * 24) - weeks * 7
			$hours = (int) $dateTime->format("G");
			$minutes = (int) $dateTime->format("i");
			$seconds = (int) $dateTime->format("s");
			$player->sendMessage(LanguageUtils::translateColors(rtrim("&c- &6Kit is on cooldown for:" . ($weeks > 0 ? " {$weeks} week" . ($weeks == 1 ? "," : "s,") : "") . ($days > 0 ? " {$days} day" . ($days == 1 ? "," : "s,") : "") . ($hours > 0 ? " {$hours} hour" . ($hours == 1 ? "," : "s,") : "") . ($minutes > 0 ? " {$minutes} minute" . ($minutes == 1 ? "," : "s,") : "") . ($seconds > 0 ? " {$seconds} second" . ($seconds == 1 ? "," : "s,") : ""),","))); // time until cooldown finishes
		}
	}

}