<?php
/**
 * Created by PhpStorm.
 * User: omgk45
 * Date: 7/2/2017
 * Time: 12:39 AM
 */

namespace classicprison\ranks;


use classicprison\Main;

class RankManager
{

    private $plugin;

    private $ranks;

    /** @var array int */
    private $playerRanks = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function loadPlayer(string $player) {
        //TODO: Discuss on how permissions will be handled.
    }

    public function getPlugin() {
        return $this->plugin;
    }

    public function getPlayerRank(string $player) {
        if(isset($this->playerRanks[strtolower($player)])) {
            return $this->getRank($this->playerRanks[strtolower($player)]);
        } else {
            return null;
        }
    }

    public function getRank(int $rankLevel) {
        return isset($this->ranks[$rankLevel]) ? $this->ranks[$rankLevel] : null;
    }

}