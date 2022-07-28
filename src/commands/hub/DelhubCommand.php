<?php

namespace supercrafter333\theSpawn\commands\hub;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\world\sound\GhastShootSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
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
        $this->plugin = theSpawn::getInstance();
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
        $spawn = new Config($pl->getDataFolder() . "theSpawns.yml", Config::YAML);
        $hub = new Config($pl->getDataFolder() . "theHub.yml", Config::YAML);
        $msgs = MsgMgr::getMsgs();
        $config = $pl->getConfig();
        #########################

        if (!$this->canUse($s)) return;

        if ($pl->getUseRandomHubs()) {
            if (!count($args) >= 1) {
                $s->sendMessage($this->usageMessage);
                return;
            }
            if (!$pl->checkSetRandomHub($args[0])) {
                $s->sendMessage($prefix . MsgMgr::getMsg("remove-random-hub-before"));
                return;
            }
            $pl->removeHub($args[0]);
            $s->sendMessage($prefix . MsgMgr::getMsg("hub-removed"));
            $s->getWorld()->addSound($s->getPosition(), new GhastShootSound());
            return;
        }
        if ($hub->exists("hub")) {
            $pl->removeHub();
            $s->sendMessage($prefix . MsgMgr::getMsg("hub-removed"));
            $s->getWorld()->addSound($s->getPosition(), new GhastShootSound());
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