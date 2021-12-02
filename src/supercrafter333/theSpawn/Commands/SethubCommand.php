<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\DoorBumpSound;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
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
     * @var theSpawn
     */
    private theSpawn $plugin;

    /**
     * SethubCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        $this->setPermission("theSpawn.sethub.cmd");
        parent::__construct("sethub", "Set the lobby of this server!", "§4Usage: §r/sethub [randomHubs: number|int]", ["setlobby", "setthehub", "setthelobby"]);
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
            if ($s->hasPermission("theSpawn.sethub.cmd")) {
                $x = $s->getPosition()->getX();
                $y = $s->getPosition()->getY();
                $z = $s->getPosition()->getZ();
                $levelname = $s->getWorld()->getDisplayName();
                $level = $s->getWorld();
                if ($pl->getUseHubServer() == false) {
                    if ($level === null) {
                        $s->sendMessage($prefix . MsgMgr::getErrorMsg());
                        return true;
                    }
                    if (count($args) >= 1 && $pl->getUseRandomHubs()) {
                        if (!is_numeric($args[0])) {
                            $s->sendMessage($this->usageMessage);
                            return true;
                        }
                        if ($pl->checkSetRandomHub($args[0])) {
                            $pl->setHub($x, $y, $z, $level, $args[0]);
                            $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-set")));
                            $s->getWorld()->addSound($s->getPosition(), new DoorBumpSound());
                        } else {
                            $s->sendMessage($prefix . MsgMgr::getMsg("set-random-hub-before"));
                        }
                    } else {
                        if (!$hub->exists("hub")) {
                            if ($level === null) {
                                $s->sendMessage($prefix . MsgMgr::getErrorMsg());
                                return true;
                            }
                            $pl->setHub($x, $y, $z, $level);
                            $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-set")));
                        } else {
                            $pl->setHub($x, $y, $z, $level);
                            $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-changed")));
                        }
                        $s->getWorld()->addSound($s->getPosition(), new DoorBumpSound());
                    }
                    return true;
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
        return true;
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}