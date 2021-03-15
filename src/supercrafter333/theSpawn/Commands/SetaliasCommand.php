<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\level\sound\DoorBumpSound;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SetaliasCommand
 * @package supercrafter333\theSpawn\Commands
 */
class SetaliasCommand extends Command implements PluginIdentifiableCommand
{

    /**
     * @var theSpawn
     */
    private $plugin;

    /**
     * SetaliasCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        parent::__construct("setalias", "Register a new alias!", "§4Use: §r/setalias <alias> <worldname>", ["addalias"]);
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): bool
    {
        $prefix = theSpawn::PREFIX;
        $pl = theSpawn::getInstance();
        if ($s instanceof Player) {
            if (count($args) < 2) {
                $s->sendMessage($this->usageMessage);
                return true;
            }
            if (!$s->hasPermission("theSpawn.setalias.cmd")) {
                $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
                return true;
            }
            if (!is_string($args[0]) || !is_string($args[1])) {
                $s->sendMessage($this->usageMessage);
                return true;
            }
            if ($pl->existsLevel($args[1]) == false) {
                $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found"));
                return true;
            }
            if ($pl->aliasCfg->get("use-aliases") == "false") {
                $s->sendMessage($prefix . MsgMgr::getMsg("aliases-deactivated"));
                return true;
            }
            $pl->addAlias($args[0], $args[1]);
            $s->sendMessage($prefix . str_replace(["{alias}"], [$args[0]], str_replace(["{world}"], [$args[1]], MsgMgr::getMsg("alias-set"))));
            $s->getLevel()->addSound(new DoorBumpSound($s));
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