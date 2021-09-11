<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\level\sound\PopSound;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SpawnCommand
 * @package supercrafter333\theSpawn\Commands
 */
class SpawnCommand extends Command implements PluginIdentifiableCommand
{

    /**
     * @var theSpawn
     */
    private theSpawn $plugin;

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
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): bool
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();
        $spawn = new Config($pl->getDataFolder() . "theSpawns.yml", Config::YAML);
        $hub = new Config($pl->getDataFolder() . "theHub.yml", Config::YAML);
        $msgs = MsgMgr::getMsgs();
        $config = $pl->getConfig();
        #########################
        if ($s instanceof Player) {
            $levelname = $s->getLevel()->getName();
            $level = $s->getLevel();
            if ($spawn->exists($levelname) && $pl->useSpawnDelays()) {
                $pl->startSpawnDelay($s);
            } elseif ($spawn->exists($levelname)) {
                $s->teleport($pl->getSpawn($level));
                $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-tp")));
                $s->getLevel()->addSound(new PopSound($s));
            } else {
                $s->sendMessage($prefix . MsgMgr::getMsg("no-spawn-set"));
            }
            return true;
        } else {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
            return true;
        }
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}