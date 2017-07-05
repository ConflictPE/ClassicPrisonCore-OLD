<?php
/**
 * ClassicPrison – DisplayNPC.php
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

namespace classicprison\entity\npc;

use classicprison\ClassicPrisonPlayer;
use core\entity\npc\HumanNPC;
use core\Utils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;

class DisplayNPC extends HumanNPC
{

    /** @var string */
    private $text = "";

    /**
     * @param string $shortName
     * @param Location $pos
     * @param string $name
     * @param string $skin
     * @param string $skinName
     * @param CompoundTag $nbt
     * @param string $text
     *
     * @return HumanNPC|null
     */
    public static function spawn($shortName, Location $pos, $name, $skin, $skinName, CompoundTag $nbt, $text = "") {
        $entity = parent::spawn($shortName, $pos, $name, $skin, $skinName, $nbt);
        if ($entity instanceof DisplayNPC)
            $entity->setText($text);
        return $entity;
    }

    /**
     * Set the text to display to players when they tap the npc
     *
     * @param string $text
     */
    public function setText($text = "") {
        $this->text = Utils::translateColors($text);
    }

    /**
     * Get the text to display to players when they tap the npc
     *
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    public function attack($damage, EntityDamageEvent $source) {
        parent::attack($damage, $source);
        if ($source instanceof EntityDamageByEntityEvent) {
            $attacker = $source->getDamager();
            if ($attacker instanceof ClassicPrisonPlayer) {
                if ($attacker->isAuthenticated()) {
                    $attacker->sendMessage($this->text);
                } else {
                    $attacker->sendTranslatedMessage("MUST_AUTHENTICATE_FIRST", [], true);
                }
            }
        }
    }

}