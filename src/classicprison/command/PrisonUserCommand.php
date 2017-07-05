<?php

/**
 * ClassicPrisonCore – PrisonUserCommand.php
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

use core\command\CoreUserCommand;
use core\Main;

abstract class PrisonUserCommand extends CoreUserCommand
{

    /** @var PrisonSubCommand[] */
    private $subCommands = [];

    public function __construct(Main $plugin, $name, $description, $usage, $aliases = []) {
        $this->registerDefaultSubCommands();
        parent::__construct($plugin, $name, $description, $usage, $aliases);
    }

    /**
     * Register all the default sub-commands for this command
     */
    protected abstract function registerDefaultSubCommands();

    /**
     * Register a sub-command
     *
     * @param PrisonSubCommand $subCommand
     */
    public function registerSubCommand(PrisonSubCommand $subCommand) {
        foreach (array_merge($subCommand->getAliases(), $subCommand->getName()) as $label) {
            $name = strtolower($label);
            if (!isset($this->subCommands[$name])) {
                $this->subCommands[$name] = $subCommand;
            } else {
                // TODO: Error handling (needs to be integrated into core)
            }
        }
    }

}