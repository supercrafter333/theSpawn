<?php

namespace supercrafter333\theSpawn\form;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\XpLevelUpSound;
use supercrafter333\theSpawn\commands\DelwarpCommand;
use supercrafter333\theSpawn\commands\SetwarpCommand;
use supercrafter333\theSpawn\commands\WarpCommand;
use supercrafter333\theSpawn\events\other\EditWarpEvent;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\warp\Warp;
use supercrafter333\theSpawn\warp\WarpManager;
use function mb_strlen;
use function str_contains;

/**
 *
 */
class WarpForms
{

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function open(Player $player): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) return;

            WarpCommand::simpleExecute($player, [$result]);
        });
        $form->setTitle(MsgMgr::getMsg("form-warp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-warp-menu-content"));
        foreach (WarpManager::getWarpConfig()->getAll(true) as $warp) {
            $warp = WarpManager::getWarp($warp);
            $warpName = $warp->getName();
            $warpIcon = $warp->getIconPath() === null ? "" : $warp->getIconPath();
            $iconType = $warpIcon === "" ? -1 : 0;
            if (str_contains($warpIcon, "http")) $iconType = 1;
            $form->addButton(str_replace(["{warp}", "{line}", "{player_count}"], [$warpName, "\n", count($warp->getLocation()->getWorld()->getPlayers())], MsgMgr::getMsg("form-warp-menu-warpButton")), $iconType, $warpIcon, $warpName);
        }
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function openRmWarp(Player $player): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) return;

            DelwarpCommand::simpleExecute($player, [$result]);
            return;
        });
        $form->setTitle(MsgMgr::getMsg("form-rmWarp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-rmWarp-menu-content"));
        foreach (WarpManager::getWarpConfig()->getAll(true) as $warp) {
            $warpInfo = WarpManager::getWarp($warp);
            $warpName = $warpInfo->getName();
            $warpIcon = $warpInfo->getIconPath() === null ? "" : $warpInfo->getIconPath();
            $iconType = $warpIcon === "" ? -1 : 0;
            if (str_contains($warpIcon, "http")) $iconType = 1;
            $form->addButton(str_replace(["{warp}", "{line}"], [$warpName, "\n"], MsgMgr::getMsg("form-rmWarp-menu-warpButton")), $iconType, $warpIcon, $warpName);
        }
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @return CustomForm
     */
    public function openSetWarp(Player $player): CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data === null) return;

            if (isset($data["warpName"])) {
                if ((isset($data["warpPermission"]) && $data["warpPermission"]) && isset($data["warpIconPath"])) {
                    SetwarpCommand::simpleExecute($player, [$data["warpName"], 'true', $data["warpIconPath"]]);
                    return;
                }
                if (isset($data["warpPermission"]) && $data["warpPermission"]) {
                    SetwarpCommand::simpleExecute($player, [$data["warpName"], 'true']);
                    return;
                }
                if (isset($data["warpIconPath"])) {
                    SetwarpCommand::simpleExecute($player, [$data["warpName"], 'false', $data["warpIconPath"]]);
                    return;
                }

                SetwarpCommand::simpleExecute($player, [$data["warpName"]]);
                return;
            }
        });
        $form->setTitle(MsgMgr::getMsg("form-setWarp-menu-title"));
        $form->addLabel(MsgMgr::getMsg("form-setWarp-menu-content"));
        $form->addInput(MsgMgr::getMsg("form-setWarp-menu-inputNameDescription"), "", null, "warpName");
        $form->addToggle(MsgMgr::getMsg("form-setWarp-menu-togglePermDescription"), false, "warpPermission");
        $form->addInput(MsgMgr::getMsg("form-setWarp-menu-inputIconPathDescription"), "", "textures/ui/world_glyph_color_2x_black_outline", "warpIconPath"); //ui/world_glyph_colour_2x_black_outline
        $form->sendToPlayer($player);
        return $form;
    }


    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function openChooseEditWarp(Player $player): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) return;

            if (($warp = WarpManager::getWarp($result)) === null) return;

            $this->openEditWarp($player, $warp);
        });
        $form->setTitle(MsgMgr::getMsg("form-chooseEditWarp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-chooseEditWarp-menu-content"));
        foreach (WarpManager::getWarpConfig()->getAll(true) as $warp) {
            $warpInfo = WarpManager::getWarp($warp);
            $warpName = $warpInfo->getName();
            $warpIcon = $warpInfo->getIconPath() === null ? "" : $warpInfo->getIconPath();
            $iconType = $warpIcon === "" ? -1 : 0;
            if (str_contains($warpIcon, "http")) $iconType = 1;
            $form->addButton(str_replace(["{warp}", "{line}"], [$warpName, "\n"], MsgMgr::getMsg("form-chooseEditWarp-menu-warpButton")), $iconType, $warpIcon, $warpName);
        }
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @param Warp $warp
     * @return SimpleForm
     */
    public function openEditWarp(Player $player, Warp $warp): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) use ($warp) {
            $result = $data;
            if ($result === null) return;

            $pl = theSpawn::getInstance();
            $warpName = $warp->getName();

            if ($result == "editName") {
                $this->openEditWarpName($player, $warp);
                return;
            }

            if ($result == "editPos") {
                if (!$this->canEditWarp($warp)) return;

                $warp->setLocation($player->getLocation());
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, WarpManager::getWarp($warpName));
                return;
            }

            if ($result == "rmPerm") {
                if (!$this->canEditWarp($warp)) return;

                $warp->setPermissionEnabled(false);
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, WarpManager::getWarp($warpName));
                return;
            }

            if ($result == "addPerm") {
                if (!$this->canEditWarp($warp)) return;

                $warp->setPermissionEnabled(true);
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, WarpManager::getWarp($warpName));
                return;
            }

            if ($result == "rmIcon") {
                if (!$this->canEditWarp($warp)) return;

                $warp->setIconPath(null);
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, WarpManager::getWarp($warpName));
                return;
            }

            if ($result == "editIcon") {
                $this->openEditWarpIcon($player, $warp);
                return;
            }

            if ($result == "addIcon") {
                $this->openEditWarpIcon($player, $warp, false);
                return;
            }
            return;
        });
        $loc = $warp->getLocation();
        $form->setTitle(MsgMgr::getMsg("form-editWarp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-editWarp-menu-content", [
            "{warp}" => $warp->getName(),
            "{world}" => $loc->getWorld()->getFolderName(),
            "{X}" => $loc->getX(),
            "{Y}" => $loc->getY(),
            "{Z}" => $loc->getZ(),
            "{yaw}" => ($loc->getYaw() !== null ? $loc->getYaw() : "---"),
            "{pitch}" => ($loc->getPitch() !== null ? $loc->getPitch() : "---"),
            "{permission}" => ($warp->getPermission() !== null ? $warp->getPermission() : "---"),
            "{icon}" => ($warp->getIconPath() !== null ? $warp->getIconPath() : "---")
        ]));
        $form->addButton(MsgMgr::getMsg("form-editWarp-menu-button-editName"), -1, "", "editName");
        $form->addButton(MsgMgr::getMsg("form-editWarp-menu-button-editPosition"), -1, "", "editPos");
        if ($warp->getPermission() !== null)
            $form->addButton(MsgMgr::getMsg("form-editWarp-menu-button-rmPermission", ["{permission}" => $warp->getPermission()]), -1, "", "rmPerm");
        else
            $form->addButton(MsgMgr::getMsg("form-editWarp-menu-button-addPermission"), -1, "", "addPerm");
        if ($warp->getIconPath() !== null) {
            $form->addButton(MsgMgr::getMsg("form-editWarp-menu-button-editIcon", ["{icon}" => $warp->getIconPath()]), -1, "", "editIcon");
            $form->addButton(MsgMgr::getMsg("form-editWarp-menu-button-rmIcon", ["{icon}" => $warp->getIconPath()]), -1, "", "rmIcon");
        } else {
            $form->addButton(MsgMgr::getMsg("form-editWarp-menu-button-addIcon"), -1, "", "addIcon");
        }
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @param Warp $warp
     * @return CustomForm
     */
    public function openEditWarpName(Player $player, Warp $warp): CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) use ($warp) {
            if ($data === null) return;

            $pl = theSpawn::getInstance();
            $warpName = $warp->getName();
            $warpPos = $warp->getLocation();
            $warpIcon = $warp->getIconPath();
            $warpPerm = $warp->getPermission();

            $editWarp = function (string $newWarpName, Position|Location $warpPos, bool $warpPerm, string|null $warpIcon) use ($pl, $warpName) {
                WarpManager::removeWarp($warpName);
                WarpManager::createWarp(new Warp($warpPos, $warpName, $warpPerm, $warpIcon));
            };

            if (isset($data["warpName"]) && mb_strlen($data["warpName"]) >= 1) {
                if (!$this->canEditWarp($warp)) return;

                $editWarp($data["warpName"], $warpPos, ($warpPerm !== null), $warpIcon);
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, WarpManager::getWarp($data["warpName"]));
                return;
            }
        });
        $form->setTitle(MsgMgr::getMsg("form-editWarp-editName-title"));
        $form->addLabel(MsgMgr::getMsg("form-editWarp-editName-content", ["{warp}" => $warp->getName()]));
        $form->addInput(MsgMgr::getMsg("form-editWarp-editName-inputNameDescription"), "", $warp->getName(), "warpName");
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @param Warp $warp
     * @param bool $edit
     * @return CustomForm
     */
    public function openEditWarpIcon(Player $player, Warp $warp, bool $edit = true): CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) use ($warp) {
            if ($data === null) return;

            $pl = theSpawn::getInstance();
            $warpName = $warp->getName();

            if (isset($data["warpIcon"]) && mb_strlen($data["warpIcon"]) >= 1) {
                if (!$this->canEditWarp($warp)) return;

                $warp->setIconPath($data["warpIcon"]);
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, WarpManager::getWarp($warpName));
                return;
            }
        });
        $form->setTitle(MsgMgr::getMsg("form-editWarp-editIcon-title"));
        $form->addLabel(MsgMgr::getMsg("form-editWarp-editIcon-content", ["{warp}" => $warp->getName()]));
        if ($edit)
            $form->addInput(MsgMgr::getMsg("form-editWarp-editIcon-inputNameDescription"), "", $warp->getIconPath(), "warpIcon");
        else
            $form->addInput(MsgMgr::getMsg("form-editWarp-editIcon-inputNameDescription"), "", null, "warpIcon");
        $form->sendToPlayer($player);
        return $form;
    }

    private function canEditWarp(Warp $warp): bool
    {
        $ev = new EditWarpEvent($warp);
        $ev->call();

        return !$ev->isCancelled();
    }
}
