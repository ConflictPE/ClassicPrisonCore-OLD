<?php
/**
 * ClassicPrison – JoinArenaNPC.php
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
use classicprison\Main;
use core\entity\npc\HumanNPC;
use core\Utils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginException;

class JoinArenaNPC extends HumanNPC
{

    /** @var UpdatableFloatingText */
    public $playingText;
    /** @var Main */
    private $plugin;
    /** @var Arena */
    private $arena;

    /**
     * @param string $shortName
     * @param Location $pos
     * @param string $name
     * @param string $skin
     * @param string $skinName
     * @param CompoundTag $nbt
     * @param string $arena
     *
     * @return JoinArenaNPC|HumanNPC|null
     */
    public static function spawn($shortName, Location $pos, $name, $skin, $skinName, CompoundTag $nbt, $arena = "") {
        $entity = parent::spawn($shortName, $pos, $name, $skin, $skinName, $nbt);
        if ($entity instanceof JoinArenaNPC) {
            $entity->updatePlayingText("&l&e0 players playing&r");
            $entity->setArena($arena);
        }
        return $entity;
    }

    public function initEntity() {
        parent::initEntity();
        $plugin = $this->server->getPluginManager()->getPlugin("ClassicPrison");
        if ($plugin instanceof Main and $plugin->isEnabled()) {
            $this->plugin = $plugin;
        } else {
            throw new PluginException("ClassicPrison plugin isn't loaded!");
        }
    }

    /**
     * @return Main
     */
    public function getPlugin() {
        return $this->plugin;
    }

    /**
     * @return Arena
     */
    public function getArena() {
        return $this->arena;
    }

    /**
     * @param string $text
     */
    public function updatePlayingText($text) {
        if (!$this->playingText instanceof UpdatableFloatingText) {
            $pos = $this->getPosition();
            $pos->y -= 1;
            $this->playingText = new UpdatableFloatingText($pos, Utils::translateColors($text));
            return;
        }
        $this->playingText->update(Utils::translateColors($text));
    }

    /**
     * @param $string
     */
    public function setArena($string) {
        $this->arena = $this->plugin->getArenaManager()->getArena($string);
    }

    public function attack($damage, EntityDamageEvent $source) {
        $source->setCancelled(true);
        if ($source instanceof EntityDamageByEntityEvent) {
            $attacker = $source->getDamager();
            if ($attacker instanceof ClassicPrisonPlayer) {
                if ($attacker->isAuthenticated()) {
                    if (!$attacker->inArena()) {
                        if ($this->arena instanceof Arena) {
                            $this->plugin->getArenaManager()->addPlayerToArena($this->arena, $attacker);
                        } else {
                            $attacker->sendTranslatedMessage("ARENA_JOIN_ERROR", [], true);
                        }
                    } else {
                        $attacker->sendTranslatedMessage("ALREADY_IN_ARENA", [], true);
                    }
                } else {
                    $attacker->sendTranslatedMessage("MUST_AUTHENTICATE_FIRST", [], true);
                }
            }
        }
    }

}