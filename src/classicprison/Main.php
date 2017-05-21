<?php

/**
 * ClassicPrison – Main.php
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

use classicprison\command\RestoreKitCommand;
use classicprison\command\SilentMessageCommand;
use core\entity\text\FloatingTextManager;
use core\entity\text\UpdatableFloatingText;
use core\language\LanguageManager;
use core\Utils;
use classicprison\arena\ArenaManager;
use classicprison\command\HubCommand;
use classicprison\entity\npc\NPCManager;
use classicprison\task\UpdateInfoTextTask;
use pocketmine\item\Item;
use pocketmine\item\Potion;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\utils\Config;

class Main extends PluginBase {

	/** @var \core\Main */
	private $components;

	/** @var ClassicPrisonListener */
	private $listener;

	/** @var Config */
	private $settings;

	/** @var NPCManager */
	private $npcManager;

	/** @var Main */
	public static $instance = null;

	/** @var Item[] */
	protected $lobbyItems = [];

	/** @var array */
	public static $languages = [
		"en" => "english.json"
	];

	const MESSAGES_FILE_PATH = "lang" . DIRECTORY_SEPARATOR . "messages" . DIRECTORY_SEPARATOR;

	public function onEnable() {
		Main::$instance = $this;
		$components = $this->getServer()->getPluginManager()->getPlugin("Components");
		if(!$components instanceof \core\Main) throw new PluginException("Components plugin isn't loaded!");
		$this->components = $components;
		if(!is_dir($this->getDataFolder() . "data")) @mkdir($this->getDataFolder() . "data");
		if(!is_dir($this->getDataFolder() . "data" . DIRECTORY_SEPARATOR . "skins")) @mkdir($this->getDataFolder() . "data" . DIRECTORY_SEPARATOR . "skins");
		$this->loadConfigs();
		$this->setListener();
		$this->setNpcManager();
		$this->registerCommands();
		$this->getServer()->getNetwork()->setName($components->getLanguageManager()->translate("SERVER_NAME", "en"));
	}

	public function loadConfigs() {
		$this->saveResource("Settings.yml");
		$this->settings = new Config($this->getDataFolder() . "Settings.yml",  Config::YAML);
		$path = $this->getDataFolder() . self::MESSAGES_FILE_PATH;
		if(!is_dir($path)) @mkdir($path);
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

	protected function registerCommands() {
		$this->components->getCommandMap()->registerAll([
			new HubCommand($this),
			new SilentMessageCommand($this),
		]);
	}

	/**
	 * @return Main
	 */
	public static function getInstance() {
		return self::$instance;
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
	 * Give a player an array of items and order them correctly in their hot bar
	 *
	 * @param Player $player
	 * @param Item[] $items
	 * @param bool $shouldCloneItems
	 */
	public static function giveItems(Player $player, array $items, $shouldCloneItems = false) {
		for($i = 0, $hotbarIndex = 0, $invIndex = 0, $inv = $player->getInventory(), $itemCount = count($items); $i < $itemCount; $i++, $invIndex++) {
			$inv->setItem($invIndex, ($shouldCloneItems ? clone $items[$i] : $items[$i]));
			if($hotbarIndex <= 9) {
				$inv->setHotbarSlotIndex($hotbarIndex, $invIndex);
				$hotbarIndex++;
			}
			continue;
		}
		$inv->sendContents($player);
	}

}