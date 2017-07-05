<?php
/**
 * Created by PhpStorm.
 * User: omgk45
 * Date: 7/2/2017
 * Time: 12:31 AM
 */

namespace classicprison\ranks;


class Rank
{

    /**
     * @var int
     */
    private $cost;

    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $name;

    /**
     * @var RankManager
     */
    private $manager;

    /**
     * Rank constructor.
     * @param RankManager $manager
     * @param int $cost
     * @param int $level
     * @param string $prefix
     * @param string $name
     */
    public function __construct(RankManager $manager, int $cost, int $level, string $prefix, string $name) {
        $this->manager = $manager;
        $this->cost = $cost;
        $this->level = $level;
        $this->prefix = $prefix;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return $this->cost;
    }

    /**
     * @return int
     */
    public function getLevel(): int {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getPrefix(): string {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return RankManager
     */
    public function getManager(): RankManager {
        return $this->manager;
    }

}