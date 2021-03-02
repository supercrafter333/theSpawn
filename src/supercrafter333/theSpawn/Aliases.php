<?php

namespace supercrafter333\theSpawn;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use supercrafter333\theSpawn\theSpawn;

class Aliases extends AliasMap
{

    public function __construct(theSpawn $main, $cmdName, $cmdDescription)
    {
        parent::__construct($main, $cmdName, $cmdDescription);
        $this->cmdName = $cmdName;
        $this->cmdDescription = $cmdDescription;
        $this->pl = $main;
    }

    public function execute(CommandSender $s, string $commandLabel, array $args): bool
    {
        $prefix = "§f[§7the§eSpawn§f] §8»§r ";
        $pl = theSpawn::getInstance();
        if ($s instanceof Player) {
            if (theSpawn::getInstance()->useAliases() == false) {
                $s->sendMessage($prefix . "§cAliases sind auf diesem Server deaktiviert! Sie können in der config.yml aktiviert werden!");
                return true;
            }
            if ($lvl = $pl->existsAlias($this->cmdName) == true) {
                $level = Server::getInstance()->getLevelByName($lvl);
                if (Server::getInstance()->isLevelLoaded($level) == true) {
                    $s->teleport($pl->getSpawn($level));
                    return true;
                } else {
                    Server::getInstance()->loadLevel($lvl);
                    $s->teleport($pl->getSpawn($level));
                    return true;
                }
            } else {
                $s->sendMessage($prefix . "§4FEHLER --> §cIrgendetwas ist schiefgelaufen!");
            }
        } else {
            $s->sendMessage("Nur In-Game!");
        }
        return false;
    }
}