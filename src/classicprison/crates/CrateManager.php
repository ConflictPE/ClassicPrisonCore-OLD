<?php

/**
 * ClassicPrisonCore – CrateManager.php
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
 * Created on 23/9/17 at 10:17 PM
 *
 */

namespace classicprison\crates;

use classicprison\Main;
use classicprison\util\traits\ClassicPrisonPluginReference;

class CrateManager {

	use ClassicPrisonPluginReference;

	private $cratesPool = [];

	const CRATES_DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "crates.json";

	public function __construct(Main $plugin) {
		$this->setClassicPrison($plugin);
		$this->plugin = $plugin;
	}

}