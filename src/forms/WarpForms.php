<?php

namespace supercrafter333\theSpawn\forms;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\XpLevelUpSound;
use supercrafter333\theSpawn\commands\DelwarpCommand;
use supercrafter333\theSpawn\commands\SetwarpCommand;
use supercrafter333\theSpawn\commands\WarpCommand;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\warp\WarpInfo;
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
    public function open(Player $player): ?SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) return;

            WarpCommand::simpleExecute($player, [$result]);
        });
        $form->setTitle(MsgMgr::getMsg("form-warp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-warp-menu-content"));
        foreach (theSpawn::getInstance()->getWarpCfg()->getAll(true) as $warp) {
            $warpInfo = theSpawn::getInstance()->getWarpInfo($warp);
            $warpName = $warpInfo->getName();
            $warpIcon = $warpInfo->getIconPath() === null ? "" : $warpInfo->getIconPath();
            $iconType = $warpIcon === "" ? -1 : 0;
            if (str_contains($warpIcon, "http")) $iconType = 1;
            $form->addButton(str_replace(["{warp}", "{line}"], [$warpName, "\n"], MsgMgr::getMsg("form-warp-menu-warpButton")), $iconType, $warpIcon, $warpName);
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
        foreach (theSpawn::getInstance()->getWarpCfg()->getAll(true) as $warp) {
            $warpInfo = theSpawn::getInstance()->getWarpInfo($warp);
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

            if (($warp = theSpawn::getInstance()->getWarpInfo($result)) === null) return;

            $this->openEditWarp($player, $warp);
        });
        $form->setTitle(MsgMgr::getMsg("form-chooseEditWarp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-chooseEditWarp-menu-content"));
        foreach (theSpawn::getInstance()->getWarpCfg()->getAll(true) as $warp) {
            $warpInfo = theSpawn::getInstance()->getWarpInfo($warp);
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
     * @param WarpInfo $warp
     * @return SimpleForm
     */
    public function openEditWarp(Player $player, WarpInfo $warp): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) use ($warp) {
            $result = $data;
            if ($result === null) return;

            $pl = theSpawn::getInstance();
            $warpName = $warp->getName();
            $warpPos = $pl->getWarpPosition($warpName);
            $warpIcon = $warp->getIconPath();
            $warpPerm = $warp->getPermission();

            $editWarp = function (string $warpName, Position|Location $warpPos, bool $warpPerm, string|null $warpIcon) use ($pl) {
                $pl->removeWarp($warpName);
                $pl->addWarp($warpPos->getX(), $warpPos->getY(), $warpPos->getZ(),
                    $warpPos->getWorld(),
                    $warpName,
                    ($warpPos instanceof Location ? $warpPos->getYaw() : null),
                    ($warpPos instanceof Location ? $warpPos->getPitch() : null),
                    $warpPerm, $warpIcon);
            };

            if ($result == "editName") {
                $this->openEditWarpName($player, $warp);
                return;
            }

            if ($result == "editPos") {
                $editWarp($warpName, $player->getLocation(), ($warpPerm !== null), $warpIcon);
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, $pl->getWarpInfo($warpName));
                return;
            }

            if ($result == "rmPerm") {
                $editWarp($warpName, $warpPos, false, $warpIcon);
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, $pl->getWarpInfo($warpName));
                return;
            }

            if ($result == "addPerm") {
                $editWarp($warpName, $warpPos, true, $warpIcon);
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, $pl->getWarpInfo($warpName));
                return;
            }

            if ($result == "rmIcon") {
                $editWarp($warpName, $warpPos, ($warpPerm !== null), null);
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, $pl->getWarpInfo($warpName));
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
        $form->setTitle(MsgMgr::getMsg("form-editWarp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-editWarp-menu-content", [
            "{warp}" => $warp->getName(),
            "{world}" => $warp->getLevelName(),
            "{X}" => $warp->getX(),
            "{Y}" => $warp->getY(),
            "{Z}" => $warp->getZ(),
            "{yaw}" => ($warp->getYaw() !== null ? $warp->getYaw() : "---"),
            "{pitch}" => ($warp->getPitch() !== null ? $warp->getPitch() : "---"),
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
     * @param WarpInfo $warp
     * @return CustomForm
     */
    public function openEditWarpName(Player $player, WarpInfo $warp): CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) use ($warp) {
            if ($data === null) return;

            $pl = theSpawn::getInstance();
            $warpName = $warp->getName();
            $warpPos = $pl->getWarpPosition($warpName);
            $warpIcon = $warp->getIconPath();
            $warpPerm = $warp->getPermission();

            $editWarp = function (string $newWarpName, Position|Location $warpPos, bool $warpPerm, string|null $warpIcon) use ($pl, $warpName) {
                $pl->removeWarp($warpName);
                $pl->addWarp($warpPos->getX(), $warpPos->getY(), $warpPos->getZ(),
                    $warpPos->getWorld(),
                    $newWarpName,
                    ($warpPos instanceof Location ? $warpPos->getYaw() : null),
                    ($warpPos instanceof Location ? $warpPos->getPitch() : null),
                    $warpPerm, $warpIcon);
            };

            if (isset($data["warpName"]) && mb_strlen($data["warpName"]) >= 1) {
                $editWarp($data["warpName"], $warpPos, ($warpPerm !== null), $warpIcon);
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, $pl->getWarpInfo($data["warpName"]));
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
     * @param WarpInfo $warp
     * @param bool $edit
     * @return CustomForm
     */
    public function openEditWarpIcon(Player $player, WarpInfo $warp, bool $edit = true): CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) use ($warp) {
            if ($data === null) return;

            $pl = theSpawn::getInstance();
            $warpName = $warp->getName();
            $warpPos = $pl->getWarpPosition($warpName);
            $warpIcon = $warp->getIconPath();
            $warpPerm = $warp->getPermission();

            $editWarp = function (string $warpName, Position|Location $warpPos, bool $warpPerm, string|null $warpIcon) use ($pl) {
                $pl->removeWarp($warpName);
                $pl->addWarp($warpPos->getX(), $warpPos->getY(), $warpPos->getZ(),
                    $warpPos->getWorld(),
                    $warpName,
                    ($warpPos instanceof Location ? $warpPos->getYaw() : null),
                    ($warpPos instanceof Location ? $warpPos->getPitch() : null),
                    $warpPerm, $warpIcon);
            };

            if (isset($data["warpIcon"]) && mb_strlen($data["warpIcon"]) >= 1) {
                $editWarp($warpName, $warpPos, ($warpPerm !== null), $data["warpIcon"]);
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditWarp($player, $pl->getWarpInfo($warpName));
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
}
