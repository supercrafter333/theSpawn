<?php

namespace supercrafter333\theSpawn\commands\hub;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\sound\GhastShootSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\HubManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class DelhubCommand
 * @package supercrafter333\theSpawn\commands
 */
class DelhubCommand extends theSpawnOwnedCommand
{

    
    /**
     * DelhubCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->setPermission("theSpawn.delhub.cmd");
        parent::__construct("delhub", "Delete the hub/lobby of this server!", "/delhub [randdomHubs: number|int]", ["dellobby", "rmhub", "rmlobby", "delthehub"]);
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
        #########################

        if (!$this->canUse($s)) return;

        if (ConfigManager::getInstance()->useRandomHubs()) {
            if (!count($args) >= 1) {
                $s->sendMessage($this->usageMessage);
                return;
            }
            if (!$hubMgr->checkSetRandomHub($args[0])) {
                $s->sendMessage($prefix . MsgMgr::getMsg("remove-random-hub-before"));
                return;
            }
            $hubMgr->removeHub($args[0]);
            $s->sendMessage($prefix . MsgMgr::getMsg("hub-removed"));
            $s->broadcastSound(new GhastShootSound(), [$s]);
            return;
        }
        if ($hub->exists("hub")) {
            $hubMgr->removeHub();
            $s->sendMessage($prefix . MsgMgr::getMsg("hub-removed"));
            $s->broadcastSound(new GhastShootSound(), [$s]);
            return;
        } else {
            $s->sendMessage($prefix . MsgMgr::getMsg("no-hub-set"));
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