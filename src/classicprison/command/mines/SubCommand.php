<?php
namespace classicprison\command\mines;


use classicprison\mines\MineReset;
use pocketmine\command\CommandSender;

abstract class SubCommand{
    /** @var  MineReset */
    private $api;

    /**
     * SubCommand constructor.
     * @param MineReset $api
     */
    public function __construct(MineReset $api){
        $this->api = $api;
    }


    abstract public function execute(CommandSender $sender, array $args);

    /**
     * @return MineReset
     */
    public function getApi(): MineReset{
        return $this->api;
    }
}