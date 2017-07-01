<?php

namespace classicprison\kits;

use classicprison\ClassicPrisonPlayer;
use pocketmine\item\Item;
use pocketmine\Player;

class ClassicPrisonKit {

	/** @var  ClassicPrisonKitManager */
	private $manager;

	/** @var  int */
	private $id;

	/** @var  string */
	private $name;

	/** @var  Item[] */
	private $items;

	/** @var  Item[] */
	private $armor;

	/** @var bool */
	private $vip;

	/** @var  int */
	private $cooldown;

	/** @var  int[] */
	private $pool;

	/**
	 * ClassicPrisonKit constructor.
	 *
	 * @param ClassicPrisonKitManager $manager
	 * @param int $id
	 * @param String $name
	 * @param array $items
	 * @param array $armor
	 * @param int $cooldown
	 * @param bool $vip
	 */
	public function __construct(ClassicPrisonKitManager $manager, int $id, String $name, array $items, array $armor, int $cooldown, bool $vip = false) {
		$this->manager = $manager;
		$this->id = $id;
		$this->name = $name;
		$this->items = $items;
		$this->armor = $armor;
		$this->cooldown = $cooldown;
		$this->vip = $vip;
		$this->loadCooldowns();
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * @param ClassicPrisonPlayer $player
	 */
	public function requestKit(ClassicPrisonPlayer $player) {
		if($this->hasAccess($player)) {
			if(!$this->checkCooldown($player->getName())) {
				if($this->checkInv($player)) {
					$this->sendToPlayer($player);
					$player->sendTranslatedMessage("KIT_USE", [$this->getName()], true);
				} else {
					$player->sendTranslatedMessage("KIT_INV_FULL", [], true);
				}
			} else {
				$player->sendTranslatedMessage("KIT_USE_COOLDOWN", [$this->getPlayerCooldown($player->getName())], true);
			}
		} else {
			$player->sendTranslatedMessage("KIT_USE_PERMISSION", [$this->getName()], true);
		}
	}

	/**
	 * @return int
	 */
	public function getKitCooldown() : int {
		return $this->cooldown;
	}

	/**
	 * @param String $name
	 *
	 * @return int
	 */
	public function getPlayerCooldown(String $name) : int {
		if($this->checkCooldown($name)) {
			$time = $this->pool[strtolower($name)];
			return ($time - time()) / 60 + 1;
		} else {
			return 0;
		}
	}

	/**
	 * @param String $name
	 */
	public function startPlayerCooldown(String $name) {
		$this->pool[strtolower($name)] = time() + $this->cooldown;
	}

	/**
	 * @param String $name
	 *
	 * @return bool
	 */
	public function checkCooldown(String $name) : bool {
		if(isset($this->pool[strtolower($name)])) {
			$time = $this->pool[strtolower($name)];
			if($time <= time()) {
				unset($this->pool[strtolower($name)]);
			}
		}
		return isset($this->pool[strtolower($name)]);
	}

	public function saveCooldowns() {
		if(count($this->pool) > 0) {
			file_put_contents($this->manager->getPlugin()->getDataFolder() . "cooldowns/" . strtolower($this->name) . ".kit", serialize($this->pool));
		}
	}

	public function loadCooldowns() {
		@mkdir($this->manager->getPlugin()->getDataFolder() . "cooldowns");
		if(file_exists($this->manager->getPlugin()->getDataFolder() . "cooldowns/" . strtolower($this->name) . ".kit")) {
			$this->pool = unserialize(file_get_contents($this->manager->getPlugin()->getDataFolder() . "cooldowns/" . strtolower($this->name) . ".kit"));
		}
		print_r($this->pool);
	}

	/**
	 * @param ClassicPrisonPlayer $player
	 *
	 * @return bool
	 */
	public function hasAccess(ClassicPrisonPlayer $player) : bool {
		return $this->vip ? $player->isVip() : true;
	}

	/**
	 * @param Player $player
	 */
	public function sendToPlayer(Player $player) {
		$inventory = $player->getInventory();
		$inventory->setContents($this->items);
		$inventory->setHelmet($this->armor["helmet"]);
		$inventory->setChestplate($this->armor["chestplate"]);
		$inventory->setLeggings($this->armor["leggings"]);
		$inventory->setBoots($this->armor["boots"]);
		$this->startPlayerCooldown($player->getName());
	}

	public function checkInv(Player $player) {
		foreach($player->getInventory()->getArmorContents() as $armorContent) {
			if($armorContent !== Item::get(0)) {
				return false;
			}
		}
		$space = 0;
		for($i = 0; $i <= $player->getInventory()->getSize(); $i++) {
			if($player->getInventory()->slotContains($i, Item::get(0))) {
				$space++;
				continue;
			}
		}
		if($space <= count($this->items)) {
			return true;
		}
	}

}