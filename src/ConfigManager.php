<?php

namespace supercrafter333\theSpawn;

use EasyUI\EasyUI;
use pocketmine\utils\Config;

class ConfigManager
{

    /**
     * @param Config|null $config
     */
    public function __construct(private Config|null $config = null)
    {
        $this->config ??= theSpawn::getInstance()->getConfig();
    }

    /**
     * @param Config|null $config
     * @return ConfigManager
     */
    public static function getInstance(Config|null $config = null): ConfigManager
    {
        return new ConfigManager($config);
    }

    /**
     * @return Config|null
     */
    public function getConfig(): ?Config
    {
        return $this->config;
    }

    /**
     * @return bool
     */
    public function useAliases(): bool
    {
        return $this->isEnabled("use-aliases");
    }

    /**
     * @return bool
     */
    public function useHomes(): bool
    {
        return $this->isEnabled("use-homes");
    }

    /**
     * @return bool
     */
    public function useMaxHomePermissions(): bool
    {
        return $this->isEnabled("use-max-home-permissions");
    }

    /**
     * @return bool
     */
    public function useWarps(): bool
    {
        return $this->isEnabled("use-warps");
    }

    /**
     * @return bool
     */
    public function useTPAs(): bool
    {
        return $this->isEnabled("use-tpas");
    }

    /**
     * @return bool
     */
    public function usePlayerWarps(): bool
    {
        return $this->isEnabled("use-playerwarps");
    }

    /**
     * @return bool
     */
    public function useMaxPlayerWarpPermissions(): bool
    {
        return $this->isEnabled("use-max-playerwarps-permissions");
    }

    /**
     * @return bool
     */
    public function useSpawnDelays(): bool
    {
        return $this->isEnabled("use-spawnDelay");
    }

    /**
     * @return bool
     */
    public function usePositionChecks(): bool
    {
        return $this->isEnabled("check-positions");
    }

    /**
     * @return bool
     */
    public function useBackCommand(): bool
    {
        return $this->isEnabled("use-back-command");
    }

    /**
     * @return bool
     */
    public function useRandomHubs(): bool
    {
        $config = $this->getConfig();
        if ($config->get("use-hub-server") === "true" && $config->get("use-random-hubs") === "true") {
            theSpawn::getInstance()->getLogger()->alert("INFORMATION: Please disable 'use-hub-server' in the config.yml to use random hubs!");
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
    public function useHubServer(): bool
    {
        return $this->isEnabled("use-hub-server");
    }

    /**
     * @return bool
     */
    public function useHubTeleportOnDeath(): bool
    {
        if ($this->useHubServer() || (!$this->useHub() && !$this->useRandomHubs())) return false;
        return $this->isEnabled("hub-teleport-on-death");
    }

    public function useHubTeleportOnJoin(): bool
    {
        if ($this->useHubServer() || (!$this->useHub() && !$this->useRandomHubs())) return false;
        return $this->isEnabled("hub-teleport-on-join");
    }

    public function useSpawns(): bool
    {
        return $this->isEnabled("use-spawns");
    }

    public function useHub(): bool
    {
        return $this->isEnabled("use-hub");
    }

    public function useToastNotifications(): bool
    {
        return $this->isEnabled("use-toast-notifications");
    }

    /**
     * @return bool
     */
    public function useForms(): bool
    {
        return ($this->isEnabled("use-forms"))
            && class_exists(EasyUI::class);
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function isEnabled(string $key): bool
    {
        return $this->getConfig()->get($key) == "true" || $this->getConfig()->get($key) == "on";
    }
}