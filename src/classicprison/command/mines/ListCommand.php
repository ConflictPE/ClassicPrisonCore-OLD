<?php
namespace classicprison\command\mines;


use classicprison\mines\Mine;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ListCommand extends SubCommand{
    public function execute(CommandSender $sender, array $args){
        if($sender->hasPermission("minereset.command.list")) {
            foreach ($this->getApi()->getMineManager() as $mine) {
                if ($mine instanceof Mine) {
                    $sender->sendMessage($mine->getName());
                }
            }
        }
        else{
            $sender->sendMessage(TextFormat::RED . "You do not have permission to run this command." . TextFormat::RESET);
        }
    }
}