<?php

/**
 * ClassicPrisonCore – Crate.php
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
 * Created on 11/12/17 at 8:57 PM
 *
 */

namespace conflict\classicprison\crate;

use conflict\classicprison\crate\loot\BaseLoot;
use conflict\classicprison\Main;
use core\exception\InvalidConfigException;

class Crate {

	public static function fromData(array $data) : Crate {
		try {
			$manager = Main::getInstance()->getCrateManager();
		} catch(\Throwable $e) {
			throw new InvalidConfigException("Could not load crate from data! Error: ". (new \ReflectionObject($e))->getShortName());
		}
	}

	/** @var CrateManager */
	private $manager;

	/** @var BaseLoot[] */
	private $lootPool = [];

	public function __construct(CrateManager $manager) {
		$this->manager = $manager;
	}

	public function getManager() : CrateManager {
		return $this->manager;
	}

	/**
	 * Add loot to the pool
	 *
	 * @param BaseLoot $loot
	 */
	public function addLoot(BaseLoot $loot) {
		$this->lootPool[] = $loot;
	}

	/**
	 * @return BaseLoot[]
	 */
	public function getLoot() : array {
		return $this->lootPool;
	}

}