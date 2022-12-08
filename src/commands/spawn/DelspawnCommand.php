<?php

namespace supercrafter333\theSpawn\commands\spawn;

use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\sound\GhastShootSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class DelspawnCommand
 * @package supercrafter333\theSpawn\commands
 */
class DelspawnCommand extends theSpawnOwnedCommand
{

    
    /**
     * DelspawnCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->setPermission("theSpawn.delspawn.cmd");
        parent::__construct("delspawn", "Delete to the spawn of this world!", $usageMessage, ["rmspawn", "deletespawn", "delthespawn"]);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     * @throws JsonException
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();
        $spawn = new Config($pl->getDataFolder() . "theSpawns.yml", Config::YAML);
        #########################

        if (!$this->canUse($s)) return;

        $levelname = $s->getWorld()->getFolderName();
        $level = $pl->getServer()->getWorldManager()->getWorldByName($levelname);
        if ($spawn->exists($levelname)) {
            $pl->removeSpawn($level);
            $s->sendMessage($prefix . MsgMgr::getMsg("spawn-removed"));
            $s->broadcastSound(new GhastShootSound(), [$s]);
        } else {
            $s->sendMessage($prefix . MsgMgr::getMsg("no-spawn-set-in-this-world"));
        }
        return;
    }
}