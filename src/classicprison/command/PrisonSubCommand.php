<?php

/**
 * ClassicPrisonCore – PrisonSubCommand.php
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
 * Created on 1/7/17 at 9:21 PM
 *
 */

namespace classicprison\command;

use classicprison\ClassicPrisonPlayer;

abstract class PrisonSubCommand {

	/** @var PrisonUserCommand */
	private $owner = null;

	public function __construct(PrisonUserCommand $owner) {
		$this->owner = $owner;
	}

	public function getOwner() : PrisonUserCommand {
		return $this->owner;
	}

	public abstract function getName() : string;

	public abstract function getUsage() : string;

	public function getAliases() : array {
		return [];
	}

	public abstract function run(ClassicPrisonPlayer $sender, array $args);

	public abstract function getPermission() : string;

}