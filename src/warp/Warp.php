<?php

namespace supercrafter333\theSpawn\warp;

use JsonException;
use pocketmine\entity\Location;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permissible;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\permission\PermissionManager;

class Warp
{

    /**
     * @param Location $location
     * @param string $warpName
     * @param bool $permission
     * @param string|null $iconPath
     */
    public function __construct(private Location $location,
                                private readonly string $warpName, private bool $permission = false,
                                private string|null $iconPath = null)
    {}

    /**
     * @return string
     */
    public function getWarpName(): string
    {
        return $this->warpName;
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->warpName;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string|null
     */
    public function getIconPath(): ?string
    {
        return $this->iconPath;
    }

    /**
     * @param string|null $iconPath
     */
    public function setIconPath(?string $iconPath): void
    {
        $this->iconPath = $iconPath;
    }

    /**
     * @return bool
     */
    public function isPermissionEnabled(): bool
    {
        return $this->permission;
    }

    /**
     * @param bool $permission
     */
    public function setPermissionEnabled(bool $permission): void
    {
        $this->permission = $permission;
    }

    /**
     * Returns the permission of this warp. If the warp-permission is disabled for this warp, it will return null.
     *
     * @return string|null
     */
    public function getPermission(): ?string
    {
        $isPermEnabled = $this->isPermissionEnabled();

        if (!$isPermEnabled) return null;


        $perm = "theSpawn.warp." . $this->getName();

        if (PermissionManager::getInstance()->getPermission($perm) instanceof Permission) return $perm;

        $op = PermissionManager::getInstance()->getPermission(DefaultPermissionNames::GROUP_OPERATOR);
        $console = PermissionManager::getInstance()->getPermission(DefaultPermissionNames::GROUP_CONSOLE);

        DefaultPermissions::registerPermission(new Permission($perm, "Warp permission"), [$op, $console]);
        PermissionManager::getInstance()->getPermission("theSpawn.warp.admin")->addChild($perm, true);
        return $perm;
    }

    /**
     * Checks if the sender is having the warp-permission of this warp.
     *
     * @param Permissible $sender
     * @return bool
     */
    public function hasPermission(Permissible $sender): bool
    {
        if (($permission = $this->getPermission()) === null || $sender->hasPermission("theSpawn.warp.admin")) return true;

        $perms = array_map(fn(PermissionAttachmentInfo $attachment) => [$attachment->getPermission(), $attachment->getValue()], $sender->getEffectivePermissions());
        $perms = array_merge(PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER)->getChildren(), $perms);
        if (count($perms) === 0)
            return false;
        /**
         * @var string $name
         * @var Permission $perm
         */
        foreach ($perms as $name => $perm)
            if ($name == $permission) return true;
        return false;
    }

    /**
     * @throws JsonException
     */
    public function save(): void
    {
        WarpManager::saveWarp($this);
    }
}