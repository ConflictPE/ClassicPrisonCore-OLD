<?php

/**
 * ClassicPrisonCore – MineCommand.php
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
 * Created on 1/7/2017 at 9:20 PM
 *
 */

namespace classicprison\command\mines;

use core\Main;
use classicprison\mines\MineReset;
use core\command\CoreUserCommand;
use core\CorePlayer;

class MineCommand extends CoreUserCommand
{

    /** @var MineReset */
    protected $api;

    /** @var SubCommand[] */
    protected $subCommands = [];

    public function __construct(Main $plugin) {
        parent::__construct($plugin, "mine", "Main command for managing prison mines", "/mine <create|set|list|reset|reset-all|destroy> <name> [parameters]");
    }

    public function onRun(CorePlayer $sender, array $args) {
        if (count($args) > 0 and isset($this->subCommands[($subCmd = strtolower(array_shift($args[0])))])) {
            return $this->subCommands[$subCmd]->execute($sender, $args);
        } else {
            $sender->sendMessage($this->getUsage());
            return false;
        }
    }

    public function registerSubCommand(string $name, SubCommand $command, $aliases = []) {
        $this->subCommands[$name] = $command;
        foreach ($aliases as $alias) {
            if (!isset($this->subCommands[$alias])) {
                $this->registerSubCommand($alias, $command);
            }
        }
    }
}