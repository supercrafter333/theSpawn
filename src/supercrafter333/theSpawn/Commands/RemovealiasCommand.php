<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\sound\GhastShootSound;
use pocketmine\Player;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class RemovealiasCommand
 * @package supercrafter333\theSpawn\Commands
 */
class RemovealiasCommand extends Command
{

    /**
     * RemovealiasCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct("removealias", "Remove an alias!", "§4Use: §r/removealias <alias>", ["rmalias"]);
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
        if (!$s instanceof Player) {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
            return true;
        }
        if (!$s->hasPermission("theSpawn.removealias.cmd")) {
            $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
            return true;
        }
        if (!count($args) >= 1) {
            $s->sendMessage($this->usageMessage);
        }
        if ($pl->existsAlias($args[0]) == false) {
            $s->sendMessage($prefix . MsgMgr::getMsg("alias-not-found"));
            return true;
        }
        $pl->rmAlias($args[0]);
        $s->sendMessage($prefix . str_replace(["{alias}"], [$args[0]], MsgMgr::getMsg("alias-removed")));
        $s->getLevel()->addSound(new GhastShootSound($s));
        return true;
    }
}