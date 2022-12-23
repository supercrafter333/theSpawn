<?php

namespace supercrafter333\theSpawn\Commands;

use supercrafter333\theSpawn\Commands\theSpawnOwnedCommand;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\DoorBumpSound;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\Forms\WarpForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SetwarpCommand
 * @package supercrafter333\theSpawn\Commands
 */
class SetwarpCommand extends theSpawnOwnedCommand
{

    
    /**
     * SetwarpCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        $this->setPermission("theSpawn.setwarp.cmd");
        parent::__construct("setwarp", "Set a new warp!", "§4Use: §r/setwarp <warpname> [permission: true|false] [iconPath | iconUrl]", $aliases);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $pl = theSpawn::getInstance();

        if (!$this->canUse($s)) return;

        if (count($args) < 1) {
            if ($pl->useForms()) {
                $warpForms = new WarpForms();
                $warpForms->openSetWarp($s);
            } else {
                $s->sendMessage($this->usageMessage);
            }
            return;
        }

        self::simpleExecute($s, $args);
    }

    public static function simpleExecute(Player $s, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!self::testPermissionX($s, "theSpawn.setwarp.cmd", "setwarp")) return;

        if ($pl->existsWarp($args[0]) == false) {
            $perm = false;
            $icon = null;
            if (isset($args[1]) && $args[1] !== 'null' && $args[1] !== 'false' && $args[1] !== "") $perm = true;
            if (isset($args[2]) && $args[2] !== 'null' && $args[2] !== "") $icon = $args[2];
            $pl->addWarp($s->getPosition()->getX(), $s->getPosition()->getY(), $s->getPosition()->getZ(), $s->getPosition()->getWorld(), $args[0], $s->getLocation()->getYaw(), $s->getLocation()->getPitch(), $perm, $icon);
            $posMsg = $s->getPosition()->getX() . $s->getPosition()->getY() . $s->getPosition()->getZ();
            $s->sendMessage($prefix . str_replace(["{warpname}"], [$args[0]], str_replace(["{position}"], [$posMsg], str_replace(["{world}"], [$s->getWorld()->getFolderName()], MsgMgr::getMsg("warp-set")))));
        } else {
            $s->sendMessage($prefix . str_replace(["{warpname}"], [$args[0]], MsgMgr::getMsg("warp-already-set")));
        }
        $s->getWorld()->addSound($s->getPosition(), new DoorBumpSound());
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}