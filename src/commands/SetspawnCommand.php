<?php

namespace supercrafter333\theSpawn\commands;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\world\sound\DoorBumpSound;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SetspawnCommand
 * @package supercrafter333\theSpawn\commands
 */
class SetspawnCommand extends theSpawnOwnedCommand
{

    
    /**
     * SetspawnCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        $this->setPermission("theSpawn.setspawn.cmd");
        parent::__construct("setspawn", "Set the spawn of this world!", $usageMessage, ["setthespawn"]);
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     * @throws \JsonException
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

        if (!$this->canUse($s)) return;

        $levelname = $s->getWorld()->getFolderName();
        $level = $s->getWorld();
        $pl->setSpawn($s, $level);
        if (!$spawn->exists($levelname)) {
            $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-set")));
        } else {
            $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-changed")));
        }
        $s->getWorld()->addSound($s->getPosition(), new DoorBumpSound());
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