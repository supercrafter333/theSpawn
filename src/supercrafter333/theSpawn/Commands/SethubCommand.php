<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\sound\DoorBumpSound;
use pocketmine\Player;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SethubCommand
 * @package supercrafter333\theSpawn\Commands
 */
class SethubCommand extends Command
{

    /**
     * SethubCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct("sethub", "Set the lobby of this server!", $usageMessage, ["setlobby"]);
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
            if ($s->hasPermission("theSpawn.sethub.cmd")) {
                $x = $s->getX();
                $y = $s->getY();
                $z = $s->getZ();
                $levelname = $s->getLevel()->getName();
                $level = $pl->getServer()->getLevelByName($levelname);
                if ($pl->getUseHubServer() == false) {
                    if (!$hub->exists("hub")) {
                        $pl->setHub($x, $y, $z, $level);
                        $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-set")));
                        $s->getLevel()->addSound(new DoorBumpSound($s));
                        return true;
                    } else {
                        $pl->setHub($x, $y, $z, $level);
                        $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-changed")));
                        $s->getLevel()->addSound(new DoorBumpSound($s));
                        return true;
                    }
                } elseif ($pl->getUseHubServer() == true) {
                    $s->sendMessage($prefix . MsgMgr::getMsg("hub-server-is-enabled"));
                    return true;
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("false-config-setting"));
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