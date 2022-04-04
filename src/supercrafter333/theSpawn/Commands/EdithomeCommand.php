<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use supercrafter333\theSpawn\Forms\HomeForms;
use supercrafter333\theSpawn\MsgMgr;
use function implode;

class EdithomeCommand extends theSpawnOwnedCommand
{

    public function __construct(string $name, Translatable|string $description = "Edit a home.", Translatable|string|null $usageMessage = "§4Usage: §r/edithome [home]", array $aliases = [])
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
        $pl = $this->plugin;
        $prefix = $this->prefix;

        if (!$this->canUse($s)) return;

        if (!isset($args[0])) {
            (new HomeForms($s->getName()))->openChooseEditHome($s);
            return;
        }

        $homeName = implode(" ", $args);

        if (($home = $pl->getHomeInfo($s, $homeName)) === null) {
            $s->sendMessage($prefix . str_replace(["{home}"], [(string)$args[0]], MsgMgr::getMsg("home-not-exists")));
            return;
        }

        (new HomeForms($s->getName()))->openEditHome($s, $home);
    }
}