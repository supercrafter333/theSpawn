<?php

namespace supercrafter333\theSpawn;

use JsonException;
use pocketmine\block\{Air,
    Crops,
    DoubleTallGrass,
    Flower,
    Grass,
    ItemFrame,
    Liquid,
    Sapling,
    SnowLayer,
    TallGrass,
    Torch};
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\permission\PermissionManager;
use pocketmine\player\IPlayer;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use supercrafter333\theSpawn\commands\{BackCommand, PlayerWarpCommand};
use supercrafter333\theSpawn\commands\alias\{AliasesCommand, AliasManager, RemovealiasCommand, SetaliasCommand};
use supercrafter333\theSpawn\commands\home\{DelhomeCommand, EdithomeCommand, HomeCommand, SethomeCommand};
use supercrafter333\theSpawn\commands\hub\{DelhubCommand, HubCommand, SethubCommand};
use supercrafter333\theSpawn\commands\spawn\{DelspawnCommand, SetspawnCommand, SpawnCommand};
use supercrafter333\theSpawn\commands\tpa\{TpacceptCommand, TpaCommand, TpaHereCommand, TpdeclineCommand};
use supercrafter333\theSpawn\commands\warp\{DelwarpCommand, EditwarpCommand, SetwarpCommand, WarpCommand};
use supercrafter333\theSpawn\home\HomeManager;
use supercrafter333\theSpawn\task\SpawnDelayTask;
use supercrafter333\theSpawn\warp\WarpManager;
use function file_exists;
use function implode;
use function phpversion;
use function sort;
use function str_contains;
use function strtolower;

/**
 * Class theSpawn
 * @package supercrafter333\theSpawn
 */
class theSpawn extends PluginBase
{

    /**
     * Displays the minimum php version for running theSpawn.
     */
    public const MIN_PHP_VERSION = "8.1.0";

    /**
     * Displays the minimum config.yml version for running theSpawn.
     */
    public const MIN_CONFIG_VERSION = "2.0.0";


    public static theSpawn $instance;

    public static string $prefix;

    /**
     * @var SpawnDelayTask[]
     */
    public array $spawnDelays = [];



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
        if (phpversion() < self::MIN_PHP_VERSION) {
            $this->getLogger()->error("[WRONG PHP VERSION] - You're using a too old php version (" . phpversion() . ")!! The minimum php version is 8.1.0!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $pluginVersion = $this->getDescription()->getVersion();

        if (str_contains(strtolower($pluginVersion), "dev"))
            $this->getLogger()->warning("You're using a development version of theSpawn ({$pluginVersion})!! This version can contain bugs, please report them on github!");

        $this->saveResource("config.yml");
        @mkdir($this->getDataFolder() . "homes");
        @mkdir($this->getDataFolder() . "Languages");
        if (strtolower(MsgMgr::getMessagesLanguage()) == "custom")
            $this->saveResource("Languages/messages.yml");

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $cmdMap = $this->getServer()->getCommandMap();
        # Version Check
        $cfgVersion = $this->getConfig()->get("version");
        $this->versionCheck($pluginVersion, ($cfgVersion < self::MIN_CONFIG_VERSION));
        ###

        $this->registerPermissions();
        self::$prefix = MsgMgr::getPrefix();
        $cfgMgr = ConfigManager::getInstance();
        if ($cfgMgr->useSpawns())
            $cmdMap->registerAll("theSpawn",
            [
                new SpawnCommand("spawn"),
                new SetspawnCommand("setspawn"),
                new DelspawnCommand("delspawn")
            ]);
        if ($cfgMgr->useHub() || $cfgMgr->useRandomHubs())
            $cmdMap->registerAll("theSpawn",
            [
                new HubCommand("hub"),
                new SethubCommand("sethub"),
                new DelhubCommand("delhub")
            ]);
        if ($cfgMgr->useAliases()) {
            $cmdMap->registerAll("theSpawn",
                [
                    new SetaliasCommand("setalias"),
                    new RemovealiasCommand("removealias"),
                    new AliasesCommand("aliases")
                ]);
            AliasManager::reactivateAliases();
        }
        if ($cfgMgr->useHomes()) {
            $cmdMap->registerAll("theSpawn",
                [
                    new SethomeCommand("sethome"),
                    new DelhomeCommand("delhome"),
                    new HomeCommand("home")
                ]);
            if ($cfgMgr->useForms()) $cmdMap->register("theSpawn", new EdithomeCommand("edithome"));
        }
        if ($cfgMgr->useWarps()) {
            $cmdMap->registerAll("theSpawn",
                [
                    new SetwarpCommand("setwarp"),
                    new DelwarpCommand("delwarp"),
                    new WarpCommand("warp")
                ]);
            if ($cfgMgr->useForms()) $cmdMap->register("theSpawn", new EditwarpCommand("editwarp"));
        }
        if ($cfgMgr->useTPAs())
            $cmdMap->registerAll("theSpawn",
                [
                    new TpaCommand("tpa"),
                    new TpaHereCommand("tpahere"),
                    new TpacceptCommand("tpaccept"),
                    new TpdeclineCommand("tpdecline")
                ]);
        if ($cfgMgr->useBackCommand()) $cmdMap->register("theSpawn", new BackCommand("back"));
        if ($cfgMgr->usePlayerWarps()) $cmdMap->register("theSpawn", new PlayerWarpCommand("playerwarp",
        "theSpawn.playerwarp.cmd",
        "Create, remove or teleport you to a player-warp.",
            "§4Usage: §r/playerwarp <create|remove|list|teleport>", ["pwarp"]));

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
            "theSpawn.playerwarp.cmd",
            "theSpawn.spawn.cmd",
            "theSpawn.hub.cmd",
            "theSpawn.alias.cmd",

            # ADMIN PERMISSIONS:
            "theSpawn.warp.admin",
            "theSpawn.homes", #all home permissions
            "theSpawn.homes.unlimited",
            "theSpawn.pwarps.unlimited"
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

    /**
     * @param Player $s
     * @return bool
     */
    public function teleportToHubServer(Player $s): bool
    {
        $config = $this->getConfig();
        if (ConfigManager::getInstance()->useHubServer())
            return $s->transfer($config->get("hub-server-ip"), $config->get("hub-server-port"));
        else return false;
    }

    /**
     * @param Player $player
     * @return string|null
     */
    public function listHomes(IPlayer $player): ?string
    {
        $homes = HomeManager::getHomesOfPlayer($player);
        sort($homes);
        return count($homes) > 0 ? implode(", ", $homes) : null;
    }

    /**
     * @return string|null
     */
    public function listWarps(): ?string
    {
        if (file_exists($this->getDataFolder() . "warps.yml")) {
            $warp = WarpManager::getWarpConfig();
            $all = $warp->getAll(true);
            sort($all);
            if (count($all) > 0)
                return implode(", ", $all);
        }

        return null;
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
     * @param Position|Location|false|null $position
     * @return bool
     */
    public function isPositionSafe(Position|Location|null|false $position): bool
    {
        if ($position === null || $position === false) return false;

        if ($position->world === null || !$this->checkWorld($position->world->getFolderName()) instanceof World) return false;

        if (!ConfigManager::getInstance()->usePositionChecks()) return true;

        if (!$position->isValid() || $position->getY() < $position->getWorld()->getMinY()) return false;

        $block0 = $position->getWorld()->getBlock(new Vector3($position->getX(), $position->getY(), $position->getZ()));
        $block1 = $position->getWorld()->getBlock(new Vector3($position->getX(), $position->getY() + 1, $position->getZ()));
        $block2 = $position->getWorld()->getBlock(new Vector3($position->getX(), $position->getY() + 2, $position->getZ()));

        $blocksToCheck = [];

        $blocksToCheck[] = $block0->getPosition()->getWorld()->getBlock(new Vector3($block0->getPosition()->getX() + 1, $block0->getPosition()->getY(), $block0->getPosition()->getZ()));
        $blocksToCheck[] = $block0->getPosition()->getWorld()->getBlock(new Vector3($block0->getPosition()->getX() - 1, $block0->getPosition()->getY(), $block0->getPosition()->getZ()));
        $blocksToCheck[] = $block0->getPosition()->getWorld()->getBlock(new Vector3($block0->getPosition()->getX(), $block0->getPosition()->getY(), $block0->getPosition()->getZ() + 1));
        $blocksToCheck[] = $block0->getPosition()->getWorld()->getBlock(new Vector3($block0->getPosition()->getX(), $block0->getPosition()->getY(), $block0->getPosition()->getZ() - 1));
        
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

        if($block0 instanceof Air && $block1 instanceof Air && ($block2 instanceof Air || !$block2->isFullCube())) return true;

        if (($block0 instanceof Air || $block0 instanceof Torch || $block0 instanceof Flower || $block0 instanceof Grass || $block0 instanceof TallGrass || $block0 instanceof DoubleTallGrass || $block0 instanceof Crops || $block0 instanceof Sapling || $block0 instanceof ItemFrame || $block0 instanceof SnowLayer)
        && ($block1 instanceof Air || $block1 instanceof Torch || $block1 instanceof Flower || $block1 instanceof Grass || $block1 instanceof TallGrass || $block1 instanceof DoubleTallGrass || $block1 instanceof Crops || $block1 instanceof Sapling || $block1 instanceof ItemFrame || $block0 instanceof SnowLayer)
        && ($block2 instanceof Air || $block2 instanceof Torch || $block2 instanceof Flower || $block2 instanceof Grass || $block2 instanceof TallGrass || $block2 instanceof DoubleTallGrass || $block2 instanceof Crops || $block2 instanceof Sapling || $block2 instanceof ItemFrame))
            return true;

        return false;
    }


    /**
     * @param World|null $world
     * @return Position|Location|false
     */
    public function getSpawn(World|null $world): Position|Location|false
    {
        if (!ConfigManager::getInstance()->useSpawns()) return false;

        if (!$world instanceof World) {
            $hub = HubManager::getInstance()->getHub();
            if (!$hub instanceof Position) {
                return $this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
            }
            return $hub;
        }

        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $spawn->get($world->getFolderName());
        if ($spawn->exists($world->getFolderName()))
            return LocationHelper::legacyConvertArrayToPosition($spawn->get($world->getFolderName(), []));

        return false;
    }

    /**
     * @param Player $s
     * @param World $world
     * @return bool
     * @throws JsonException
     */
    public function setSpawn(Player $s, World $world): bool
    {
        if (!ConfigManager::getInstance()->useSpawns()) return false;

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
}