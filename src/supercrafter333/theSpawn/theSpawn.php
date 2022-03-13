<?php

namespace supercrafter333\theSpawn;

use JsonException;
use pocketmine\block\BlockLegacyIds as BLI;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\permission\PermissionManager;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use pocketmine\world\Position;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\Commands\DelhomeCommand;
use supercrafter333\theSpawn\Commands\DelhubCommand;
use supercrafter333\theSpawn\Commands\DelspawnCommand;
use supercrafter333\theSpawn\Commands\DelwarpCommand;
use supercrafter333\theSpawn\Commands\HomeCommand;
use supercrafter333\theSpawn\Commands\HubCommand;
use supercrafter333\theSpawn\Commands\RemovealiasCommand;
use supercrafter333\theSpawn\Commands\SetaliasCommand;
use supercrafter333\theSpawn\Commands\SethomeCommand;
use supercrafter333\theSpawn\Commands\SethubCommand;
use supercrafter333\theSpawn\Commands\SetspawnCommand;
use supercrafter333\theSpawn\Commands\SetwarpCommand;
use supercrafter333\theSpawn\Commands\SpawnCommand;
use supercrafter333\theSpawn\Commands\TpacceptCommand;
use supercrafter333\theSpawn\Commands\TpaCommand;
use supercrafter333\theSpawn\Commands\TpaHereCommand;
use supercrafter333\theSpawn\Commands\TpdeclineCommand;
use supercrafter333\theSpawn\Commands\WarpCommand;
use supercrafter333\theSpawn\Others\HomeInfo;
use supercrafter333\theSpawn\Others\TpaInfo;
use supercrafter333\theSpawn\Others\WarpInfo;
use supercrafter333\theSpawn\Tasks\SpawnDelayTask;
use function array_filter;
use function array_map;
use function array_merge;
use function file_exists;
use function is_numeric;
use function krsort;
use function mb_substr;
use function print_r;
use function str_starts_with;

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
     * @var Config
     */
    public Config $aliasCfg;

    /**
     * @var array
     */
    public array $TPAs = [];

    /**
     * @var array
     */
    public array $spawnDelays = [];

    /**
     * @var Config
     */
    public Config $warpCfg;

    /**
     * @var string
     */
    public string $version = "1.7.0-dev";


    public const DEVELOPMENT_VERSION = true;



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
        if (self::DEVELOPMENT_VERSION) $this->getLogger()->warning("You're using a development version of theSpawn!! This version can contain bugs, please report them on github!");

        $this->saveResource("config.yml");
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $cmdMap = $this->getServer()->getCommandMap();
        # Version Check
        //$this->versionCheck($this->version, true); //UPDATE CONFIG DATAs.
        $cfgVersion = $this->getConfig()->get("version");
        $this->versionCheck($this->version, ($cfgVersion < "1.7.1"));
        ###

        $this->registerPermissions();

        $this->msgCfg = MsgMgr::getMsgs();
        self::$prefix = MsgMgr::getPrefix();
        @mkdir($this->getDataFolder() . "homes");
        @mkdir($this->getDataFolder() . "Languages");
        if (strtolower(MsgMgr::getMessagesLanguage()) == "custom") {
            $this->saveResource("Languages/messages.yml");
        }
        $this->aliasCfg = new Config($this->getDataFolder() . "aliaslist.yml", Config::YAML);
        $this->warpCfg = new Config($this->getDataFolder() . "warps.yml", Config::YAML);
        $cmdMap->registerAll("theSpawn",
            [
                new SpawnCommand("spawn"),
                new SetspawnCommand("setspawn"),
                new DelspawnCommand("delspawn"),
                new HubCommand("hub"),
                new SethubCommand("sethub"),
                new DelhubCommand("delhub")
            ]);
        if ($this->useAliases() == true) {
            $cmdMap->registerAll("theSpawn",
                [
                    new SetaliasCommand("setalias"),
                    new RemovealiasCommand("removealias")
                ]);
            $this->reactivateAliases();
        }
        if ($this->useHomes() == true) {
            $cmdMap->registerAll("theSpawn",
                [
                    new SethomeCommand("sethome"),
                    new DelhomeCommand("delhome"),
                    new HomeCommand("home")
                ]);
        }
        if ($this->useWarps() == true) {
            $cmdMap->registerAll("theSpawn",
                [
                    new SetwarpCommand("setwarp"),
                    new DelwarpCommand("delwarp"),
                    new WarpCommand("warp")
                ]);
        }
        if ($this->useTPAs() == true) {
            $cmdMap->registerAll("theSpawn",
                [
                    new TpaCommand("tpa"),
                    new TpaHereCommand("tpahere"),
                    new TpacceptCommand("tpaccept"),
                    new TpdeclineCommand("tpdecline")
                ]);
        }
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
     * @return Config
     */
    public function getMsgCfg(): Config
    {
        return MsgMgr::getMsgs();
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
     */
    private function versionCheck($version, bool $update = true)
    {
        if (!$this->getConfig()->exists("version") || $this->getConfig()->get("version") !== $version) {
            if ($update == true) {
                $this->convertOldWarpPermissions(); //TODO: remove after v1.7.x
                $this->getLogger()->debug("OUTDATED CONFIG.YML!! You config.yml is outdated! Your config.yml will automatically updated!");
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
        if (strtolower(MsgMgr::getMessagesLanguage()) == "custom" && (!$this->getMsgCfg()->exists("version") || $this->getMsgCfg()->get("version") !== $version)) {
            if ($update == true) {
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

        foreach ($defaultPerms as $perm) {
            $bypassPerm->addChild($perm, true);
        }
    }

    /**
     * @return array
     */
    public function getTPAs(): array
    {
        return $this->TPAs;
    }

    /**
     * @param string $source
     * @return array|null
     */
    public function getTpaOf(string $source): ?array
    {
        if (!isset($this->TPAs[$source])) return null;
        return $this->TPAs[$source];
    }

    /**
     * @param string $source
     * @param string $target
     * @param bool $isTpaHere
     * @return bool
     */
    public function addTpa(string $source, string $target, bool $isTpaHere = false): bool
    {
        if (isset($this->TPAs[$source])) return false;
        $arr = ["target" => $target, "isTpaHere" => $isTpaHere];
        $this->TPAs[$source] = $arr;
        return true;
    }

    /**
     * @param string $source
     * @param Task $task
     * @return bool
     */
    public function setTpaTask(string $source, Task $task): bool
    {
        if ($this->getTpaOf($source) === null) return false;
        $tpaInfo = new TpaInfo($source);
        $target = $tpaInfo->getTarget();
        $isTpaHere = $tpaInfo->isTpaHere();
        $arr = ["target" => $target, "isTpaHere" => $isTpaHere, "task" => $task];
        unset($this->TPAs[$source]);
        $this->TPAs[] = $source;
        $this->TPAs[$source] = $arr;
        return true;
    }

    /**
     * @param string $source
     * @return bool
     */
    public function removeTpa(string $source): bool
    {
        if (!isset($this->TPAs[$source])) return false;
        unset($this->TPAs[$source]);
        return true;
    }

    /**
     * @param string $target
     * @param string $source
     * @return bool
     */
    public function hasTpaOf(string $target, string $source): bool
    {
        $tpaInfo = new TpaInfo($source);
        if ($tpaInfo->getTarget() === $target) return true;
        return false;
    }

    /**
     * @param string $target
     * @return array|null
     */
    public function getTPAsOf(string $target): ?array
    {
        $TPAs = $this->getTPAs();
        $newTPAs = [];
        foreach ($TPAs as $TPA) {
            if ($this->hasTpaOf($target, $TPA)) {
                $newTPAs[] = $TPA;
            }
        }
        if (count($newTPAs, COUNT_RECURSIVE) <= 0) return null;
        return $newTPAs;
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
     * @return Config
     */
    public function getRandomHubList(): Config
    {
        return new Config($this->getDataFolder() . "theRandHubs.yml", Config::YAML);
    }

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param World $world
     * @param int|null $count
     */
    public function setHub($x, $y, $z, World $world, int $count = null)
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        $randHub = new Config($this->getDataFolder() . "theRandHubs.yml", Config::YAML);
        $hubcoords = ["hub", "X" => $x, "Y" => $y, "Z" => $z, "level" => $world->getFolderName()];
        if ($count !== null && $this->getUseRandomHubs()) {
            $setRandHub = $x . '|' . $y . '|' . $z . '|' . $world->getFolderName();
            $randHub->set($count, $setRandHub);
            $randHub->save();
        } else {
            $hub->set("hub", $hubcoords);
            $hub->save();
        }
    }

    /**
     * @param int|null $count
     * @return Position|null
     */
    public function getRandomHub(int $count = null): ?Position
    {
        $randHubs = $this->getRandomHubList();
        if (!$this->getUseRandomHubs()) return null;
        if ($count === null) {
            $matches = [];
            if (!$randHubs->exists(1)) return null;
            foreach ($randHubs->getAll() as $all) {
                $matches[] = $all;
            }
            $matchCount = count($matches);
            return $this->getRandomHub(mt_rand(1, $matchCount));
        } else {
            $i = explode('|', $randHubs->get($count));
            $worldName = $i[3];
            if ($this->getHubLevel($worldName) instanceof World) {
                return new Position($i[0], $i[1], $i[2], $this->levelCheck($worldName));
            } else {
                return $this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
            }
        }
    }

    /**
     * @param int $count
     * @return bool
     */
    public function checkSetRandomHub(int $count): bool
    {
        $randHubs = $this->getRandomHubList();
        if ($randHubs->exists(($count - 1)) || $count == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $count
     * @return bool
     */
    public function checkRemoveRandomHub(int $count): bool
    {
        $randHubs = $this->getRandomHubList();
        if (!$randHubs->exists(($count + 1)) || $count == 1) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param int|null $count
     * @return Position|false
     */
    public function getHub(int $count = null): ?Position
    {
        $prefix = "§f[§7the§eSpawn§f] §8»§r ";
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        if ($count !== null && $this->getUseRandomHubs()) {
            return $this->getRandomHub($count) === null ? false : $this->getRandomHub($count);
        }
        if ($this->getUseRandomHubs()) {
            return $this->getRandomHub() === null ? false : $this->getRandomHub();
        }
        if ($hub->exists("hub")) {
            $X = $hub->get("hub")["X"];
            $Y = $hub->get("hub")["Y"];
            $Z = $hub->get("hub")["Z"];
            $worldname = $hub->get("hub")["level"];
            $world = !$this->checkWorld($worldname) instanceof World ? $this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn() : $this->checkWorld($worldname);
            $coords = new Position($X, $Y, $Z, $world);
            return $coords;
        } else {
            $this->getLogger()->error("!!Please set a Hub!!");
            return $this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
        }
    }

    /**
     * @param World|null $world
     * @return false|Position
     * @return false|Position
     */
    public function getSpawn(?World $world): Position|false
    {
        if (!$world instanceof World) {
            $hub = $this->getHub();
            if (!$hub instanceof Position) {
                return $this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
            }
            return $hub;
        }

        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $spawn->get($world->getFolderName());
        if ($spawn->exists($world->getFolderName())) {
            $X = $spawn->get($world->getFolderName())["X"];
            $Y = $spawn->get($world->getFolderName())["Y"];
            $Z = $spawn->get($world->getFolderName())["Z"];
            return new Position($X, $Y, $Z, $world);
        } else {
            return false;
        }
    }

    /**
     * @param Player $s
     * @param World $world
     * @return bool
     */
    public function setSpawn(Player $s, World $world): bool
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $x = $s->getPosition()->getX();
        $y = $s->getPosition()->getY();
        $z = $s->getPosition()->getZ();
        $coords = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $world->getFolderName()];
        $spawn->set($world->getFolderName(), $coords);
        $spawn->save();
        return true;
    }

    /**
     * @return false|mixed|World
     */
    public function getHubLevel(string $worldName): ?World
    {
        if (!$this->getServer()->getWorldManager()->isWorldGenerated($worldName)) return null;
        if (!$this->getServer()->getWorldManager()->isWorldLoaded($worldName)) {
            $this->getServer()->getWorldManager()->loadWorld($worldName);
            return $this->getServer()->getWorldManager()->getWorldByName($worldName);
        }
        return $this->getServer()->getWorldManager()->getWorldByName($worldName);
        //TODO: return $this->checkWorld($worldName); ???
    }

    /**
     * @param int|null $count
     * @return bool
     */
    public function removeHub(int $count = null): bool
    {
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        $randHubs = $this->getRandomHubList();
        if ($count !== null && $this->getUseRandomHubs()) {
            if ($randHubs->exists($count)) {
                $hub->remove("hub");
                $hub->save();
                return true;
            } else {
                return false;
            }
        } elseif ($hub->exists("hub")) {
            $hub->remove("hub");
            $hub->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param World $world
     * @return bool
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
            $this->getLogger()->alert("INFORMATION: Plase disable 'use-hub-server' in the config.yml to use random hubs!");
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
        if ($config->get("use-hub-server") === "true") {
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
        if ($this->getConfig()->get("use-aliases") == "true") {
            return true;
        }
        return false;
    }

    /**
     * @param string $alias
     * @return string
     */
    public function getWorldOfAlias(string $alias): string
    {
        return $this->aliasCfg->get($alias);
    }

    /**
     * @param string $worldName
     * @return bool
     */
    public function existsLevel(string $worldName): bool
    {
        if ($this->getServer()->getWorldManager()->isWorldGenerated($worldName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function existsAlias(string $alias): bool
    {
        if ($this->aliasCfg->exists($alias)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function rmAlias(string $alias): bool
    {
        if ($this->existsAlias($alias) == true) {
            $cmd = $this->getServer()->getCommandMap()->getCommand($alias);
            $this->getServer()->getCommandMap()->unregister($cmd);
            $this->aliasCfg->remove($alias);
            $this->aliasCfg->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $alias
     * @param string $worldName
     * @return bool
     */
    public function addAlias(string $alias, string $worldName): bool
    {
        $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        if ($this->getSpawn($world) == false) {
            return false;
        }
        $this->aliasCfg->set($alias, $worldName);
        $this->aliasCfg->save();
        $this->getServer()->getCommandMap()->register("theSpawn", new Aliases($this, $alias, str_replace(["{alias}"], [$alias], str_replace(["{world}"], [$worldName], MsgMgr::getMsg("alias-command-description")))));
        return true;
    }


    /**
     *
     */
    public function reactivateAliases()
    {
        foreach ($this->aliasCfg->getAll() as $cmd => $worldName) {
            $this->getServer()->getCommandMap()->register("theSpawn", new Aliases($this, $cmd, str_replace(["{alias}"], [$cmd], str_replace(["{world}"], [$worldName], MsgMgr::getMsg("alias-command-description")))));
        }
    }

    /**
     * @param string $worldName
     * @return World
     */
    public function levelCheck(string $worldName): World
    {
        if (!$this->getServer()->getWorldManager()->isWorldLoaded($worldName)) {
            $this->getServer()->getWorldManager()->loadWorld($worldName);
        }
        return $this->getServer()->getWorldManager()->getWorldByName($worldName);
    }

    /**
     * @param string $playerName
     * @return Config
     */
    public function getHomeCfg(string $playerName): Config
    {
        return new Config($this->getDataFolder() . "homes/" . $playerName . ".yml", Config::YAML);
    }

    /**
     * @param string $homeName
     * @param Player $player
     * @return bool
     */
    public function existsHome(string $homeName, Player $player): bool
    {
        if (file_exists($this->getDataFolder() . "homes/" . $player->getName() . ".yml")) {
            if ($this->getHomeCfg($player->getName())->exists($homeName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Player $player
     * @param string $homeName
     * @param $x
     * @param $y
     * @param $z
     * @param World $world
     * @return bool
     */
    public function setHome(Player $player, string $homeName, $x, $y, $z, World $world): bool
    {
        if ($this->existsHome($homeName, $player) == false) {
            $home = $this->getHomeCfg($player->getName());
            $setThis = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $world->getFolderName(), "homeName" => $homeName];
            $home->set($homeName, $setThis);
            $home->save();
            return true;
        }
        return false;
    }

    /**
     * @param Player $player
     * @param string $homeName
     * @return bool
     */
    public function rmHome(Player $player, string $homeName): bool
    {
        if ($this->existsHome($homeName, $player) == true) {
            $home = $this->getHomeCfg($player->getName());
            $home->remove($homeName);
            $home->save();
            return true;
        }
        return false;
    }

    /**
     * @param Player $player
     * @param string $homeName
     * @return Position|bool|string
     */
    public function getHomePos(Player $player, string $homeName): Position|bool|string
    {
        if ($this->existsHome($homeName, $player) == true) {
            $home = $this->getHomeCfg($player->getName());
            $x = $home->get($homeName)["X"];
            $y = $home->get($homeName)["Y"];
            $z = $home->get($homeName)["Z"];
            $worldName = $home->get($homeName)["level"];
            if ($this->getServer()->getWorldManager()->isWorldGenerated($worldName)) {
                if (!$this->getServer()->getWorldManager()->isWorldLoaded($worldName)) {
                    $this->getServer()->getWorldManager()->loadWorld($worldName);
                }
                $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
                return new Position($x, $y, $z, $world);
            } else {
                return "LevelError";
            }
        } else {
            return false;
        }
    }

    /**
     * @param Player $player
     * @param string $homeName
     * @return bool|string
     */
    public function teleportToHome(Player $player, string $homeName): bool|string
    {
        if ($this->existsHome($homeName, $player) == true) {
            if ($this->getHomePos($player, $homeName) == false) {
                return false;
            } elseif ($this->getHomePos($player, $homeName) == "LevelError") {
                return "LevelError";
            } else {
                $pos = $this->getHomePos($player, $homeName);
                $player->teleport($pos);
                return true;
            }
        }
        return false;
    }

    /**
     * @param Player $player
     * @return string|null
     */
    public function listHomes(Player $player): ?string
    {
        $homes = null;
        if (file_exists($this->getDataFolder() . "homes/" . $player->getName() . ".yml")) {
            $home = $this->getHomeCfg($player->getName());
            $all = $home->getAll();
            $getRight = $all;
            foreach ($getRight as $homex => $homez) {
                $right = [$homez["homeName"] . ", "];
                $homes .= implode(", ", $right);
            }
            return $homes;
        }
        return $homes;
    }

    /**
     * @param Player $player
     * @param string $homeName
     * @return HomeInfo|null
     */
    public function getHomeInfo(Player $player, string $homeName): ?HomeInfo
    {
        return $this->existsHome($homeName, $player) ? new HomeInfo($player, $homeName) : null;
    }

    public function getHomesOfPlayer(Player $player): array|null
    {
        $file = theSpawn::getInstance()->getDataFolder() . "homes/" . $player->getName() . ".yml";

        if (!file_exists($file)) return null;

        return (new Config($file, Config::YAML))->getAll(true);
    }

    public function getMaxHomesOfPlayer(Player $player): int //copied from MyPlot (by jasonwynn10)
    {
		if($player->hasPermission("theSpawn.homes.unlimited") || !$this->useMaxHomePermissions()) return PHP_INT_MAX;

		$perms = array_map(fn(PermissionAttachmentInfo $attachment) => [$attachment->getPermission(), $attachment->getValue()], $player->getEffectivePermissions());
		$perms = array_merge(PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER)->getChildren(), $perms);
		$perms = array_filter($perms, function(string $name) : bool {
			return (str_starts_with($name, "theSpawn.homes."));
		}, ARRAY_FILTER_USE_KEY);
		if(count($perms) === 0)
			return 0;
		krsort($perms, SORT_FLAG_CASE | SORT_NATURAL);
		/**
		 * @var string $name
		 * @var Permission $perm
		 */
		foreach($perms as $name => $perm) {
			$maxHomes = mb_substr($name, 15);
			if(is_numeric($maxHomes)) {
				return (int) $maxHomes;
			}
		}
		return 0;
	}

    public function useMaxHomePermissions(): bool
    {
        if ($this->getConfig()->get("use-max-home-permissions") == "true" || $this->getConfig()->get("use-max-home-permissions") == "on") return true;
        return false;
    }

    /**
     * @return bool
     */
    public function useHomes(): bool
    {
        if ($this->getConfig()->get("use-homes") == "true" || $this->getConfig()->get("use-homes") == "on") {
            return true;
        } else {
            return false;
        }
    }

    /**
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
     * @return Config
     */
    public function getWarpCfg(): Config
    {
        $cfg = new Config($this->getDataFolder() . "warps.yml", Config::YAML);
        return $cfg;
    }

    /**
     * @param string $warpName
     * @return bool
     */
    public function existsWarp(string $warpName): bool
    {
        return $this->getWarpCfg()->exists($warpName);
    }

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param World $level
     * @param string $warpName
     * @param bool $permission
     * @param string|null $iconPath
     * @return bool
     * @throws JsonException
     */
    public function addWarp($x, $y, $z, World $level, string $warpName, bool $permission = false, string|null $iconPath = null): bool
    {
        //if ($this->existsWarp($warpName) == true) {
        $warp = $this->getWarpCfg();
        $warpArray = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $level->getFolderName(), "warpName" => $warpName];

        if ($permission) $warpArray["perm"] = true;
        if ($iconPath !== null && $iconPath !== "") $warpArray["iconPath"] = $iconPath;

        $warp->set($warpName, $warpArray);
        $warp->save();
        return true;
        //}
        //return false;
    }

    /**
     * @param string $warpName
     * @throws JsonException
     */
    public function removeWarp(string $warpName)
    {
        $warp = $this->getWarpCfg();
        if ($this->existsWarp($warpName) == true) {
            $warp->remove($warpName);
            $warp->save();
        }
    }

    /**
     * @param string $warpName
     * @return false|Position
     */
    public function getWarpPosition(string $warpName): Position|bool
    {
        $warpCfg = $this->getWarpCfg();
        if ($this->existsWarp($warpName) == false) {
            return false;
        }
        $warp = $warpCfg->get($warpName);
        $x = $warp["X"];
        $y = $warp["Y"];
        $z = $warp["Z"];
        $worldName = $warp["level"];
        if (!$this->getServer()->getWorldManager()->isWorldGenerated($worldName)) {
            return false;
        }
        if ($this->getServer()->getWorldManager()->isWorldLoaded($worldName)) {
            $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
            return new Position($x, $y, $z, $world);
        } else {
            $this->getServer()->getWorldManager()->loadWorld($worldName);
            $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
            return new Position($x, $y, $z, $world);
        }
    }

    /**
     * @return bool
     */
    public function useWarps(): bool
    {
        if ($this->getConfig()->get("use-warps") == "true" || $this->getConfig()->get("use-warps") == "on") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $warpName
     * @return WarpInfo|null
     */
    public function getWarpInfo(string $warpName): ?WarpInfo
    {
        return WarpInfo::getWarpInfo($warpName);
    }

    /**
     * @return string|null
     */
    public function listWarps(): ?string
    {
        $warps = null;
        if (file_exists($this->getDataFolder() . "warps.yml")) {
            $warp = $this->getWarpCfg();
            $all = $warp->getAll();
            $getRight = $all;
            foreach ($getRight as $warpx => $warpz) {
                $right = [$warpz["warpName"] . ", "];
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
        if ($this->getConfig()->get("use-tpas") == "true" || $this->getConfig()->get("use-tpas") == "on") {
            return true;
        } else {
            return false;
        }
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
     * @return bool
     */
    public function useForms(): bool
    {
        if ($this->getConfig()->get("use-forms") == "true" || $this->getConfig()->get("use-forms") == "on") {
            return true;
        } else {
            return false;
        }
    }

    private function convertOldWarpPermissions(): void
    {
        $cfg = $this->getWarpCfg();
        foreach ($cfg->getAll() as $warp => $warpArray) {
            if (isset($warpArray["perm"]) && $warpArray["perm"] !== true && $warpArray["perm"] !== false) {
                $cfg->setNested($warpArray["warpName"] . ".perm", "true");
                $cfg->save();
            }
        }
    }
}