<?php

namespace supercrafter333\theSpawn;

use pocketmine\utils\Config;
use function array_keys;
use function str_replace;
use function strtolower;

/**
 * Class MsgMgr
 * @package supercrafter333\theSpawn
 */
class MsgMgr
{

    public const LANG_en_BE = "en_BE"; # British English
    //TODO: public const LANG_en_AE = "en_AE"; # American English
    public const LANG_ger_DE = "ger_DE";
    public const LANG_es_ES = "es_ES";
    public const LANG_fr_FR = "fr_FR";
    public const LANG_CUSTOM = "messages";

    public static array $languages = [
        self::LANG_en_BE => self::LANG_en_BE,
        //TODO: self::LANG_en_AE => self::LANG_en_AE,
        self::LANG_ger_DE => self::LANG_ger_DE,
        self::LANG_es_ES => self::LANG_es_ES,
        self::LANG_fr_FR => self::LANG_fr_FR,
        self::LANG_CUSTOM => self::LANG_CUSTOM,
    ];

    /**
     * @var MsgMgr
     */
    private static MsgMgr $Me;

    /**
     * MsgMgr constructor.
     */
    public function __construct()
    {
        self::$Me = $this;
    }

    /**
     * @return Config
     */
    public static function getMsgs(): Config
    {
        if (strtolower(self::getMessagesLanguage()) == "custom") {
            theSpawn::getInstance()->saveResource("Languages/messages.yml");
            return new Config(theSpawn::getInstance()->getDataFolder() . "Languages/messages.yml", Config::YAML);
        }
        if (isset(self::$languages[self::getMessagesLanguage()]) && file_exists(theSpawn::getInstance()->getFile2() . "resources/Languages/" . self::getMessagesLanguage() . ".yml")) return new Config(theSpawn::getInstance()->getFile2() . "resources/Languages/" . self::getMessagesLanguage() . ".yml", Config::YAML);
        return self::getDefaultMsgs();
    }

    public static function getDefaultMsgs(): Config
    {
        return new Config(theSpawn::getInstance()->getFile2() . "resources/Languages/" . self::LANG_en_BE . ".yml", Config::YAML);
    }

    /**
     * @param string $version
     * @return bool
     */
    public function checkMsgCfgVersionX(string $version): bool
    {
        if ($this->getMsgsX()->exists("version")) {
            if ($this->getMsgsX()->get("version") == $version) {
                return true;
            }
        }
        return false;
    }

    private static function getLowerLang(): string
    {
        return strtolower(theSpawn::getInstance()->getConfig()->get("language"));
    }

    public static function getMessagesLanguage(): string
    {
        if (self::getLowerLang() == "custom") return theSpawn::getInstance()->getConfig()->get("language");
        if (isset(self::$languages[theSpawn::getInstance()->getConfig()->get("language")])) return theSpawn::getInstance()->getConfig()->get("language");
        return self::LANG_en_BE;
    }

    /**
     * @param string $version
     * @return bool
     */
    public static function checkMsgCfgVersion(string $version): bool
    {
        if (self::getMsgs()->exists("version")) {
            if (self::getMsgs()->get("version") == $version) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public static function updateMsgCfg()
    {
        rename(theSpawn::getInstance()->getDataFolder() . "Languages/" . self::getMessagesLanguage() . ".yml", theSpawn::getInstance()->getDataFolder() . "Languages/" . self::getMessagesLanguage() . "Old.yml");
        return theSpawn::getInstance()->saveResource("Languages/" . self::getMessagesLanguage() . ".yml");
    }

    /**
     * @param string $message
     * @param array|null $replace
     * @param bool $prefix
     * @return string
     */
    public static function getMsg(string $message, array|null $replace = null, bool $prefix = false): string
    {
        $prefixStr = $prefix ? theSpawn::$prefix : "";
        if (self::getMsgs()->exists($message)) {
            if ($replace === null) return self::getMsgs()->get($message);
            $replaced = self::getMsgs()->get($message);
            foreach (array_keys($replace) as $i) {
                $replaced = str_replace($i, $replace[$i], $replaced);
            }
            return $prefixStr . str_replace("{line}", "\n", $replaced);
        } elseif (self::getDefaultMsgs()->exists($message)) {
            if ($replace === null) return self::getDefaultMsgs()->get($message);
            $replaced = self::getDefaultMsgs()->get($message);
            foreach (array_keys($replace) as $i) {
                $replaced = $prefixStr . str_replace($i, $replace[$i], $replaced);
            }
            return $prefixStr . str_replace("{line}", "\n", $replaced);
        }
        return "ERROR";
    }

    /**
     * @return string
     */
    public static function getNoPermMsg(): string
    {
        return self::getMsg("no-perms");
    }

    /**
     * @return string
     */
    public static function getOnlyIGMsg(): string
    {
        return self::getMsg("only-In-Game");
    }

    /**
     * @return string
     */
    public static function getErrorMsg(): string
    {
        return self::getMsg("something-went-wrong");
    }

    /**
     * @return string
     */
    public static function getPrefix(): string
    {
        return self::getMsg("prefix");
    }

    /**
     * @return static
     */
    public static function getMe()
    {
        return self::$Me;
    }

    /**
     * @return Config
     */
    public function getMsgsX(): Config
    {
        return self::getMsgs();
    }

    /**
     * @return string
     */
    public function getErrorMgsX(): string
    {
        return self::getMsg("something-went-wrong");
    }

    /**
     * @param string $message
     * @return bool|mixed
     */
    public function getMsgX(string $message)
    {
        return self::getMsg($message);
    }
}
