<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\level\sound\PopSound;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class HubCommand
 * @package supercrafter333\theSpawn\Commands
 */
class HubCommand extends Command implements PluginIdentifiableCommand
{

    /**
     * @var theSpawn
     */
    private $plugin;

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
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $s, string $commandLabel, array $args)
    {
        $prefix = theSpawn::$prefix;
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
            if ($pl->getUseHubServer() == false) {
                if ($hub->exists("hub")) {
                    $hublevel = $pl->getHubLevel();
                    $hublevelxd = $pl->getServer()->getLevelByName($hublevel);
                    if ($pl->getServer()->isLevelLoaded($hublevel) == true && !$hublevelxd == null) {
                        $s->teleport($pl->getHub());
                        $s->sendMessage($prefix . str_replace(["{world}"], [$hublevelxd->getName()], MsgMgr::getMsg("hub-tp")));
                        $s->getLevel()->addSound(new PopSound($s));
                    } elseif ($hublevelxd == null) {
                        $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found-hub"));
                    } elseif (!$pl->getServer()->isLevelLoaded($hublevel)) {
                        $pl->getServer()->loadLevel($hublevel);
                        $s->teleport($pl->getHub());
                        $s->sendMessage($prefix . str_replace(["{world}"], [$hublevelxd->getName()], MsgMgr::getMsg("hub-tp")));
                        $s->getLevel()->addSound(new PopSound($s));
                    }
                    return true;
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("no-hub-set"));
                    return true;
                }
            } elseif ($pl->getUseHubServer() == true && $pl->getUseWaterdogTransfer() == false) {
                $pl->teleportToHubServer($s);
                return true;
            } elseif ($pl->getUseHubServer() == true && $pl->getUseWaterdogTransfer() == true) {
                $pl->transferToProxyServer($s, $config->get("waterdog-servername"));
                return true;
            } else {
                $s->sendMessage($prefix . MsgMgr::getMsg("false-config-setting"));
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