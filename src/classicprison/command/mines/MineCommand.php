<?php

namespace classicprison\command\mines;


use classicprison\Main;
use classicprison\mines\MineReset;
use core\command\CoreUserCommand;
use core\CorePlayer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;

class MineCommand extends CoreUserCommand {
    /** @var MineReset  */
    protected $api;
    /** @var  SubCommand[] */
    protected $subCommands;
    public function __construct(MineReset $api){
        parent::__construct($api->getPlugin()->getCore(), "mine", "Main command for managing prison mines", "/mine <create|set|list|reset|reset-all|destroy> <name> [parameters]");
        $this->api = $api;
        $this->subCommands = [];
    }

    public function onRun(CorePlayer $sender, array $args){
        if(count($args) > 0 && array_key_exists($args[0], $this->subCommands)){
            return $this->subCommands[array_shift($args)]->execute($sender, $args);
        }
        else{
            $sender->sendMessage($this->getUsage());
            return null;
        }
    }

    /**
     * @return MineReset
     */
    public function getPlugin(): MineReset{
        return $this->api;
    }

    public function registerSubCommand(string $name, SubCommand $command, $aliases = []){
        $this->subCommands[$name] = $command;

        foreach ($aliases as $alias){
            if(!isset($this->subCommands[$alias])){
                $this->registerSubCommand($alias, $command);
            }
        }
    }
}