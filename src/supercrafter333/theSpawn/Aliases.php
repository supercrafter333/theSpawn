<?php

namespace supercrafter333\theSpawn;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\sound\PopSound;

/**
 * Class Aliases
 * @package supercrafter333\theSpawn
 */
class Aliases extends AliasMap
{

    /**
     * Aliases constructor.
     * @param theSpawn $pl
     * @param string $cmdName
     * @param string $cmdDescription
     */
    public function __construct(public theSpawn $pl, public string $cmdName, public string $cmdDescription)
    {
        parent::__construct($pl, $cmdName, $cmdDescription);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $prefix = "§f[§7the§eSpawn§f] §8»§r ";
        $pl = theSpawn::getInstance();
        if ($s instanceof Player) {
            if (theSpawn::getInstance()->useAliases() == true) {
                $lvl = $pl->getWorldOfAlias($this->cmdName);
                if ($pl->existsAlias($this->cmdName) == true) {
                    if (theSpawn::getInstance()->getServer()->getWorldManager()->isWorldGenerated($lvl) == false) {
                        $s->sendMessage($prefix . "§cDie angegeben Welt existiert nicht!");
                        return;
                    }
                    if (!Server::getInstance()->getWorldManager()->isWorldLoaded($lvl)) {
                        Server::getInstance()->getWorldManager()->loadWorld($lvl);
                    }
                    $level = Server::getInstance()->getWorldManager()->getWorldByName($lvl);
                    if (!$pl->isPositionSafe($pl->getSpawn($level))) {
                        $s->sendMessage($prefix . MsgMgr::getMsg("position-not-safe"));
                        return;
                    }
                    $s->teleport($pl->getSpawn($level));
                    $s->sendMessage(theSpawn::$prefix . str_replace(["{alias}"], [$this->cmdName], str_replace(["{world}"], [$level->getFolderName()], MsgMgr::getMsg("alias-teleport"))));
                    $s->getWorld()->addSound($s->getPosition(), new PopSound());
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("something-went-wrong"));
                }
            } else {
                $s->sendMessage($prefix . MsgMgr::getMsg("aliases-deactivated"));
            }
        } else {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
        }
    }
}