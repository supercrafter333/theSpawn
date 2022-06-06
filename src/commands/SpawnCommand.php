<?php

namespace supercrafter333\theSpawn\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\world\sound\PopSound;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SpawnCommand
 * @package supercrafter333\theSpawn\commands
 */
class SpawnCommand extends theSpawnOwnedCommand
{

    
    /**
     * SpawnCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        parent::__construct("spawn", "Teleport you to the spawn of this world!", $usageMessage, ["spawntp"]);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();
        $spawn = new Config($pl->getDataFolder() . "theSpawns.yml", Config::YAML);
        $hub = new Config($pl->getDataFolder() . "theHub.yml", Config::YAML);
        $msgs = MsgMgr::getMsgs();
        $config = $pl->getConfig();
        #########################

        if (!$this->isPlayer($s)) return;

        $levelname = $s->getWorld()->getFolderName();
        $level = $s->getWorld();
        if ($spawn->exists($levelname) && $pl->useSpawnDelays()) {
            $pl->startSpawnDelay($s);
        } elseif ($spawn->exists($levelname)) {
            if (!$pl->isPositionSafe($pl->getSpawn($level))) {
                $s->sendMessage($prefix . MsgMgr::getMsg("position-not-safe"));
                return;
            }
            $s->teleport($pl->getSpawn($level));
            $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-tp")));
            $s->getWorld()->addSound($s->getPosition(), new PopSound());
        } else {
            $s->sendMessage($prefix . MsgMgr::getMsg("no-spawn-set"));
        }
        return;
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}