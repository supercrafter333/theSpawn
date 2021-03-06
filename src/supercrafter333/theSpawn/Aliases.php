<?php

namespace supercrafter333\theSpawn;

use pocketmine\command\CommandSender;
use pocketmine\level\sound\PopSound;
use pocketmine\Player;
use pocketmine\Server;

/**
 * Class Aliases
 * @package supercrafter333\theSpawn
 */
class Aliases extends AliasMap
{

    /**
     * Aliases constructor.
     * @param theSpawn $main
     * @param $cmdName
     * @param $cmdDescription
     */
    public function __construct(theSpawn $main, $cmdName, $cmdDescription)
    {
        parent::__construct($main, $cmdName, $cmdDescription);
        $this->cmdName = $cmdName;
        $this->cmdDescription = $cmdDescription;
        $this->pl = $main;
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): bool
    {
        $prefix = "§f[§7the§eSpawn§f] §8»§r ";
        $pl = theSpawn::getInstance();
        if ($s instanceof Player) {
            if (theSpawn::getInstance()->useAliases() == true) {
                $lvl = $pl->getWorldOfAlias($this->cmdName);
                if ($pl->existsAlias($this->cmdName) == true) {
                    if (theSpawn::getInstance()->getServer()->isLevelGenerated($lvl) == false) {
                        $s->sendMessage($prefix . "§cDie angegeben Welt existiert nicht!");
                        return true;
                    }
                    if (Server::getInstance()->isLevelLoaded($lvl) == true) {
                        $level = Server::getInstance()->getLevelByName($lvl);
                        $s->teleport($pl->getSpawn($level));
                        return true;
                    } else {
                        Server::getInstance()->loadLevel($lvl);
                        $level = Server::getInstance()->getLevelByName($lvl);
                        $s->teleport($pl->getSpawn($level));
                        $s->sendMessage(theSpawn::$prefix . str_replace(["{alias}"], [$this->cmdName], str_replace(["{world}"], [$level->getName()], MsgMgr::getMsg("alias-teleport"))));
                        $s->getLevel()->addSound(new PopSound($s));
                        return true;
                    }
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("something-went-wrong"));
                }
            } else {
                $s->sendMessage($prefix . MsgMgr::getMsg("aliases-deactivated"));
                return true;
            }
        } else {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
            return true;
        }
        return false;
    }
}