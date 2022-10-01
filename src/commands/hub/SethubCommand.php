<?php

namespace supercrafter333\theSpawn\commands\hub;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\world\sound\DoorBumpSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\HubManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SethubCommand
 * @package supercrafter333\theSpawn\commands
 */
class SethubCommand extends theSpawnOwnedCommand
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
        $this->plugin = theSpawn::getInstance();
        $this->setPermission("theSpawn.sethub.cmd");
        parent::__construct("sethub", "Set the lobby of this server!", "§4Usage: §r/sethub [randomHubs: number|int]", ["setlobby", "setthehub", "setthelobby"]);
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
        $hubMgr = HubManager::getInstance();
        $hub = new Config($pl->getDataFolder() . "theHub.yml", Config::YAML);
        #########################

        if (!$this->canUse($s)) return;

        $x = $s->getPosition()->getX();
        $y = $s->getPosition()->getY();
        $z = $s->getPosition()->getZ();
        $yaw = $s->getLocation()->getYaw();
        $pitch = $s->getLocation()->getPitch();
        $levelname = $s->getWorld()->getFolderName();
        $level = $s->getWorld();
        if (!$pl->getUseHubServer()) {
            if ($level === null) {
                $s->sendMessage($prefix . MsgMgr::getErrorMsg());
                return;
            }
            if (count($args) >= 1 && $pl->getUseRandomHubs()) {
                if (!is_numeric($args[0])) {
                    $s->sendMessage($this->usageMessage);
                    return;
                }
                if ($hubMgr->checkSetRandomHub($args[0])) {
                    $hubMgr->setHub($x, $y, $z, $level, $yaw, $pitch, $args[0]);
                    $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-set")));
                    $s->broadcastSound(new DoorBumpSound(), [$s]);
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("set-random-hub-before"));
                }
            } else {
                if (!$hub->exists("hub")) {
                    if ($level === null) {
                        $s->sendMessage($prefix . MsgMgr::getErrorMsg());
                        return;
                    }
                    $hubMgr->setHub($x, $y, $z, $level, $yaw, $pitch);
                    $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-set")));
                } else {
                    $hubMgr->setHub($x, $y, $z, $level, $yaw, $pitch);
                    $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-changed")));
                }
                $s->broadcastSound(new DoorBumpSound(), [$s]);
            }
            return;
        } elseif ($pl->getUseHubServer()) {
            $s->sendMessage($prefix . MsgMgr::getMsg("hub-server-is-enabled"));
            return;
        } else {
            $s->sendMessage($prefix . MsgMgr::getMsg("false-config-setting"));
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