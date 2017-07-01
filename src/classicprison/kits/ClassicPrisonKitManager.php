<?php
/**
 * Created by PhpStorm.
 * User: omgk45
 * Date: 6/21/2017
 * Time: 2:45 PM
 */

namespace classicprison\kits;

use classicprison\ClassicPrisonPlayer;
use classicprison\command\Kit;
use classicprison\Main;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\Config;

class ClassicPrisonKitManager {

	/** @var  Main */
	private $plugin;

	/** @var  ClassicPrisonKit[] */
	private $kits;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$this->plugin->getCore()->getCommandMap()->registerAll([
			new Kit($this->plugin, $this),
		]);
		$this->loadKits();
	}

	public function onDisable() {
		foreach($this->kits as $kit) {
			$kit->saveCooldowns();
		}
	}

	/**
	 * @return Main
	 */
	public function getPlugin() : Main {
		return $this->plugin;
	}

	public function kitExists(string $name) : bool {
		return isset($this->kits[strtolower($name)]) ? true : false;
	}

	public function getKit(string $name) : ClassicPrisonKit {
		return $this->kits[strtolower($name)];
	}

	public function sendAvailableKits(ClassicPrisonPlayer $player) {
		$string = "";
		foreach($this->kits as $kit) {
			if($kit->hasAccess($player)) {
				$string .= "&a&l" . $kit->getName() . " &r&6: &7" . $kit->getKitCooldown() / 60 . " Minute Cooldown!\n";
			}
		}
		$string = trim(str_replace("&", "§", $string));
		$player->sendTranslatedMessage("KIT_AVAILABLE", [$string], true, true);
	}

	public function loadKits() {
		$this->plugin->saveResource("kits.yml");
		$config = yaml_parse_file($this->plugin->getDataFolder() . "kits.yml");
		foreach($config as $kitName => $kitData) {
			$items = [];
			$armor = [];
			foreach($kitData["items"] as $string) {
				/** @var int[] $data */
				$data = explode(":", $string);
				$item = Item::fromString($data[0] . ":" . $data[2]);
				$item->setCount($data[1]);
				$items[] = $item;
			}
			foreach($kitData["armor"] as $type => $armorType) {
				$item = Item::fromString($armorType);
				$armor[$type] = $item;
			}
			$kit = new ClassicPrisonKit($this, count($this->kits) + 1, $kitName, $items, $armor, $kitData["cooldown"], $kitData["vip"]);
			$this->kits[strtolower($kitName)] = $kit;
		}
	}

}