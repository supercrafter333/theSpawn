<?php

namespace supercrafter333\theSpawn\commands\home;

use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\form\HomeForms;
use supercrafter333\theSpawn\home\HomeManager;
use supercrafter333\theSpawn\MsgMgr;
use function implode;

class EdithomeCommand extends theSpawnOwnedCommand
{

    public function __construct(string $name, Translatable|string $description = "Edit a home.", Translatable|string|null $usageMessage = "§4Usage: §r/edithome [home]", array $aliases = [])
    {
        parent::__construct($name, "theSpawn.edithome.cmd", $description, $usageMessage, $aliases);
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
            (new HomeForms())->openChooseEditHome($s);
            return;
        }

        $homeName = implode(" ", $args);

        if (($home = HomeManager::getHome($homeName, $s)) === null) {
            $s->sendMessage($prefix . MsgMgr::getMsg("home-not-exists", ["{home}" => $args[0]]));
            return;
        }

        $s->sendForm((new HomeForms())->openEditHome($s, $home));
    }
}