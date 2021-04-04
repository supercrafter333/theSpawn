<?php

namespace supercrafter333\theSpawn;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\sound\PopSound;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
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
use supercrafter333\theSpawn\Commands\WarpCommand;
use supercrafter333\theSpawn\Others\HomeInfo;
use supercrafter333\theSpawn\Others\WarpInfo;

/**
 * Class theSpawn
 * @package supercrafter333\theSpawn
 */
class theSpawn extends PluginBase implements Listener
{

    /**
     * @var
     */
    public static $instance;

    /**
     *
     */
    public const PREFIX = "§f[§7the§eSpawn§f] §8»§r ";
    /**
     * @var
     */
    public $config;
    /**
     * @var
     */
    public $msgCfg;
    /**
     * @var
     */
    public $aliasCfg;

    /**
     * @var
     */
    public $warpCfg;

    /**
     * @var string
     */
    public $version = "1.2.1";

    /**
     *
     */
    public function onEnable()
    {
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $cmdMap = $this->getServer()->getCommandMap();
        $this->saveResource("messages.yml");
        $this->saveResource("config.yml");
        if ($this->checkCfgVersion($this->version) == false) {
            $this->updateCfg();
            $this->getLogger()->warning("The config.yml data was updated automatically for version " . $this->version . " of theSpawn!");
        }
        if (MsgMgr::checkMsgCfgVersion($this->version) == false) {
            MsgMgr::updateMsgCfg();
            $this->getLogger()->warning("The messages.yml data was updated automatically for version " . $this->version . " of theSpawn!");
        }
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->msgCfg = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        @mkdir($this->getDataFolder() . "homes");
        $this->aliasCfg = new Config($this->getDataFolder() . "aliaslist.yml", Config::YAML);
        $this->warpCfg = new Config($this->getDataFolder() . "warps.yml", Config::YAML);
        $aliasCfg = new Config($this->getDataFolder() . "aliaslist.yml", Config::YAML);
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
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function getCfg()
    {
        return new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    /**
     * @return mixed
     */
    public function getMsgCfg()
    {
        return MsgMgr::getMsgs();
    }

    /**
     * @param string $version
     * @return bool
     */
    public function checkCfgVersion(string $version): bool
    {
        if ($this->getCfg()->exists("version")) {
            if ($this->getCfg()->get("version") == $version) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     */
    public function updateCfg()
    {
        unlink($this->getDataFolder() . "config.yml");
        $this->saveResource("config.yml");
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event)
    {
        $prefix = "§f[§7the§eSpawn§f] §8»§r ";
        $s = $event->getPlayer();
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $levelname = $s->getLevel()->getName();
        $level = $this->getServer()->getLevelByName($levelname);
        if ($spawn->exists($levelname)) {
            $event->setRespawnPosition($this->getSpawn($level));
            $s->getLevel()->addSound(new PopSound($s));
        } elseif ($this->getHub() !== false) {
            $event->setRespawnPosition($this->getHub());
            $s->getLevel()->addSound(new PopSound($s));
        } else {
            if ($level->getSafeSpawn() === null) {
                $event->setRespawnPosition($this->getServer()->getDefaultLevel()->getSafeSpawn());
            } else {
                $event->setRespawnPosition($level->getSafeSpawn());
            }
            $s->getLevel()->addSound(new PopSound($s));
        }
        /*if ($this->getSpawn($levelname)) {
            if ($this->getServer()->isLevelLoaded($levelname) == true && !$level == null) {
                $event->setRespawnPosition(new Position($X, $Y, $Z, $level));
                $s->getLevel()->addSound(new PopSound($s));
            } elseif ($level == null) {
                $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found"));
                $s->teleport($this->getHub());
                $s->kick(MsgMgr::getMsg("no-spawn-found-kick"));
            } elseif (!$this->getServer()->isLevelLoaded($levelname)) {
                $this->getServer()->loadLevel($levelname);
                $event->setRespawnPosition(new Position($X, $Y, $Z, $level));
                $s->getLevel()->addSound(new PopSound($s));
            }
        }*/
    }

    /*public function isTpToHubOnRepawnEnabled(): bool
    {
        $use = $this->getCfg()->get("teleport-to-hub-on-respawn");
        if ($use == "true") {
            return true;
        } else {
            return false;
        }
    }*/

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param Level $level
     * @return bool
     */
    public function setHub($x, $y, $z, Level $level): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        $hubcoords = ["hub", "X" => $x, "Y" => $y, "Z" => $z, "level" => $level->getName()];
        $hub->set("hub", $hubcoords);
        $hub->save();
        return true;
    }

    /**
     * @return false|Position
     */
    public function getHub()
    {
        $prefix = "§f[§7the§eSpawn§f] §8»§r ";
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        if ($hub->exists("hub")) {
            $X = $hub->get("hub")["X"];
            $Y = $hub->get("hub")["Y"];
            $Z = $hub->get("hub")["Z"];
            $levelname = $hub->get("hub")["level"];
            $level = $this->getServer()->getLevelByName($levelname);
            $coords = new Position($X, $Y, $Z, $level);
            return $coords;
        } else {
            $this->getLogger()->error("!!Please set a Hub!!");
            return false;
        }
    }

    /**
     * @param Level $level
     * @return false|Position
     * @return false|Position
     */
    public function getSpawn(Level $level)
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $spawn->get($level->getName());
        if ($spawn->exists($level->getName())) {
            $X = $spawn->get($level->getName())["X"];
            $Y = $spawn->get($level->getName())["Y"];
            $Z = $spawn->get($level->getName())["Z"];
            return new Position($X, $Y, $Z, $level);
        } else {
            return false;
        }
    }

    /**
     * @param Player $s
     * @param Level $level
     * @return bool
     */
    public function setSpawn(Player $s, Level $level): bool
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $x = $s->getX();
        $y = $s->getY();
        $z = $s->getZ();
        $coords = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $level->getName()];
        $spawn->set($level->getName(), $coords);
        $spawn->save();
        return true;
    }

    /**
     * @return false|mixed
     */
    public function getHubLevel(): string
    {
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        if ($hub->exists("hub")) {
            $levelname = $hub->get("hub")["level"];
            return $levelname;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function removeHub(): bool
    {
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        if ($hub->exists("hub")) {
            $hub->remove("hub");
            return $hub->save();
        } else {
            return false;
        }
    }

    /**
     * @param Level $level
     * @return bool
     */
    public function removeSpawn(Level $level): bool
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        if ($spawn->exists($level->getName())) {
            $spawn->remove($level->getName());
            return $spawn->save();
        } else {
            return false;
        }
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
        if ($this->config->get("use-aliases") == "true") {
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
     * @param string $levelName
     * @return bool
     */
    public function existsLevel(string $levelName): bool
    {
        if ($this->getServer()->isLevelGenerated($levelName)) {
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
     * @param string $levelName
     * @return bool
     */
    public function addAlias(string $alias, string $levelName): bool
    {
        $level = $this->getServer()->getLevelByName($levelName);
        if ($this->getSpawn($level) == false) {
            return false;
        }
        $this->aliasCfg->set($alias, $levelName);
        $this->aliasCfg->save();
        $this->getServer()->getCommandMap()->register("theSpawn", new Aliases($this, $alias, str_replace(["{alias}"], [$alias], str_replace(["{world}"], [$levelName], MsgMgr::getMsg("alias-command-description")))));
        return true;
    }

    /**
     *
     */
    public function reactivateAliases()
    {
        foreach ($this->aliasCfg->getAll() as $cmd => $worldName) {
            $this->getServer()->getCommandMap()->register($cmd, new Aliases($this, $cmd, str_replace(["{alias}"], [$cmd], str_replace(["{world}"], [$worldName], MsgMgr::getMsg("alias-command-description")))));
        }
    }

    /**
     * @param string $levelName
     * @return Level
     */
    public function levelCheck(string $levelName): Level
    {
        if ($this->getServer()->isLevelLoaded($levelName)) {
            $level = $this->getServer()->getLevelByName($levelName);
            return $level;
        } else {
            $this->getServer()->loadLevel($levelName);
            $level = $this->getServer()->getLevelByName($levelName);
            return $level;
        }
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
     * @param Level $level
     * @return bool
     */
    public function setHome(Player $player, string $homeName, $x, $y, $z, Level $level): bool
    {
        if ($this->existsHome($homeName, $player) == false) {
            $home = $this->getHomeCfg($player->getName());
            $setThis = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $level->getName(), "homeName" => $homeName];
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
     * @return false|Position
     */
    public function getHomePos(Player $player, string $homeName)
    {
        if ($this->existsHome($homeName, $player) == true) {
            $home = $this->getHomeCfg($player->getName());
            $x = $home->get($homeName)["X"];
            $y = $home->get($homeName)["Y"];
            $z = $home->get($homeName)["Z"];
            $levelName = $home->get($homeName)["level"];
            if ($this->getServer()->isLevelGenerated($levelName)) {
                if ($this->getServer()->isLevelLoaded($levelName)) {
                    $level = $this->getServer()->getLevelByName($levelName);
                    return new Position($x, $y, $z, $level);
                } else {
                    $this->getServer()->loadLevel($levelName);
                    $level = $this->getServer()->getLevelByName($levelName);
                    return new Position($x, $y, $z, $level);
                }
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
    public function teleportToHome(Player $player, string $homeName)
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
    public function listHomes(Player $player)
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
     * @return HomeInfo
     */
    public function getHomeInfo(Player $player, string $homeName)
    {
        return new HomeInfo($player, $homeName);
    }

    /**
     * @return bool
     */
    public function useHomes(): bool
    {
        if ($this->getCfg()->get("use-homes") == "true" || $this->getCfg()->get("use-homes") == "on") {
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
        $player->sendDataPacket($pk);
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
        return WarpInfo::getWarpInfo($warpName)->exists();
    }

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param Level $level
     * @param string $warpName
     */
    public function addWarp($x, $y, $z, Level $level, string $warpName): bool
    {
        //if ($this->existsWarp($warpName) == true) {
        $warp = $this->getWarpCfg();
        $setThis = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $level->getName(), "warpName" => $warpName];
        $warp->set($warpName, $setThis);
        $warp->save();
        return true;
        //}
        //return false;
    }

    /**
     * @param string $warpName
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
    public function getWarpPosition(string $warpName)
    {
        $warpCfg = $this->getWarpCfg();
        if ($this->existsWarp($warpName) == false) {
            return false;
        }
        $warp = $warpCfg->get($warpName);
        $x = $warp["X"];
        $y = $warp["Y"];
        $z = $warp["Z"];
        $levelName = $warp["level"];
        if (!$this->getServer()->isLevelGenerated($levelName)) {
            return false;
        }
        if ($this->getServer()->isLevelLoaded($levelName)) {
            $level = $this->getServer()->getLevelByName($levelName);
            return new Position($x, $y, $z, $level);
        } else {
            $this->getServer()->loadLevel($levelName);
            $level = $this->getServer()->getLevelByName($levelName);
            return new Position($x, $y, $z, $level);
        }
    }

    /**
     * @return bool
     */
    public function useWarps(): bool
    {
        if ($this->getCfg()->get("use-warps") == "true" || $this->getCfg()->get("use-warps") == "on") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $warpName
     * @return WarpInfo
     */
    public function getWarpInfo(string $warpName)
    {
        return WarpInfo::getWarpInfo($warpName);
    }

    /**
     * @return string|null
     */
    public function listWarps()
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
}
