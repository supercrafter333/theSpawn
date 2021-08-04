<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\level\sound\DoorBumpSound;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SethubCommand
 * @package supercrafter333\theSpawn\Commands
 */
class SethubCommand extends Command implements PluginIdentifiableCommand
{

    /**
     * @var theSpawn
     */
    private $plugin;

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
        $config = new Config($pl->getDataFolder() . "config.yml", Config::YAML);
        #########################
        if ($s instanceof Player) {
            if ($s->hasPermission("theSpawn.sethub.cmd")) {
                $x = $s->getX();
                $y = $s->getY();
                $z = $s->getZ();
                $levelname = $s->getLevel()->getName();
                $level = $s->getLevel();
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
                            $s->getLevel()->addSound(new DoorBumpSound($s));
                            return true;
                        } else {
                            $s->sendMessage($prefix . MsgMgr::getMsg("set-random-hub-before"));
                            return true;
                        }
                    } else {
                        if (!$hub->exists("hub")) {
                            if ($level === null) {
                                $s->sendMessage($prefix . MsgMgr::getErrorMsg());
                                return true;
                            }
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

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}
