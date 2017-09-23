<?php

/**
 * ClassicPrisonCore – ClassicPrisonPlayer.php
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

use classicprison\area\BaseArea;
use core\CorePlayer;
use pocketmine\utils\PluginException;

class ClassicPrisonPlayer extends CorePlayer {

	/** @var int */
	private $areaId = -1;

	/** @var Main */
	private $plugin = null;

	/**
	 * @return BaseArea|null
	 */
	public function getArea() {
		return $this->plugin->getAreaManager()->getArea($this->areaId);
	}

	public function setArea(BaseArea $area) {
		$oldArea = $this->getArea();
		$this->areaId = $area->getId();
		$area->onAreaEnter($this, $oldArea);
	}

	public function initEntity() {
		parent::initEntity();
		$plugin = $this->server->getPluginManager()->getPlugin("ClassicPrison");
		if($plugin instanceof Main) {
			$this->plugin = $plugin;
		} else {
			throw new PluginException("ClassicPrison plugin isn't loaded!");
		}
	}

}