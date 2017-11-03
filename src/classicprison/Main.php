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

use core\Utils;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\PluginException;

class Main extends PluginBase {

	/** @var \core\Main */
	private $components;

	/** @var ClassicPrisonListener */
	private $listener;

	/** @var Config */
	private $settings;

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
		Utils::$centerWelcome = true;

		$components = $this->getServer()->getPluginManager()->getPlugin("Components");
		if(!$components instanceof \core\Main) throw new PluginException("Components plugin isn't loaded!");
		$this->components = $components;
		$this->loadConfigs();
		$this->setListener();
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
	 * Set the listener
	 */
	public function setListener() {
		$this->listener = new ClassicPrisonListener($this);
	}

}