<?php

namespace supercrafter333\theSpawn;

use pocketmine\utils\Config;

class MsgMgr
{

    private static $Me;

    public function __construct()
    {
        self::$Me = $this;
    }

    public static function getMsgs(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
    }

    public static function getMsg(string $message): string
    {
        if (self::getMsgs()->exists($message)) {
            return self::getMsgs()->get($message);
        } else {
            return "ERROR";
        }
    }

    public static function getNoPermMsg(): string
    {
        return self::getMsgs()->get("no-perms");
    }

    public static function getOnlyIGMsg(): string
    {
        return self::getMsgs()->get("only-In-Game");
    }

    public static function getMe(): self
    {
        return self::$Me;
    }

    public function getMsgsX(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
    }

    public function getMsgX(string $message)
    {
        return self::getMsgs()->get($message);
    }
}