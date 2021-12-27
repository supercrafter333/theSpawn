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
     * @var theSpawn
     */
    private theSpawn $plugin;

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
        parent::__construct("setwarp", "Set a new warp!", "§4Use: §r/setwarp <warpname> [permission]", $aliases);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender|Player $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::$prefix;
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
            $pl->addWarp($s->getPosition()->getX(), $s->getPosition()->getY(), $s->getPosition()->getZ(), $s->getPosition()->getWorld(), $args[0], isset($args[1]) ? (string)$args[1] : null);
            $posMsg = (string)$s->getPosition()->getX() . $s->getPosition()->getY() . $s->getPosition()->getZ();
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