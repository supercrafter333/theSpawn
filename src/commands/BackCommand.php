<?php

namespace supercrafter333\theSpawn\commands;

use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use supercrafter333\theSpawn\MsgMgr;

class BackCommand extends theSpawnOwnedCommand
{

    public function __construct(string $name, Translatable|string $description = "Teleports you to the position where you have died.", Translatable|string|null $usageMessage = null, array $aliases = ["deathpos"])
    {
        $this->setPermission("theSpawn.back.cmd");
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        if (!$this->canUse($s)) return;

        if (($pos = $this->plugin->getLastDeathPosition($s)) === null) {
            $s->sendMessage($this->prefix . MsgMgr::getMsg("no-deathposition-found"));
            return;
        }

        $s->teleport($pos);
        $s->sendMessage($this->prefix . MsgMgr::getMsg("teleported-to-deathposition", [
                    "{X}" => $pos->getX(),
                    "{Y}" => $pos->getY(),
                    "{Z}" => $pos->getZ(),
                    "{yaw}" => $pos->getYaw(),
                    "{pitch}" => $pos->getPitch(),
                    "{world}" => $pos->getWorld()->getFolderName()
                ]));
    }
}