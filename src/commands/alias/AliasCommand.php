<?php

namespace supercrafter333\theSpawn\commands\alias;

use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\world\sound\PopSound;
use pocketmine\world\World;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class Aliases
 * @package supercrafter333\theSpawn
 */
class AliasCommand extends theSpawnOwnedCommand
{

    /**
     * @param string $name
     * @param Translatable|string $description
     */
    public function __construct(string $name, Translatable|string $description = "")
    {
        parent::__construct($name, $description);
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
        if (!$s instanceof Player) {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
            return;
        }
        if (!ConfigManager::getInstance()->useAliases()) {
            $s->sendMessage($prefix . MsgMgr::getMsg("aliases-deactivated"));
            return;
        }

        if (!AliasManager::existsAlias($this->getName())) {
            $s->sendMessage($prefix . MsgMgr::getMsg("something-went-wrong"));
            return;
        }

        $world = AliasManager::getAliasWorld($this->getName());

        if (!$world instanceof World) {
            $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found"));
            return;
        }

        if (!$pl->isPositionSafe($pl->getSpawn($world))) {
            $s->sendMessage($prefix . MsgMgr::getMsg("position-not-safe"));
            return;
        }

        $s->teleport($pl->getSpawn($world));
        $s->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("alias-teleport", ["{alias}" => $this->getName(), "{world}" => $world->getFolderName()]));
        $s->broadcastSound(new PopSound(), [$s]);
    }
}