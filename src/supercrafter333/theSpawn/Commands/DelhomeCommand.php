<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use supercrafter333\theSpawn\Commands\theSpawnOwnedCommand;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\GhastShootSound;
use pocketmine\player\Player;
use supercrafter333\theSpawn\Forms\HomeForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class DelhomeCommand
 * @package supercrafter333\theSpawn\Commands
 */
class DelhomeCommand extends theSpawnOwnedCommand
{

    /**
     * SethomeCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->setPermission("theSpawn.delhome.cmd");
        parent::__construct("delhome", "Delete a home!", "§4Use: §r/delhome <name>", ["deletehome", "rmhome"]);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender|Player $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!$this->canUse($s)) return;

        if (count($args) < 1) {
            if ($pl->useForms()) {
                if ($pl->listHomes($s) == null) {
                    $s->sendMessage($prefix . MsgMgr::getMsg("no-homes-set"));
                    return;
                }
                $warpForms = new HomeForms($s->getName());
                $warpForms->openRmHome($s);
            } else {
                $s->sendMessage($this->usageMessage);
            }
            return;
        }
        if ($pl->rmHome($s, $args[0]) == false) {
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-not-exists")));
        } else {
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-deleted")));
            $s->getWorld()->addSound($s->getPosition(), new GhastShootSound());
        }
        return;
    }

    public static function simpleExecute(Player $s, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!self::testPermissionX($s, "theSpawn.delhome.cmd", "delhome")) return;

        if ($pl->rmHome($s, $args[0]) == false) {
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-not-exists")));
        } else {
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-deleted")));
            $s->getWorld()->addSound($s->getPosition(), new GhastShootSound());
        }
        return;
    }
}