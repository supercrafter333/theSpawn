<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\level\sound\GhastShootSound;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class DelspawnCommand
 * @package supercrafter333\theSpawn\Commands
 */
class DelspawnCommand extends Command implements PluginIdentifiableCommand
{

    /**
     * @var theSpawn
     */
    private $plugin;

    /**
     * DelspawnCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        parent::__construct("delspawn", "Delete to the spawn of this world!", $usageMessage, ["rmspawn", "deletespawn"]);
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
            if ($s->hasPermission("theSpawn.delspawn.cmd")) {
                $levelname = $s->getLevel()->getName();
                $level = $pl->getServer()->getLevelByName($levelname);
                if ($spawn->exists($levelname)) {
                    $pl->removeSpawn($level);
                    $s->sendMessage($prefix . MsgMgr::getMsg("spawn-removed"));
                    $s->getLevel()->addSound(new GhastShootSound($s));
                    return true;
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("no-spawn-set-in-this-world"));
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

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}