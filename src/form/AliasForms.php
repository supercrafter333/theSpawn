<?php

namespace supercrafter333\theSpawn\form;

use EasyUI\element\Button;
use EasyUI\element\Dropdown;
use EasyUI\element\Input;
use EasyUI\element\Label;
use EasyUI\element\Option;
use EasyUI\utils\FormResponse;
use EasyUI\variant\CustomForm;
use EasyUI\variant\SimpleForm;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\sound\DoorBumpSound;
use pocketmine\world\sound\GhastShootSound;
use pocketmine\world\World;
use supercrafter333\theSpawn\commands\alias\AliasManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

class AliasForms
{

    public static function menu(): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-aliases-menu-title"),
        MsgMgr::getMsg("form-aliases-menu-content"));

        $form->addButton(new Button(MsgMgr::getMsg("form-aliases-menu-addAliasButton"), null,
        function (Player $player): void {
            $player->sendForm(self::addAlias($player->getWorld()));
        }));

        foreach (AliasManager::getAliasConfig()->getAll(true) as $aliasName)
            if (($aliasWorld = AliasManager::getAliasWorld($aliasName)) instanceof World)
                $form->addButton(new Button(MsgMgr::getMsg("form-aliases-menu-aliasButton", ["{alias}" => $aliasName, "{world}" => $aliasWorld->getFolderName()]), null,
                function (Player $player) use ($aliasName): void {
                    $player->sendForm(self::viewAlias($aliasName));
                }));

        return $form;
    }

    public static function viewAlias(string $aliasName): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-aliases-viewAlias-title", ["{alias}" => $aliasName]));
        $form->setHeaderText(MsgMgr::getMsg("form-aliases-viewAlias-content", [
            "{alias}" => $aliasName,
            "{world}" => (string)AliasManager::getAliasWorld($aliasName)?->getFolderName()
        ]));

        $form->addButton(new Button(MsgMgr::getMsg("form-aliases-viewAlias-deleteButton"), null,
        function (Player $player) use ($aliasName): void {
            if (AliasManager::removeAlias($aliasName)) {
                $player->sendMessage(MsgMgr::getMsg("alias-removed", ["{alias}" => $aliasName], true));
                $player->broadcastSound(new GhastShootSound(), [$player]);
            } else
                $player->sendMessage(MsgMgr::getMsg("alias-not-found", ["{alias}" => $aliasName], true));
        }));

        return $form;
    }

    public static function addAlias(World|null $defaultWorld = null): CustomForm
    {
        $form = new CustomForm(MsgMgr::getMsg("form-aliases-addAlias-title"));
        $worlds = Server::getInstance()->getWorldManager()->getWorlds();

        $form->addElement("label", new Label(MsgMgr::getMsg("form-aliases-addAlias-content")));
        $form->addElement("name", new Input(null, null, MsgMgr::getMsg("form-aliases-addAlias-inputField")));

        $dd = new Dropdown(MsgMgr::getMsg("form-aliases-addAlias-dropdownField"));
        if ($defaultWorld instanceof World) {
            $dd->addOption(new Option($defaultWorld->getFolderName(), $defaultWorld->getDisplayName()));
            $dd->setDefaultIndex(0);
        }

        foreach ($worlds as $world)
            if (!$defaultWorld instanceof World || $world->getFolderName() !== $defaultWorld->getFolderName())
                $dd->addOption(new Option($world->getFolderName(), $world->getDisplayName()));
        $form->addElement("world", $dd);

        $form->setSubmitListener(
            function (Player $player, FormResponse $response): void {
                $name = $response->getInputSubmittedText("name");
                $world = $response->getDropdownSubmittedOptionId("world");
                
                if ($name === null || $name === "" || $world === null || $world === "") return;

                $pl = theSpawn::getInstance();
                if (!$pl->checkWorld($world) instanceof World) {
                    $player->sendMessage(MsgMgr::getMsg("world-not-found", null, true));
                    return;
                }
        
                if (!$pl->getSpawn($pl->checkWorld($world))) {
                    $player->sendMessage(str_replace(["{world}"], [$world], MsgMgr::getMsg("no-spawn-set-for-world", null, true)));
                    return;
                }
                
                AliasManager::setAlias($name, $pl->checkWorld($world));
                $player->sendMessage(MsgMgr::getMsg("alias-set", ["{alias}" => $name, "{world}" => $world], true));
                $player->broadcastSound(new DoorBumpSound(), [$player]);
            }
        );

        return $form;
    }
}