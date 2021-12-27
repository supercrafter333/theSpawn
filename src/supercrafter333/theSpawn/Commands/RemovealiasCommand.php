<?php

namespace supercrafter333\theSpawn\Commands;

use supercrafter333\theSpawn\Commands\theSpawnOwnedCommand;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\GhastShootSound;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class RemovealiasCommand
 * @package supercrafter333\theSpawn\Commands
 */
class RemovealiasCommand extends theSpawnOwnedCommand
{

    /**
     * @var theSpawn
     */
    private theSpawn $plugin;

    /**
     * RemovealiasCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        $this->setPermission("theSpawn.removealias.cmd");
        parent::__construct("removealias", "Remove an alias!", "§4Use: §r/removealias <alias>", ["rmalias"]);
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

        if (!$this->canUse($s)) return;

        if (!count($args) >= 1) {
            $s->sendMessage($this->usageMessage);
            return;
        }
        if ($pl->existsAlias($args[0]) == false) {
            $s->sendMessage($prefix . MsgMgr::getMsg("alias-not-found"));
            return;
        }
        $pl->rmAlias($args[0]);
        $s->sendMessage($prefix . str_replace(["{alias}"], [$args[0]], MsgMgr::getMsg("alias-removed")));
        $s->getWorld()->addSound($s->getPosition(), new GhastShootSound());
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