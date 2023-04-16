<?php

namespace supercrafter333\theSpawn\form;

use EasyUI\element\Button;
use EasyUI\element\Input;
use EasyUI\element\Label;
use EasyUI\element\Toggle;
use EasyUI\icon\ButtonIcon;
use EasyUI\utils\FormResponse;
use EasyUI\variant\CustomForm;
use EasyUI\variant\SimpleForm;
use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\XpLevelUpSound;
use supercrafter333\theSpawn\commands\warp\{DelwarpCommand, SetwarpCommand, WarpCommand};
use supercrafter333\theSpawn\events\other\EditWarpEvent;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\warp\Warp;
use supercrafter333\theSpawn\warp\WarpManager;
use function mb_strlen;
use function sort;
use function str_contains;

class WarpForms
{

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function open(Player $player): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-warp-menu-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-warp-menu-content"));

        $warps = WarpManager::getWarpConfig()->getAll(true);
        sort($warps);
        foreach ($warps as $warp) {
            $warp = WarpManager::getWarp($warp);
            $warpName = $warp->getName();
            $warpIcon = $warp->getIconPath() === null ? "" : $warp->getIconPath();
            $iconType = $warpIcon === "" ? null : ButtonIcon::TYPE_PATH;
            if (str_contains($warpIcon, "http")) $iconType = ButtonIcon::TYPE_URL;
            $form->addButton(new Button(str_replace(["{warp}", "{line}", "{player_count}"], [$warpName, "\n", count($warp->getLocation()->getWorld()->getPlayers())], MsgMgr::getMsg("form-warp-menu-warpButton")),
                ($iconType !== null ? new ButtonIcon($warpIcon, $iconType) : null),
                function (Player $player) use ($warp): void {
                WarpCommand::simpleExecute($player, [$warp->getName()]);
            }));
        }

        return $form;
    }

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function openRmWarp(Player $player): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-rmWarp-menu-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-rmWarp-menu-content"));
        foreach (WarpManager::getWarpConfig()->getAll(true) as $warp) {
            $warpInfo = WarpManager::getWarp($warp);
            $warpName = $warpInfo->getName();
            $warpIcon = $warpInfo->getIconPath() === null ? "" : $warpInfo->getIconPath();
            $iconType = $warpIcon === "" ? -1 : 0;
            if (str_contains($warpIcon, "http")) $iconType = 1;
            $form->addButton(new Button(str_replace(["{warp}", "{line}", "{player_count}"], [$warpName, "\n", count($warpInfo->getLocation()->getWorld()->getPlayers())], MsgMgr::getMsg("form-rmWarp-menu-warpButton")),
                ($iconType !== null ? new ButtonIcon($warpIcon, $iconType) : null),
                function (Player $player) use ($warpInfo): void {
                DelwarpCommand::simpleExecute($player, [$warpInfo->getName()]);
            }));
        }

        return $form;
    }

    /**
     * @param Player $player
     * @return CustomForm
     */
    public function openSetWarp(Player $player): CustomForm
    {
        $form = new CustomForm(MsgMgr::getMsg("form-setWarp-menu-title"),
            function (Player $player, FormResponse $response) {
            $name = $response->getInputSubmittedText("warpName");
            $perm = $response->getToggleSubmittedChoice("warpPermission");
            $icon = $response->getInputSubmittedText("warpIconPath");

            if ($name !== null && $name !== "") {
                if (($perm !== null && $perm) && ($icon !== null && $icon !== "")) {
                    SetwarpCommand::simpleExecute($player, [$name, 'true', $icon]);
                    return;
                }
                if ($perm !== null && $perm) {
                    SetwarpCommand::simpleExecute($player, [$name, 'true']);
                    return;
                }
                if ($icon !== null && $icon !== "") {
                    SetwarpCommand::simpleExecute($player, [$name, 'false', $icon]);
                    return;
                }

                SetwarpCommand::simpleExecute($player, [$name]);
            }
        });
        $form->addElement("label", new Label(MsgMgr::getMsg("form-setWarp-menu-content")));
        $form->addElement("warpName", new Input(MsgMgr::getMsg("form-setWarp-menu-inputNameDescription")));
        $form->addElement("warpPermission", new Toggle(MsgMgr::getMsg("form-setWarp-menu-togglePermDescription")));
        $form->addElement("warpIconPath", new Input(MsgMgr::getMsg("form-setWarp-menu-inputIconPathDescription"), "textures/ui/world_glyph_color_2x_black_outline"));

        return $form;
    }


    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function openChooseEditWarp(Player $player): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-chooseEditWarp-menu-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-chooseEditWarp-menu-content"));
        foreach (WarpManager::getWarpConfig()->getAll(true) as $warp) {
            $warpInfo = WarpManager::getWarp($warp);
            if ($warpInfo !== null) {
                $warpName = $warpInfo->getName();
                $warpIcon = $warpInfo->getIconPath() === null ? "" : $warpInfo->getIconPath();
                $iconType = $warpIcon === "" ? -1 : 0;
                if (str_contains($warpIcon, "http")) $iconType = 1;
                $form->addButton(new Button(str_replace(["{warp}", "{line}", "{player_count}"], [$warpName, "\n", count($warpInfo->getLocation()->getWorld()->getPlayers())], MsgMgr::getMsg("form-chooseEditWarp-menu-warpButton")),
                    ($iconType !== null ? new ButtonIcon($warpIcon, $iconType) : null),
                    function (Player $player) use ($warpInfo): void {
                    $player->sendForm($this->openEditWarp($player, $warpInfo));
                }));
            }
        }

        return $form;
    }

    /**
     * @param Player $player
     * @param Warp $warp
     * @return SimpleForm
     */
    public function openEditWarp(Player $player, Warp $warp): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-editWarp-menu-title"));
        $loc = $warp->getLocation();
        $form->setHeaderText(MsgMgr::getMsg("form-editWarp-menu-content", [
            "{warp}" => $warp->getName(),
            "{world}" => $loc->getWorld()->getFolderName(),
            "{X}" => $loc->getFloorX(),
            "{Y}" => $loc->getFloorY(),
            "{Z}" => $loc->getFloorZ(),
            "{yaw}" => ($loc->getYaw() !== null ? $loc->getYaw() : "---"),
            "{pitch}" => ($loc->getPitch() !== null ? $loc->getPitch() : "---"),
            "{permission}" => ($warp->getPermission() !== null ? $warp->getPermission() : "---"),
            "{icon}" => ($warp->getIconPath() !== null ? $warp->getIconPath() : "---")
        ]));
        $form->addButton(new Button(MsgMgr::getMsg("form-editWarp-menu-button-editName"), null,
            function (Player $player) use ($warp): void {
                $player->sendForm($this->openEditWarpName($player, $warp));
            }));
        $form->addButton(new Button(MsgMgr::getMsg("form-editWarp-menu-button-editPosition"), null,
            function (Player $player) use ($warp): void {
            if (!$this->canEditWarp($warp)) return;

                $warp->setLocation($player->getLocation());
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $player->sendForm($this->openEditWarp($player, WarpManager::getWarp($warp->getName())));
            }));
        if ($warp->getPermission() !== null)
            $form->addButton(new Button(MsgMgr::getMsg("form-editWarp-menu-button-rmPermission", ["{permission}" => $warp->getPermission()]), null,
                function (Player $player) use ($warp): void {
                if (!$this->canEditWarp($warp)) return;

                $warp->setPermissionEnabled(false);
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $player->sendForm($this->openEditWarp($player, WarpManager::getWarp($warp->getName())));
                }));
        else
            $form->addButton(new Button(MsgMgr::getMsg("form-editWarp-menu-button-addPermission"), null,
        function (Player $player) use ($warp): void {
                if (!$this->canEditWarp($warp)) return;

                $warp->setPermissionEnabled(true);
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $player->sendForm($this->openEditWarp($player, WarpManager::getWarp($warp->getName())));
        }));
        if ($warp->getIconPath() !== null) {
            $form->addButton(new Button(MsgMgr::getMsg("form-editWarp-menu-button-editIcon", ["{icon}" => $warp->getIconPath()]), null,
                function (Player $player) use ($warp): void {
                $player->sendForm($this->openEditWarpIcon($player, $warp));
                }));
            $form->addButton(new Button(MsgMgr::getMsg("form-editWarp-menu-button-rmIcon", ["{icon}" => $warp->getIconPath()]), null,
                function (Player $player) use ($warp): void {
                if (!$this->canEditWarp($warp)) return;

                $warp->setIconPath(null);
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $player->sendForm($this->openEditWarp($player, WarpManager::getWarp($warp->getName())));
                }));
        } else {
            $form->addButton(new Button(MsgMgr::getMsg("form-editWarp-menu-button-addIcon"), null,
                function (Player $player) use ($warp): void {
                $player->sendForm($this->openEditWarpIcon($player, $warp, false));
                }));
        }

        return $form;
    }

    /**
     * @param Player $player
     * @param Warp $warp
     * @return CustomForm
     */
    public function openEditWarpName(Player $player, Warp $warp): CustomForm
    {
        $form = new CustomForm(MsgMgr::getMsg("form-editWarp-editName-title"),
            function (Player $player, FormResponse $response) use ($warp) {

            $pl = theSpawn::getInstance();
            $warpName = $warp->getName();
            $warpPos = $warp->getLocation();
            $warpIcon = $warp->getIconPath();
            $warpPerm = $warp->getPermission();
            $newName = $response->getInputSubmittedText("name");

            $editWarp = function (string $newWarpName, Position|Location $warpPos, bool $warpPerm, string|null $warpIcon) use ($pl, $warpName) {
                WarpManager::removeWarp($warpName);
                WarpManager::createWarp(new Warp($warpPos, $warpName, $warpPerm, $warpIcon));
            };

            if ($newName !== null && mb_strlen($newName) >= 1) {
                if (!$this->canEditWarp($warp)) return;

                $editWarp($newName, $warpPos, ($warpPerm !== null), $warpIcon);
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $player->sendForm($this->openEditWarp($player, WarpManager::getWarp($newName)));
            }
        });
        $form->addElement("label", new Label(MsgMgr::getMsg("form-editWarp-editName-content", ["{warp}" => $warp->getName()])));
        $form->addElement("name", new Input(MsgMgr::getMsg("form-editWarp-editName-inputNameDescription"), $warp->getName()));

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
        $form = new CustomForm(MsgMgr::getMsg("form-editWarp-editIcon-title"),
            function (Player $player, FormResponse $response) use ($warp) {
            $icon = $response->getInputSubmittedText("icon");
            $warpName = $warp->getName();

            if ($icon && mb_strlen($icon) >= 1) {
                if (!$this->canEditWarp($warp)) return;

                $warp->setIconPath($icon);
                $warp->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $player->sendForm($this->openEditWarp($player, WarpManager::getWarp($warpName)));
            }
        });
        $form->addElement("label", new Label(MsgMgr::getMsg("form-editWarp-editIcon-content", ["{warp}" => $warp->getName()])));
        if ($edit)
            $form->addElement("icon", new Input(MsgMgr::getMsg("form-editWarp-editIcon-inputNameDescription"), $warp->getIconPath()));
        else
            $form->addElement("icon", new Input(MsgMgr::getMsg("form-editWarp-editIcon-inputNameDescription")));

        return $form;
    }

    private function canEditWarp(Warp $warp): bool
    {
        $ev = new EditWarpEvent($warp);
        $ev->call();

        return !$ev->isCancelled();
    }
}
