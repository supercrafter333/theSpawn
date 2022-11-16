<?php

namespace supercrafter333\theSpawn\commands\alias;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\sound\DoorBumpSound;
use pocketmine\world\World;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\form\AliasForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SetaliasCommand
 * @package supercrafter333\theSpawn\commands
 */
class SetaliasCommand extends theSpawnOwnedCommand
{

    
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
        $this->setPermission("theSpawn.setalias.cmd");
        parent::__construct("setalias", "Register a new alias!", "§4Use: §r/setalias <alias> <worldname>", ["addalias"]);
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

        if (!$this->canUse($s)) return;

        if (count($args) < 2) {
            if ($pl->useForms())
                $s->sendForm(AliasForms::addAlias($s->getWorld()));
            else
                $s->sendMessage($this->usageMessage);
            return;
        }

        if (!is_string($args[0]) || !is_string($args[1])) {
            $s->sendMessage($this->usageMessage);
            return;
        }

        if (!$pl->checkWorld($args[1]) instanceof World) {
            $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found"));
            return;
        }

        if (!$pl->getSpawn($pl->checkWorld($args[1]))) {
            $s->sendMessage($prefix . str_replace(["{world}"], [$args[1]], MsgMgr::getMsg("no-spawn-set-for-world")));
            return;
        }

        AliasManager::setAlias($args[0], theSpawn::getInstance()->checkWorld($args[1]));
        $s->sendMessage($prefix . str_replace(["{alias}"], [$args[0]], str_replace(["{world}"], [$args[1]], MsgMgr::getMsg("alias-set"))));
        $s->broadcastSound(new DoorBumpSound(), [$s]);
        return;
    }
}