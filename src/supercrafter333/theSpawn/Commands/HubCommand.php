<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use supercrafter333\theSpawn\Commands\theSpawnOwnedCommand;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\PopSound;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class HubCommand
 * @package supercrafter333\theSpawn\Commands
 */
class HubCommand extends theSpawnOwnedCommand
{

    /**
     * @var theSpawn
     */
    private theSpawn $plugin;

    /**
     * HubCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        parent::__construct("hub", "Teleport you to the hub/lobby of this server!", $usageMessage, ["lobby", "hubtp", "lobbytp"]);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
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
        if ($this->isPlayer($s)) {
            if ($pl->getUseHubServer() == false) {
                if ($pl->getUseRandomHubs()) {
                    $hubPos = $pl->getRandomHub();
                    if ($hubPos !== null) {
                        $s->teleport($hubPos);
                        $s->sendMessage($prefix . str_replace(["{world}"], [$hubPos->getWorld()->getFolderName()], MsgMgr::getMsg("hub-tp")));
                        $s->getWorld()->addSound($s->getPosition(), new PopSound());
                    } else {
                        $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found-hub"));
                    }
                    return;
                }
                if ($hub->exists("hub")) {
                    $hublevel = $pl->getHub()->getWorld();
                    if ($hublevel !== null) {
                        $s->teleport($pl->getHub());
                        $s->sendMessage($prefix . str_replace(["{world}"], [$hublevel->getFolderName()], MsgMgr::getMsg("hub-tp")));
                        $s->getWorld()->addSound($s->getPosition(), new PopSound());
                    } else {
                        $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found-hub"));
                    }
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("no-hub-set"));
                }
            } elseif ($pl->getUseHubServer() == true && $pl->getUseWaterdogTransfer() == false) {
                $pl->teleportToHubServer($s);
            } elseif ($pl->getUseHubServer() == true && $pl->getUseWaterdogTransfer() == true) {
                $pl->transferToProxyServer($s, $config->get("waterdog-servername"));
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