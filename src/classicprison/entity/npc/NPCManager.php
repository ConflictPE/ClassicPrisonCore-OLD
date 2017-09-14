<?php
/**
 * ClassicPrison – NPCManager.php
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

use classicprison\Main;
use core\entity\npc\HumanNPC;
use core\Utils;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\utils\Config;

class NPCManager {

	const DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "NPCs.json";
	const TYPE_COMMAND = "command";

	/* Path to where the NPC data file is stored */
	const TYPE_DISPLAY = "display";

	/* NPC types */
	/** @var HumanNPC[] */
	public $spawned = [];
	/** @var Main */
	private $plugin;

	/**
	 * NPCManager constructor
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$plugin->saveResource(self::DATA_FILE_PATH);
		Entity::registerEntity(DisplayNPC::class, true);
		Entity::registerEntity(CommandNPC::class, true);
		$this->spawnFromData();
	}

	/**
	 * @return Main
	 */
	public function getPlugin() {
		return $this->plugin;
	}

	public function spawn(Position $pos, $name, $skin, $skinName = "custom", $yaw = 180, $pitch = 0, $type = "display", $extraData = []) {
		$location = new Location($pos->x, $pos->y, $pos->z, $yaw, $pitch, $pos->getLevel());
		switch($type) {
			case self::TYPE_DISPLAY:
				return DisplayNPC::spawn("DisplayNPC", $location, $name, $skin, $skinName, $this->makeNBT($pos), $extraData["text"]);
			case self::TYPE_COMMAND:
				return CommandNPC::spawn("CommandNPC", $location, $name, $skin, $skinName, $this->makeNBT($pos), $extraData["commands"]);
		}
	}

	public function makeNBT(Vector3 $pos) {
		return new Compound("", [
			"Pos" => new Enum("Pos", [
				new DoubleTag("", $pos->x),
				new DoubleTag("", $pos->y),
				new DoubleTag("", $pos->z),
			]),
			"Motion" => new Enum("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0),
			]),
			"Rotation" => new Enum("Rotation", [
				new FloatTag("", 180),
				new FloatTag("", 0),
			]),
		]);
	}

	/**
	 * @return \core\entity\npc\HumanNPC[]
	 */
	public function getSpawned() {
		return $this->spawned;
	}

	private function spawnFromData() {
		$data = (new Config($this->plugin->getDataFolder() . self::DATA_FILE_PATH))->getAll();
		foreach($data as $npc) {
			try {
				$path = "data" . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . $npc["skin-file"];
				$this->plugin->saveResource($path);
				$npc["skin"] = file_get_contents($this->plugin->getDataFolder() . $path);
				$npc["skinName"] = "Standard_Custom";
			} catch(\ArrayOutOfBoundsException $e) {
				$npc["skin"] = "";
				$npc["skinName"] = "Standard_Custom";
			}
			$this->spawn(Utils::parsePosition($npc["pos"]), Utils::translateColors($npc["name"]), $npc["skin"], $npc["skinName"], $npc["yaw"], $npc["pitch"], $npc["type"], $npc["data"]);
		}
	}

}