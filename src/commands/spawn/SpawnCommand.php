<?php

namespace supercrafter333\theSpawn\commands\spawn;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\sound\PopSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\SpawnDelayManager;
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
        parent::__construct("spawn", "Teleport you to the spawn of this world!", $usageMessage, ["spawntp"]);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();
        $spawn = new Config($pl->getDataFolder() . "theSpawns.yml", Config::YAML);
        #########################

        if (!$this->isPlayer($s)) return;

        $levelname = $s->getWorld()->getFolderName();
        $level = $s->getWorld();
        if ($spawn->exists($levelname) && ConfigManager::getInstance()->useSpawnDelays()) {
            SpawnDelayManager::startSpawnDelay($s);
        } elseif ($spawn->exists($levelname)) {
            if (!$pl->isPositionSafe($pl->getSpawn($level))) {
                $s->sendMessage($prefix . MsgMgr::getMsg("position-not-safe"));
                return;
            }
            $s->teleport($pl->getSpawn($level));
            $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-tp")));
            $s->broadcastSound(new PopSound(), [$s]);
        } else {
            $s->sendMessage($prefix . MsgMgr::getMsg("no-spawn-set"));
        }
        return;
    }
}