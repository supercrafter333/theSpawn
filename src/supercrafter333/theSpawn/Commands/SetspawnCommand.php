<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\sound\DoorBumpSound;
use pocketmine\level\sound\PopSound;
use pocketmine\Player;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SetspawnCommand
 * @package supercrafter333\theSpawn\Commands
 */
class SetspawnCommand extends Command
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
        parent::__construct("setspawn", "Set the spawn of this world!", $usageMessage, $aliases);
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $s, string $commandLabel, array $args)
    {
        $prefix = theSpawn::PREFIX;
        $pl = theSpawn::getInstance();
        $spawn = new Config($pl->getDataFolder() . "theSpawns.yml", Config::YAML);
        $hub = new Config($pl->getDataFolder() . "theHub.yml", Config::YAML);
        $msgs = MsgMgr::getMsgs();
        $pl->getConfig();
        @mkdir($pl->getDataFolder());
        $pl->saveResource("config.yml");
        $config = new Config($pl->getDataFolder() . "config.yml", Config::YAML);
        $config->save();
        #########################
        if ($s instanceof Player) {
            if ($s->hasPermission("theSpawn.setspawn.cmd")) {
                $levelname = $s->getLevel()->getName();
                $level = $s->getLevel();
                if (!$spawn->exists($levelname)) {
                    $pl->setSpawn($s, $level);
                    $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-set")));
                    $s->getLevel()->addSound(new DoorBumpSound($s));
                    return true;
                } else {
                    $pl->setSpawn($s, $level);
                    $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-changed")));
                    $s->getLevel()->addSound(new DoorBumpSound($s));
                    return true;
                }
            } else {
                $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
                return true;
            }
        } else {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
            return true;
        }
    }
}