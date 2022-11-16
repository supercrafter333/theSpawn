<?php

namespace supercrafter333\theSpawn;

use DateTime;
use EasyUI\Form;
use JsonException;
use pocketmine\block\{Air, Crops, DoubleTallGrass, Flower, Grass, Liquid, Sapling, TallGrass, Torch};
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\permission\PermissionManager;
use pocketmine\player\IPlayer;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use supercrafter333\theSpawn\commands\alias\{AliasesCommand, AliasManager, RemovealiasCommand, SetaliasCommand};
use supercrafter333\theSpawn\commands\BackCommand;
use supercrafter333\theSpawn\commands\home\{DelhomeCommand, EdithomeCommand, HomeCommand, SethomeCommand};
use supercrafter333\theSpawn\commands\hub\{DelhubCommand, HubCommand, SethubCommand};
use supercrafter333\theSpawn\commands\spawn\{DelspawnCommand, SetspawnCommand, SpawnCommand};
use supercrafter333\theSpawn\commands\tpa\{TpacceptCommand, TpaCommand, TpaHereCommand, TpdeclineCommand};
use supercrafter333\theSpawn\commands\warp\{DelwarpCommand, EditwarpCommand, SetwarpCommand, WarpCommand};
use supercrafter333\theSpawn\home\HomeManager;
use supercrafter333\theSpawn\task\SpawnDelayTask;
use supercrafter333\theSpawn\warp\WarpManager;
use function class_exists;
use function file_exists;
use function implode;
use function str_contains;
use function strtolower;

/**
 * Class theSpawn
 * @package supercrafter333\theSpawn
 */
class theSpawn extends PluginBase
{

    /**
     * @var theSpawn
     */
    public static theSpawn $instance;

    /**
     * @var string
     */
    public static string $prefix;

    /**
     * @var Config
     */
    public Config $msgCfg;

    /**
     * @var string[]
     */
    public array $spawnDelays = [];

    /**
     * @var array
     */
    public array $lastDeathPositions = [];



    /**
     * On plugin loading. (That's before enabling)
     */
    public function onLoad(): void
    {
        self::$instance = $this;
    }

    /**
     * On plugin enabling.
     */
    public function onEnable(): void
    {
        $pluginVersion = $this->getDescription()->getVersion();

        if (str_contains(strtolower($this->getDescription()->getVersion()), "dev"))
            $this->getLogger()->warning("You're using a development version of theSpawn ({$pluginVersion})!! This version can contain bugs, please report them on github!");

        if ($this->getServer()->getVersion() < 5)
            $this->getLogger()->error("This version of theSpawn has not been tested in PM4 and may contain bugs on PM4-servers!! You should upgrade to PocketMine-MP 5.0.0 or higher!");

        $this->saveResource("config.yml");
        @mkdir($this->getDataFolder() . "homes");
        @mkdir($this->getDataFolder() . "Languages");
        if (strtolower(MsgMgr::getMessagesLanguage()) == "custom")
            $this->saveResource("Languages/messages.yml");

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $cmdMap = $this->getServer()->getCommandMap();
        # Version Check
        //$this->versionCheck($this->version, true); //UPDATE CONFIG DATAs.
        $cfgVersion = $this->getConfig()->get("version");
        $this->versionCheck($pluginVersion, ($cfgVersion < "1.8.0"));
        ###

        $this->registerPermissions();

        $this->msgCfg = MsgMgr::getMsgs();
        self::$prefix = MsgMgr::getPrefix();
        $cmdMap->registerAll("theSpawn",
            [
                new SpawnCommand("spawn"),
                new SetspawnCommand("setspawn"),
                new DelspawnCommand("delspawn"),
                new HubCommand("hub"),
                new SethubCommand("sethub"),
                new DelhubCommand("delhub")
            ]);
        if ($this->useAliases()) {
            $cmdMap->registerAll("theSpawn",
                [
                    new SetaliasCommand("setalias"),
                    new RemovealiasCommand("removealias"),
                    new AliasesCommand("aliases")
                ]);
            AliasManager::reactivateAliases();
        }
        if ($this->useHomes()) {
            $cmdMap->registerAll("theSpawn",
                [
                    new SethomeCommand("sethome"),
                    new DelhomeCommand("delhome"),
                    new HomeCommand("home")
                ]);
            if ($this->useForms()) $cmdMap->register("theSpawn", new EdithomeCommand("edithome"));
        }
        if ($this->useWarps()) {
            $cmdMap->registerAll("theSpawn",
                [
                    new SetwarpCommand("setwarp"),
                    new DelwarpCommand("delwarp"),
                    new WarpCommand("warp")
                ]);
            if ($this->useForms()) $cmdMap->register("theSpawn", new EditwarpCommand("editwarp"));
        }
        if ($this->useTPAs())
            $cmdMap->registerAll("theSpawn",
                [
                    new TpaCommand("tpa"),
                    new TpaHereCommand("tpahere"),
                    new TpacceptCommand("tpaccept"),
                    new TpdeclineCommand("tpdecline")
                ]);
        if ($this->useBackCommand()) $cmdMap->register("theSpawn", new BackCommand("back"));

    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @return string
     */
    public function getFile2(): string
    {
        return $this->getFile();
    }

    /**
     * @deprecated This version will be removed when theSpawn v2.0.0 (stable) release
     * @return Config
     * @see MsgMgr::getMsgs()
     */
    public function getMsgCfg(): Config
    {
        return MsgMgr::getMsgs();
    }

     /**
     * @return bool
     */
    public function useForms(): bool
    {
        return ($this->getConfig()->get("use-forms") == "true" || $this->getConfig()->get("use-forms") == "on")
            && class_exists(Form::class);
    }

    #OLD FUNCTION (new: versionCheck($version, bool $update = true))
    /*public function checkCfgVersion(string $version): bool
    {
        if ($this->getConfig()->exists("version")) {
            if ($this->getConfig()->get("version") == $version) {
                return true;
            }
        }
        return false;
    }*/

    /**
     * Check the version of theSpawn.
     *
     * @param $version
     * @param bool $update
     * @throws JsonException
     */
    private function versionCheck($version, bool $update = true)
    {
        if (!$this->getConfig()->exists("version") || $this->getConfig()->get("version") !== $version) {
            if ($update) {
                $this->getLogger()->debug("OUTDATED CONFIG.YML!! You config.yml is outdated! Your config.yml will automatically updated!");
                $this->convertOldWarpPermissions();
                WarpManager::migrateOldWarps();
                HomeManager::migrateOldHomes();
                if (file_exists($this->getDataFolder() . "oldConfig.yml")) {
                    unlink($this->getDataFolder() . "oldConfig.yml");
                }
                rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "oldConfig.yml");
                $this->saveResource("config.yml");
                $this->getLogger()->debug("config.yml Updated for version: §b$version");
                $this->getLogger()->notice("INFORMATION: Your old config.yml can be found under `oldConfig.yml`");
            } else {
                $this->getLogger()->warning("Your config.yml is outdated but that's not so bad.");
            }
        }
        if (strtolower(MsgMgr::getMessagesLanguage()) == "custom" && (!MsgMgr::getMsgs()->exists("version") || MsgMgr::getMsgs()->get("version") !== $version)) {
            if ($update) {
                $this->getLogger()->debug("OUTDATED messages.yml!! Your messages.yml is outdated! Your " . MsgMgr::getMessagesLanguage() . ".yml will automatically updated!");
                if (file_exists($this->getDataFolder() . "Languages/messagesOld.yml")) {
                    unlink($this->getDataFolder() . "Languages/messagesOld.yml");
                }
                rename($this->getDataFolder() . "Languages/messages.yml", $this->getDataFolder() . "Languages/messagesOld.yml");
                $this->saveResource("Languages/messages.yml");
                $this->getLogger()->debug("messages.yml Updated for version: §b$version");
                $this->getLogger()->notice("INFORMATION: Your old messages.yml can be found under `" . MsgMgr::getMessagesLanguage() . "Old.yml`");
            } else {
                $this->getLogger()->warning("Your messages.yml is outdated but that's not so bad.");
            }
        }
    }

    /**
     * @return void
     */
    private function registerPermissions(): void
    {
        $defaultPerms = [
            "theSpawn.setspawn.cmd",
            "theSpawn.delspawn.cmd",
            "theSpawn.sethub.cmd",
            "theSpawn.delhub.cmd",
            "theSpawn.setalias.cmd",
            "theSpawn.removealias.cmd",
            "theSpawn.setwarp.cmd",
            "theSpawn.delwarp.cmd",
            "theSpawn.sethome.cmd",
            "theSpawn.delhome.cmd",
            "theSpawn.home.cmd",
            "theSpawn.warp.cmd",
            "theSpawn.tpa.cmd",
            "theSpawn.tpahere.cmd",
            "theSpawn.tpaccept.cmd",
            "theSpawn.tpdecline.cmd",
            "theSpawn.editwarp.cmd",
            "theSpawn.edithome.cmd",
            "theSpawn.back.cmd",

            # ADMIN PERMISSIONS:
            "theSpawn.warp.admin",
            "theSpawn.homes", #all home permissions
            "theSpawn.homes.unlimited"
        ];

        $bypassPerm = PermissionManager::getInstance()->getPermission("theSpawn.bypass");
        $homesPerm = PermissionManager::getInstance()->getPermission("theSpawn.homes");

        $homesPerm->addChild("theSpawn.sethome.cmd", true);
        $homesPerm->addChild("theSpawn.delhome.cmd", true);
        $homesPerm->addChild("theSpawn.home.cmd", true);
        $homesPerm->addChild("theSpawn.edithome.cmd", true);

        foreach ($defaultPerms as $perm) {
            $bypassPerm->addChild($perm, true);
        }
    }

    /**
     * @param string $worldName
     * @return World|null
     */
    public function checkWorld(string $worldName): ?World
    {
        if (!$this->getServer()->getWorldManager()->isWorldGenerated($worldName)) return null;
        if (!$this->getServer()->getWorldManager()->isWorldLoaded($worldName)) {
            $this->getServer()->getWorldManager()->loadWorld($worldName);
        }
        return $this->getServer()->getWorldManager()->getWorldByName($worldName);
    }

    /*public function isTpToHubOnRepawnEnabled(): bool
    {
        $use = $this->getConfig()->get("teleport-to-hub-on-respawn");
        if ($use == "true") {
            return true;
        } else {
            return false;
        }
    }*/

    /**
     * @param World|null $world
     * @return Position|Location|false
     */
    public function getSpawn(?World $world): Position|Location|false
    {
        if (!$world instanceof World) {
            $hub = HubManager::getInstance()->getHub();
            if (!$hub instanceof Position) {
                return $this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
            }
            return $hub;
        }

        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $spawn->get($world->getFolderName());
        if ($spawn->exists($world->getFolderName())) {
            $spawnArray = $spawn->get($world->getFolderName(), []);
            return $this->convertArrayToPosition($spawnArray);
        } else {
            return false;
        }
    }

    /**
     * @param Player $s
     * @param World $world
     * @return bool
     * @throws JsonException
     */
    public function setSpawn(Player $s, World $world): bool
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $x = $s->getPosition()->getX();
        $y = $s->getPosition()->getY();
        $z = $s->getPosition()->getZ();
        $yaw = $s->getLocation()->getYaw();
        $pitch = $s->getLocation()->getPitch();
        $coords = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $world->getFolderName(), "yaw" => $yaw, "pitch" => $pitch];
        $spawn->set($world->getFolderName(), $coords);
        $spawn->save();
        return true;
    }

    /**
     * @param World $world
     * @return bool
     * @throws JsonException
     */
    public function removeSpawn(World $world): bool
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        if ($spawn->exists($world->getFolderName())) {
            $spawn->remove($world->getFolderName());
            $spawn->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getUseRandomHubs(): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($config->get("use-hub-server") === "true" && $config->get("use-random-hubs") === "true") {
            $this->getLogger()->alert("INFORMATION: Please disable 'use-hub-server' in the config.yml to use random hubs!");
            return false;
        } elseif ($config->get("use-hub-server") === "true") {
            return false;
        } elseif (!$config->get("use-random-hubs") == "true") {
            return false;
        } elseif ($config->get("use-random-hubs") === "true") {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getUseHubServer(): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($config->get("use-hub-server") == "true") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getUseWaterdogTransfer(): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($config->get("waterdog-hub-teleport") === "true") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Player $s
     * @return bool
     */
    public function teleportToHubServer(Player $s): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($this->getUseHubServer() == true) {
            return $s->transfer($config->get("hub-server-ip"), $config->get("hub-server-port"));
        } else {
            return false;
        }
    }

    /*/**
     * @param Player $player
     * @param string $server
     */
    /*public function teleportToHubServerWithWaterdog(Player $player, string $server) //Thanks to FlxiBoy
    {
        API::transfer($player, $server);
        /*$pk = new ScriptCustomEventPacket();
        $pk->eventName = "bungeecord:main";
        $pk->eventData = Binary::writeShort(strlen("Connect"))."Connect".Binary::writeShort(strlen($server)).$server;
        $player->sendDataPacket($pk);
    }*/

    /**
     * @return bool
     */
    public function useAliases(): bool
    {
        return $this->getConfig()->get("use-aliases") == "true";
    }

    /**
     * @param Player $player
     * @return string|null
     */
    public function listHomes(IPlayer $player): ?string
    {
        return count(($homes = HomeManager::getHomesOfPlayer($player))) > 0 ? implode(", ", $homes) : null;
    }

    /**
     * @return bool
     */
    public function useMaxHomePermissions(): bool
    {
        return $this->getConfig()->get("use-max-home-permissions") == "true" || $this->getConfig()->get("use-max-home-permissions") == "on";
    }

    /**
     * @return bool
     */
    public function useHomes(): bool
    {
        return $this->getConfig()->get("use-homes") == "true" || $this->getConfig()->get("use-homes") == "on";
    }

    /**
     * @deprecated This will be removed soon (Reason: Waterdog is outdated and no longer under maintenance)
     * @param Player $player
     * @param string $server
     */
    public function transferToProxyServer(Player $player, string $server)
    {
        $pk = new ScriptCustomEventPacket();
        $pk->eventName = "bungeecord:main";
        $pk->eventData = Binary::writeShort(strlen("Connect")) . "Connect" . Binary::writeShort(strlen($server)) . $server;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @return bool
     */
    public function useWarps(): bool
    {
        return $this->getConfig()->get("use-warps") == "true" || $this->getConfig()->get("use-warps") == "on";
    }

    /**
     * @return string|null
     */
    public function listWarps(): ?string
    {
        $warps = null;
        if (file_exists($this->getDataFolder() . "warps.yml")) {
            $warp = WarpManager::getWarpConfig();
            $all = $warp->getAll(true);
            $getRight = $all;
            foreach ($getRight as $warpName) {
                $right = [$warpName . ", "];
                $warps .= implode(", ", $right);
            }
            return $warps;
        }
        return $warps;
    }

    /**
     * @return bool
     */
    public function useTPAs(): bool
    {
        return $this->getConfig()->get("use-tpas") == "true" || $this->getConfig()->get("use-tpas") == "on";
    }

    /**
     * @return bool
     */
    public function useSpawnDelays(): bool
    {
        if ($this->getConfig()->get("use-spawnDelay") == "true" || $this->getConfig()->get("use-spawnDelay") == "on") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Player $player
     */
    public function startSpawnDelay(Player $player)
    {
        $task = $this->getScheduler()->scheduleRepeatingTask(new SpawnDelayTask($player, $this->getConfig()->get("spawn-delay-seconds")), 20);
        $this->spawnDelays[] = $player->getName();
        $this->spawnDelays[$player->getName()] = ["task" => $task];
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function hasSpawnDelay(Player $player): bool
    {
        return isset($this->spawnDelays[$player->getName()]);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function stopSpawnDelay(Player $player): bool
    {
        if (!isset($this->spawnDelays[$player->getName()])) return false;
        $task = $this->spawnDelays["task"];
        if ($task instanceof Task) {
            $task->getHandler()->cancel();
        }
        unset($this->spawnDelays[$player->getName()]);
        return true;
    }

    /**
     * @return void
     * @throws JsonException
     */
    private function convertOldWarpPermissions(): void
    {
        $cfg = WarpManager::getWarpConfig();
        foreach ($cfg->getAll() as $warp => $warpArray) {
            if (isset($warpArray["perm"]) && $warpArray["perm"] !== true && $warpArray["perm"] !== false) {
                $cfg->setNested($warpArray["warpName"] . ".perm", "true");
                $cfg->save();
            }
        }
    }

    /**
     * @param array $posArray
     * @return Position|Location|null
     */
    public function convertArrayToPosition(array $posArray): Position|Location|null
    {
        if (!isset($posArray["level"])) return null;

        if (isset($posArray["yaw"]) && isset($posArray["pitch"])) return new Location(
            $posArray["X"],
            $posArray["Y"],
            $posArray["Z"],
            $this->checkWorld($posArray["level"]),
            $posArray["yaw"],
            $posArray["pitch"]
        );

        return new Position(
            $posArray["X"],
            $posArray["Y"],
            $posArray["Z"],
            $this->checkWorld($posArray["level"])
        );
    }

    /**
     * @return bool
     */
    public function usePositionChecks(): bool
    {
        return ($this->getConfig()->get("check-positions") == "true" || $this->getConfig()->get("check-positions") == "on");
    }

    /**
     * @param Position|Location|false|null $position
     * @return bool
     */
    public function isPositionSafe(Position|Location|null|false $position): bool
    {
        if ($position === null || $position === false) return false;

        if (!$this->usePositionChecks()) return true;

        if (!$position->isValid() || $position->getY() < $position->getWorld()->getMinY()) return false;

        $block1 = $position->getWorld()->getBlock(new Vector3($position->getX(), $position->getY() + 1, $position->getZ()));
        $block2 = $position->getWorld()->getBlock(new Vector3($position->getX(), $position->getY() + 2, $position->getZ()));

        $blocksToCheck = [];

        $blocksToCheck[] = $block1->getPosition()->getWorld()->getBlock(new Vector3($block1->getPosition()->getX() + 1, $block1->getPosition()->getY(), $block1->getPosition()->getZ()));
        $blocksToCheck[] = $block1->getPosition()->getWorld()->getBlock(new Vector3($block1->getPosition()->getX() - 1, $block1->getPosition()->getY(), $block1->getPosition()->getZ()));
        $blocksToCheck[] = $block1->getPosition()->getWorld()->getBlock(new Vector3($block1->getPosition()->getX(), $block1->getPosition()->getY(), $block1->getPosition()->getZ() + 1));
        $blocksToCheck[] = $block1->getPosition()->getWorld()->getBlock(new Vector3($block1->getPosition()->getX(), $block1->getPosition()->getY(), $block1->getPosition()->getZ() - 1));

        $blocksToCheck[] = $block2->getPosition()->getWorld()->getBlock(new Vector3($block2->getPosition()->getX() + 1, $block2->getPosition()->getY(), $block2->getPosition()->getZ()));
        $blocksToCheck[] = $block2->getPosition()->getWorld()->getBlock(new Vector3($block2->getPosition()->getX() - 1, $block2->getPosition()->getY(), $block2->getPosition()->getZ()));
        $blocksToCheck[] = $block2->getPosition()->getWorld()->getBlock(new Vector3($block2->getPosition()->getX(), $block2->getPosition()->getY(), $block2->getPosition()->getZ() + 1));
        $blocksToCheck[] = $block2->getPosition()->getWorld()->getBlock(new Vector3($block2->getPosition()->getX(), $block2->getPosition()->getY(), $block2->getPosition()->getZ() - 1));

        foreach ($blocksToCheck as $blockToCheck)
            if($blockToCheck instanceof Liquid && !$blockToCheck instanceof Air && !$blockToCheck->isSolid())
                return false;

        if($block1 instanceof Air && $block2 instanceof Air) return true;

        if (($block1 instanceof Torch || $block1 instanceof Flower || $block1 instanceof Grass || $block1 instanceof TallGrass || $block1 instanceof DoubleTallGrass || $block1 instanceof Crops || $block1 instanceof Sapling)
        && ($block2 instanceof Torch || $block2 instanceof Flower || $block1 instanceof Grass || $block1 instanceof TallGrass || $block1 instanceof DoubleTallGrass || $block1 instanceof Crops || $block1 instanceof Sapling))
            return true;

        return false;
    }

    /**
     * @return bool
     */
    public function useBackCommand(): bool
    {
        return ($this->getConfig()->get("use-back-command") == "true" || $this->getConfig()->get("use-back-command") == "on");
    }

    /**
     * @return bool
     */
    public function useHubTeleportOnDeath(): bool
    {
        if ($this->getUseHubServer()) return false;
        return ($this->getConfig()->get("hub-teleport-on-death") == "true" || $this->getConfig()->get("hub-teleport-on-death") == "on");
    }

    /**
     * @param Player $player
     * @param Location|Position $position
     * @return void
     */
    public function setLastDeathPosition(Player $player, Location|Position $position): void
    {
        $date = (new DateTime('now'))->modify('+' . $this->getConfig()->get("back-time") . ' minutes');
        $this->lastDeathPositions[$player->getName()] = [$position, $date];
    }

    /**
     * @param Player $player
     * @return Location|Position|null
     */
    public function getLastDeathPosition(Player $player): Location|Position|null
    {
        if (!isset($this->lastDeathPositions[$player->getName()])) return null;

        $dp = $this->lastDeathPositions[$player->getName()];
        $now = new DateTime('now');

        if ($now > $dp[1]) {
            unset($this->lastDeathPositions[$player->getName()]);
            return null;
        }

        $loc = $dp[0];
        unset($this->lastDeathPositions[$player->getName()]);

        return $loc;
    }
}