<?php

namespace supercrafter333\theSpawn\commands\hub;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\sound\PopSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\HubManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class HubCommand
 * @package supercrafter333\theSpawn\commands
 */
class HubCommand extends theSpawnOwnedCommand
{

    
    /**
     * HubCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct("hub", "theSpawn.hub.cmd", "Teleport you to the hub/lobby of this server!", $usageMessage, ["lobby", "hubtp", "lobbytp"]);
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
        $hub = $hubMgr->getHubConfig();
        $config = $pl->getConfig();
        #########################

        if ($this->isPlayer($s)) {
            if (!ConfigManager::getInstance()->useHubServer()) {
                if (ConfigManager::getInstance()->useRandomHubs()) {
                    $hubPos = $hubMgr->getRandomHub();
                    if ($hubPos !== null) {
                        if (!$pl->isPositionSafe($hubPos)) {
                            $s->sendMessage($prefix . MsgMgr::getMsg("position-not-safe"));
                            return;
                        }
                        $s->teleport($hubPos);
                        $s->sendMessage($prefix . str_replace(["{world}"], [$hubPos->getWorld()->getFolderName()], MsgMgr::getMsg("hub-tp")));
                        $s->broadcastSound(new PopSound(), [$s]);
                    } else {
                        $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found-hub"));
                    }
                    return;
                }
                if ($hub->exists("hub")) {
                    $hublevel = $hubMgr->getHub()->getWorld();
                    if ($hublevel !== null) {
                        $hubPos2 = $hubMgr->getHub();
                        if (!$pl->isPositionSafe($hubPos2)) {
                            $s->sendMessage($prefix . MsgMgr::getMsg("position-not-safe"));
                            return;
                        }
                        $s->teleport($hubPos2);
                        $s->sendMessage($prefix . str_replace(["{world}"], [$hublevel->getFolderName()], MsgMgr::getMsg("hub-tp")));
                        $s->broadcastSound(new PopSound(), [$s]);
                    } else {
                        $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found-hub"));
                    }
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("no-hub-set"));
                }
            } elseif (ConfigManager::getInstance()->useHubServer()) {
                $pl->teleportToHubServer($s);
            } else {
                $s->sendMessage($prefix . MsgMgr::getMsg("false-config-setting"));
            }
        } else {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
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