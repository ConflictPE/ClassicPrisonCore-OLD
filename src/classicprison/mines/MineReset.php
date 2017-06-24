<?php
namespace classicprison\mines;


use classicprison\Main;
use classicprison\command\mines\CreateCommand;
use classicprison\command\mines\DestroyCommand;
use classicprison\command\mines\ListCommand;
use classicprison\command\mines\MineCommand;
use classicprison\command\mines\ResetAllCommand;
use classicprison\command\mines\ResetCommand;
use classicprison\command\mines\SetCommand;
use classicprison\mines\listener\CreationListener;
use classicprison\mines\listener\RegionBlockerListener;
use classicprison\mines\store\EntityStore;
use classicprison\mines\store\YAMLStore;
use classicprison\mines\task\ScheduledResetTaskPool;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;


/**
 * MineReset is a powerful mine resetting tool for PocketMine
 *
 * Class MineReset
 * @package classicprison\mines
 */
class MineReset  {

    /** @var  Main] */
    private $plugin;
    /** @var  MineManager */
    private $mineManager;
    /** @var  ResetProgressManager */
    private $resetProgressManager;
    /** @var  RegionBlockerListener */
    private $regionBlockerListener;
    /** @var  MineCommand */
    private $mainCommand;
    /** @var  bool */
    private static $supportsChunkSetting = null;

    /** @var  CreationListener */
    private $creationListener;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;

        self::detectChunkSetting();

        $this->mineManager = new MineManager($this, new YAMLStore(new Config($this->getPlugin()->getDataFolder() . "mines.yml", Config::YAML, [])));

        $this->resetProgressManager = new ResetProgressManager($this);

        $this->regionBlockerListener = new RegionBlockerListener($this);
        $this->getPlugin()->getServer()->getPluginManager()->registerEvents($this->regionBlockerListener, $this->getPlugin());

        $this->creationListener = new CreationListener($this);
        $this->getPlugin()->getServer()->getPluginManager()->registerEvents($this->creationListener, $this->getPlugin());

        $this->mainCommand = new MineCommand($this);
        $this->getPlugin()->getCore()->getCommandMap()->register($this->mainCommand, "minereset");
        
        $this->mainCommand->registerSubCommand("list", new ListCommand($this), ['l']);
        $this->mainCommand->registerSubCommand("create", new CreateCommand($this), ['c']);
        $this->mainCommand->registerSubCommand("set", new SetCommand($this), ['s']);
        $this->mainCommand->registerSubCommand("destroy", new DestroyCommand($this), ['d']);
        $this->mainCommand->registerSubCommand("reset", new ResetCommand($this), ['r']);
        $this->mainCommand->registerSubCommand("reset-all", new ResetAllCommand($this), ['ra']);

        if(!self::supportsChunkSetting()){
            $this->getPlugin()->getLogger()->warning("Your server does not support setting chunks without unloading them. This will cause tiles and entities to be lost when resetting mines. Upgrade to a newer pmmp to resolve this.");
        }

    }

    public function onDisable(){
        $this->mineManager->saveAll();
    }
    
    public function getPlugin() : Main {
        return $this->plugin;
    }

    /**
     * @return MineManager
     */
    public function getMineManager(): MineManager{
        return $this->mineManager;
    }

    /**
     * @return ResetProgressManager
     */
    public function getResetProgressManager(): ResetProgressManager{
        return $this->resetProgressManager;
    }

    /**
     * @return MineCommand
     */
    public function getMainCommand(): MineCommand{
        return $this->mainCommand;
    }

    /**
     * @return CreationListener
     */
    public function getCreationListener(): CreationListener{
        return $this->creationListener;
    }

    /**
     * @return RegionBlockerListener
     */
    public function getRegionBlockerListener(): RegionBlockerListener{
        return $this->regionBlockerListener;
    }


    public static function supportsChunkSetting(): bool {
        return static::$supportsChunkSetting;
    }

    private static function detectChunkSetting(){
        if(self::$supportsChunkSetting === null) {
            $class = new \ReflectionClass(Level::class);
            $func = $class->getMethod("setChunk");
            $filename = $func->getFileName();
            $start_line = $func->getStartLine() - 1;
            $end_line = $func->getEndLine();
            $length = $end_line - $start_line;

            $source = file($filename);
            $body = implode("", array_slice($source, $start_line, $length));
            self::$supportsChunkSetting = strpos($body, 'removeEntity') !== false;
        }
    }
}