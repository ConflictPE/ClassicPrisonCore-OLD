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
use classicprison\command\SilentMessageCommand;
use classicprison\entity\npc\NPCManager;
use classicprison\kits\ClassicPrisonKitManager;
use classicprison\mines\MineManager;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\utils\Config;

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

	/** @var  ClassicPrisonKitManager */
	private $kitManager;

	/** @var MineManager */
	private $mineManager;

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
		if(!is_dir($this->getDataFolder() . "data"))
			@mkdir($this->getDataFolder() . "data");
		if(!is_dir($this->getDataFolder() . "data" . DIRECTORY_SEPARATOR . "skins"))
			@mkdir($this->getDataFolder() . "data" . DIRECTORY_SEPARATOR . "skins");
		$this->loadConfigs();
		$this->setListener();
		$this->setNpcManager();
		$this->setKitManager();
		$this->setMineManager();
		$this->registerCommands();
		$this->getServer()->getNetwork()->setName($components->getLanguageManager()->translate("SERVER_NAME", "en"));
	}

	public function onDisable() {
		$this->getKitManager()->onDisable();
	}

	public function loadConfigs() {
		$this->saveResource("Settings.yml");
		$this->settings = new Config($this->getDataFolder() . "Settings.yml", Config::YAML);
		$path = $this->getDataFolder() . self::MESSAGES_FILE_PATH;
		if(!is_dir($path))
			@mkdir($path);
		foreach(self::$languages as $lang => $filename) {
			$file = $path . $filename;
			$this->saveResource(self::MESSAGES_FILE_PATH . $filename);
			if(!is_file($file)) {
				$this->getLogger()->notice("Couldn't find language file for '{$lang}'!\nPath: {$file}");
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
	 * @return ClassicPrisonKitManager
	 */
	public function getKitManager() {
		return $this->kitManager;
	}

	/**
	 * @return MineManager
	 */
	public function getMineManager() : MineManager {
		return $this->mineManager;
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
		$this->kitManager = new ClassicPrisonKitManager($this);
	}

	/**
	 * Set the mine manager
	 */
	public function setMineManager() {
		$this->mineManager = new MineManager($this);
	}

}