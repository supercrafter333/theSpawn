<?php

namespace supercrafter333\theSpawn\Forms;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use supercrafter333\theSpawn\Commands\DelwarpCommand;
use supercrafter333\theSpawn\Commands\SetwarpCommand;
use supercrafter333\theSpawn\Commands\WarpCommand;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
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
            $warpIcon = $warpInfo->getIconPath();
            $iconType = $warpIcon === null ? -1 : 0;
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
            $warpIcon = $warpInfo->getIconPath();
            $iconType = $warpIcon === null ? -1 : 0;
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
        $form->addInput(MsgMgr::getMsg("form-setWarp-menu-inputIconPathDescription"), "", "textures/ui/world_glyph_colour_2x_black_outline", "warpIconPath"); //ui/world_glyph_colour_2x_black_outline
        $form->sendToPlayer($player);
        return $form;
    }
}