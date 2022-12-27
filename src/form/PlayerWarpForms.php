<?php

namespace supercrafter333\theSpawn\form;

use EasyUI\element\Button;
use EasyUI\element\Input;
use EasyUI\element\Label;
use EasyUI\icon\ButtonIcon;
use EasyUI\utils\FormResponse;
use EasyUI\variant\CustomForm;
use EasyUI\variant\SimpleForm;
use pocketmine\player\Player;
use pocketmine\world\sound\XpLevelUpSound;
use supercrafter333\theSpawn\events\playerwarp\PlayerWarpCreateEvent;
use supercrafter333\theSpawn\events\playerwarp\PlayerWarpTeleportEvent;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\pwarp\PlayerWarp;
use supercrafter333\theSpawn\pwarp\PlayerWarpManager;
use supercrafter333\theSpawn\theSpawn;
use function count;
use function mt_rand;
use function sort;

class PlayerWarpForms
{

    public static function menu(Player $player): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-playerWarps-menu-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-playerWarps-menu-content"));

        if (count(PlayerWarpManager::getPlayerWarpsOf($player->getName())) >= 1)
            $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-menu-yourWarpsButton",
                ["{count}" => count(PlayerWarpManager::getPlayerWarpsOf($player->getName()))]), null,
            function (Player $player): void {
                $player->sendForm(self::ownWarpsForm($player));
            }));

        $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-menu-teleportButton"), null,
        function (Player $player): void {
            $player->sendForm(self::teleportWarpList());
        }));

        if (count(PlayerWarpManager::getPlayerWarpsOf($player->getName())) < PlayerWarpManager::getMaxPlayerWarpCount($player))
            $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-menu-createWarpButton"), null,
            function (Player $player): void {
                $player->sendForm(self::createWarp());
            }));

        return $form;
    }

    public static function ownWarpsForm(Player $player): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-playerWarps-ownWarps-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-playerWarps-ownWarps-content",
            ["{count}" => count(PlayerWarpManager::getPlayerWarpsOf($player->getName()))]));

        foreach (PlayerWarpManager::getPlayerWarpsOf($player->getName()) as $warp) {

            $warpIcon = $warp->getIconPath() === null ? "" : $warp->getIconPath();
            $iconType = $warpIcon === "" ? null : ButtonIcon::TYPE_PATH;
            if (str_contains($warpIcon, "http")) $iconType = ButtonIcon::TYPE_URL;

            $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-ownWarps-button",
                ["{warp}" => $warp->getName(), "{world}" => $warp->getLocation()->getWorld()->getFolderName()]),
                ($iconType === null ? null : new ButtonIcon($warpIcon, $iconType)),
            function (Player $player) use ($warp): void {
                $player->sendForm(self::editWarp($warp));
            }));
        }

        return $form;
    }

    public static function teleportWarpList(): SimpleForm
    {
        $cfg = PlayerWarpManager::getConfig();
        $all = $cfg->getAll(true);
        sort($all);

        $form = new SimpleForm(MsgMgr::getMsg("form-playerWarps-warpTeleport-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-playerWarps-warpTeleport-content", ["{count}" => count($all)]));
        
        foreach ($all as $warpName) {
            if (($warp = PlayerWarpManager::getPlayerWarp($warpName)) instanceof PlayerWarp) {
                $warpIcon = $warp->getIconPath() === null ? "" : $warp->getIconPath();
                $iconType = $warpIcon === "" ? null : ButtonIcon::TYPE_PATH;
                if (str_contains($warpIcon, "http")) $iconType = ButtonIcon::TYPE_URL;
    
                $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-warpTeleport-button",
                    ["{warp}" => $warp->getName(), "{world}" => $warp->getLocation()->getWorld()->getFolderName(),
                        "{owner}" => $warp->getOwnerName()]),
                    ($iconType === null ? null : new ButtonIcon($warpIcon, $iconType)),
                function (Player $player) use ($warp): void {

                    $loc = $warp->getLocation();
                    $posMsg = $loc->getFloorX() . ' | ' . $loc->getFloorY() . ' | ' . $loc->getFloorZ();
                    $worldName = $loc->getWorld()->getFolderName();

                    if (!theSpawn::getInstance()->isPositionSafe($loc)) {
                        $player->sendMessage(MsgMgr::getMsg("position-not-safe", null, true));
                        return;
                    }
    
                    $ev = new PlayerWarpTeleportEvent($warp, $player);
                    $ev->call();
                    if ($ev->isCancelled()) return;
    
                    $player->teleport($warp->getLocation());
                    $player->sendMessage(MsgMgr::getMsg("pwarp-teleported", ["{warp}" => $warp->getName(), "{owner}" => $warp->getOwnerName(), "{location}" => $posMsg, "{world}" => $worldName], true));
                }
                ));
            }
        }

        return $form;
    }

    public static function createWarp(): CustomForm
    {
        $form = new CustomForm(MsgMgr::getMsg("form-playerWarps-createWarp-title"));

        $form->addElement("name", new Input("", null, MsgMgr::getMsg("form-playerWarps-createWarp-nameInput")));
        $form->addElement("icon", new Input("", null, MsgMgr::getMsg("form-playerWarps-createWarp-iconInput")));

        $form->setSubmitListener(
            function (Player $player, FormResponse $response): void {
                $warpName = $response->getInputSubmittedText("name");
                $icon = $response->getInputSubmittedText("icon");
                
                if ($warpName === "") return;
                
                if (PlayerWarpManager::exists($warpName)) {
                    $player->sendMessage(MsgMgr::getMsg("pwarp-already-exists", ["{warp}" => $warpName], true));
                    return;
                }

                if (($max = PlayerWarpManager::getMaxPlayerWarpCount($player)) >= PlayerWarpManager::getPlayerWarpsOf($player->getName())) {
                    $player->sendMessage(MsgMgr::getMsg("pwarp-maximum-reached", ["{max}" => $max], true));
                    return;
                }

                $warp = new PlayerWarp($player->getLocation(), $warpName, $player->getName(), ($icon === "" ? null : $icon));

                $ev = new PlayerWarpCreateEvent($warp);
                $ev->call();
                if ($ev->isCancelled()) return;

                PlayerWarpManager::createPlayerWarp($warp);
                $player->sendMessage(MsgMgr::getMsg("pwarp-created", ["{warp}" => $warpName], true));
            }
        );

        return $form;
    }

    public static function editWarp(PlayerWarp $warp): SimpleForm
    {
        $loc = $warp->getLocation();
        $form = new SimpleForm(MsgMgr::getMsg("form-playerWarps-editWarp-title", ["{warp}" => $warp->getName()]));
        $form->setHeaderText(MsgMgr::getMsg("form-playerWarps-editWarp-content", [
            "{warp}" => $warp->getName(), "{owner}" => $warp->getOwnerName(), "{world}" => $warp->getLocation()->getWorld()->getFolderName(),
            "{icon}" => ($warp->getIconPath() === null ? "---" : $warp->getIconPath()),
            "{X}" => $loc->getFloorX(), "{Y}" => $loc->getFloorY(), "{Z}" => $loc->getFloorZ(),
            "{yaw}" => ($loc->getYaw() !== null ? $loc->getYaw() : "---"),
            "{pitch}" => ($loc->getPitch() !== null ? $loc->getPitch() : "---"),]));

        $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-editWarp-setPositionButton"), null,
        function (Player $player) use ($warp): void {
            $warp->setLocation($player->getLocation());
            $warp->save();

            $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
            $player->sendForm(self::editWarp($warp));
        }));
        if ($warp->getIconPath() === null)
            $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-editWarp-addIconButton"), null,
            function (Player $player) use ($warp): void {
                $player->sendForm(self::editOrAddIcon($warp));
            }));
        else {
            $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-editWarp-editIconButton"), null,
                function (Player $player) use ($warp): void {
                    $player->sendForm(self::editOrAddIcon($warp));
                }));
            $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-editWarp-removeIconButton"), null,
                function (Player $player) use ($warp): void {
                $warp->setIconPath(null);
                $warp->save();

                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $player->sendForm(self::editWarp($warp));
                }));
        }
        $form->addButton(new Button(MsgMgr::getMsg("form-playerWarps-editWarp-deleteButton"), null,
        function (Player $player) use ($warp): void {
            $name = $warp->getName();
            PlayerWarpManager::removePlayerWarp($name);

            $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
            $player->sendMessage(MsgMgr::getMsg("form-playerWarps-editWarp-deletedMsg", ["{warp}" => $name], true));
        }));

        return $form;
    }

    public static function editOrAddIcon(PlayerWarp $warp): CustomForm
    {
        $form = new CustomForm(MsgMgr::getMsg("form-playerWarps-editOrAddWarpIcon-title"));
        $hasIcon = $warp->getIconPath() !== null;

        if ($hasIcon) {
            $form->addElement("label", new Label(MsgMgr::getMsg("form-playerWarps-editWarpIcon-content")));
            $form->addElement("icon", new Input(null, $warp->getIconPath(), MsgMgr::getMsg("form-playerWarps-editWarpIcon-iconInput")));
        } else {
            $form->addElement("label", new Label(MsgMgr::getMsg("form-playerWarps-addWarpIcon-content")));
            $form->addElement("icon", new Input(null, null, MsgMgr::getMsg("form-playerWarps-addWarpIcon-iconInput")));
        }

        $form->setSubmitListener(
            function (Player $player, FormResponse $response) use ($warp, $hasIcon): void {
                $icon = $response->getInputSubmittedText("icon");

                if ($icon === "") return;

                $warp->setIconPath($icon);
                $warp->save();

                if ($hasIcon)
                    $player->sendMessage(MsgMgr::getMsg("form-playerWarps-editWarpIcon-iconEdited", ["{warp}" => $warp->getName(), "{icon}" => $warp->getIconPath()], true));
                else
                    $player->sendMessage(MsgMgr::getMsg("form-playerWarps-addWarpIcon-iconAdded", ["{warp}" => $warp->getName(), "{icon}" => $warp->getIconPath()], true));
            }
        );

        return $form;
    }
}