<?php

/**
 * ClassicPrisonCore – ClassicPrisonPluginReference.php
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
 * Created on 18/10/17 at 5:57 PM
 *
 */

namespace classicprison\util\traits;

use classicprison\Main;

/**
 * Simple trait for providing a reference to the plugins main class
 */
trait ClassicPrisonPluginReference {

	/** @var Main */
	private $plugin;

	protected function setClassicPrison(Main $plugin) {
		$this->plugin = $plugin;
	}

	public function getClassicPrison() : Main {
		return $this->plugin;
	}

}