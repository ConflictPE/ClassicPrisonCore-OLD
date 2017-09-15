<?php

/**
 * ClassicPrisonCore – Main.php
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

use classicprison\command\HubCommand;
use classicprison\command\KitCommand;
use classicprison\command\WarpCommand;
use classicprison\entity\npc\NPCManager;
use classicprison\kit\KitManager;
use classicprison\warp\WarpManager;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\PluginException;

class Main extends PluginBase {

	/** @var Main */
	public static $instance = null;

	/** @var array */
	public static $languages = [
		"en" => "english.json",
	];

	/** @var Item[] */
	protected $lobbyItems = [];

	/** @var \core\Main */
	private $components;

	/** @var ClassicPrisonListener */
	private $listener;

	/** @var Config */
	private $settings;

	/** @var NPCManager */
	private $npcManager;

	/** @var KitManager */
	private $kitManager;

	/** @var WarpManager */
	private $warpManager;

	/** Resource files & paths */
	const MESSAGES_FILE_PATH = "lang" . DIRECTORY_SEPARATOR . "messages" . DIRECTORY_SEPARATOR;
	const MINES_DATA_FILE = "data" . DIRECTORY_SEPARATOR . "mines.json";

	/**
	 * @return Main
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * Give a player an array of items and order them correctly in their hot bar
	 *
	 * @param Player $player
	 * @param Item[] $items
	 * @param bool $replace
	 * @param bool $shouldCloneItems
	 */
	public static function giveItems(Player $player, array $items, bool $replace = true, $shouldCloneItems = false) {
		for($i = 0, $hotbarIndex = 0, $invIndex = 0, $inv = $player->getInventory(), $itemCount = count($items); $i < $itemCount; $i++, $invIndex++) {
			if(!$replace) {
				$inv->addItem($items[$i]);
				continue;
			}
			$inv->setItem($invIndex, ($shouldCloneItems ? clone $items[$i] : $items[$i]));
			if($inv->getItem($invIndex)->getId() == 0)
				if($hotbarIndex <= 9) {
					$inv->setHotbarSlotIndex($hotbarIndex, $invIndex);
					$hotbarIndex++;
				}
			continue;
		}
		$inv->sendContents($player);
	}

	public function onEnable() {
		Main::$instance = $this;
		$components = $this->getServer()->getPluginManager()->getPlugin("Components");
		if(!$components instanceof \core\Main)
			throw new PluginException("Components plugin isn't loaded!");
		$this->components = $components;
		$this->loadConfigs();
		$this->setNpcManager();
		$this->setKitManager();
		$this->setWarpManager();
		$this->setListener(); // register event listener last due to possible dependency on other components
		$this->registerCommands(); // register commands last due to possible dependency on other components
		$this->getServer()->getNetwork()->setName($components->getLanguageManager()->translate("SERVER_NAME", "en"));
	}

	public function onDisable() {
		$this->kitManager->saveKitCooldowns(false);
	}

	public function loadConfigs() {
		if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
		if(!is_dir($this->getDataFolder() . "data")) @mkdir($this->getDataFolder() . "data");
		if(!is_dir($this->getDataFolder() . "lang")) @mkdir($this->getDataFolder() . "lang");
		if(!is_dir($this->getDataFolder() . "data" . DIRECTORY_SEPARATOR . "skins")) @mkdir($this->getDataFolder() . "data" . DIRECTORY_SEPARATOR . "skins");
		$msgPath = $this->getDataFolder() . self::MESSAGES_FILE_PATH;
		if(!is_dir($msgPath)) @mkdir($msgPath);
		$this->saveResource("Settings.yml");
		$this->settings = new Config($this->getDataFolder() . "Settings.yml",  Config::YAML);
		foreach(self::$languages as $lang => $filename) {
			$file = $msgPath . $filename;
			$this->saveResource(self::MESSAGES_FILE_PATH . $filename);
			if(!is_file($file)) {
				echo "Couldn't find language file for '{$lang}'!\nPath: {$file}\n";
			} else {
				$this->components->getLanguageManager()->registerLanguage($lang, (new Config($file, Config::JSON))->getAll());
			}
		}
	}

	/**
	 * Register all the default plugin commands
	 */
	protected function registerCommands() {
		$this->components->getCommandMap()->registerAll([
			new HubCommand($this),
			new KitCommand($this),
			new WarpCommand($this),
		]);
	}

	/**
	 * @return \core\Main
	 */
	public function getCore() {
		return $this->components;
	}

	/**
	 * @return Config
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * @return ClassicPrisonListener
	 */
	public function getListener() {
		return $this->listener;
	}

	/**
	 * @return NPCManager
	 */
	public function getNpcManager() {
		return $this->npcManager;
	}

	/**
	 * @return KitManager
	 */
	public function getKitManager() {
		return $this->kitManager;
	}

	/**
	 * @return WarpManager
	 */
	public function getWarpManager() {
		return $this->warpManager;
	}

	/**
	 * Set the listener
	 */
	public function setListener() {
		$this->listener = new ClassicPrisonListener($this);
	}

	/**
	 * Set the npc manager
	 */
	public function setNpcManager() {
		$this->npcManager = new NPCManager($this);
	}

	/**
	 * Set the kit manager
	 */
	public function setKitManager() {
		$this->kitManager = new KitManager($this);
	}

	/**
	 * Set the warp manager
	 */
	public function setWarpManager() {
		$this->warpManager = new WarpManager($this);
	}

}