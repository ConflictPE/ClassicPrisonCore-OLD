<?php

/**
 * ClassicPrisonCore – SilentMessageCommand.php
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

namespace classicprison\command;

use classicprison\ClassicPrisonPlayer;
use classicprison\Main;
use core\command\CoreCommand;
use core\Utils;
use pocketmine\command\CommandSender;

class SilentMessageCommand extends CoreCommand
{

    public function __construct(Main $plugin) {
        parent::__construct($plugin->getCore(), "silentmessage", "Send a silent message to a player", "/silentmessage {player} {message}", ["sm"]);
    }

    public function run(CommandSender $sender, array $args) {
        if (isset($args[1])) {
            $target = $this->getPlugin()->getServer()->getPlayer($plain = array_shift($args));
            if ($target instanceof ClassicPrisonPlayer and $target->isOnline()) {
                $target->sendMessage(Utils::translateColors(implode(" ", $args)));
                if ($sender instanceof ClassicPrisonPlayer)
                    $sender->sendTranslatedMessage("SILENT_MESSAGE_COMMAND", [], true);
            } else {
                $sender->sendMessage($this->getPlugin()->getLanguageManager()->translate("USER_NOT_ONLINE", "en", [$plain]));
            }
        } else {
            $sender->sendMessage($this->getPlugin()->getLanguageManager()->translate("COMMAND_USAGE", "en", [$this->getUsage()]));
        }
    }

}