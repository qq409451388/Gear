<?php
class Env
{
    public const PROD = "PROD";
    public const DEV = "DEV";
    public const TEST = "TEST";

    const OS_UNIX = "UNIX";
    const OS_WINDOWS = "WINDOWS";
    const OS_MAC = "MACOS";

    /**
     * 一次性脚本
     */
    public const RUN_MODE_SCRIPT = "SCRIPT";
    /**
     * 常驻程序
     */
    public const RUN_MODE_CONSOLE = "CONSOLE";
    /**
     * @var null|string 运行模式
     */
    private static $RUN_MODE = null;

    public static function isDev(){
        return self::getEnv() == self::DEV;
    }

    public static function isTest(){
        return self::getEnv() == self::TEST;
    }

    public static function isProd(){
        return self::getEnv() == self::PROD;
    }

    public static function getDomain(){
        $host = self::getIp();
        $port = Config::get('port');
        return 'http://'.$host.':'.$port.'/';
    }

    public static function getIp(){
        $ipAddress = Config::get("ip");
        if (!empty($ipAddress) && "0.0.0.0" != $ipAddress) {
            return $ipAddress;
        }
        return self::getOuterIp() ? self::getOuterIp() : $ipAddress;
    }

    public static function getOuterIp() {
        static $ipAddress = "";
        if (self::isUnix()) {
            $interface = 'eth0'; // 网卡名称
            $ifconfigInfo = shell_exec('/sbin/ifconfig ' . $interface);
            if (preg_match('/inet\s+([0-9\.]+)/', $ifconfigInfo, $matches)) {
                $ipAddress = $matches[1];
            }
        } else if (self::isWin()) {
            exec("ipconfig", $output);
            foreach ($output as $line) {
                if (preg_match('/IPv4 Address.*: ([0-9\.]+)/', $line, $matches)) {
                    $ipAddress = $matches[1];
                }
            }
        }
        return $ipAddress;
    }

    public static function get(){
        return self::getEnv();
    }

    private static function getEnv(){
        return @defined("ENV") ? strtoupper(ENV) : null;
    }

    public static function staticPath(){
        #DBC::assertTrue(@defined("STATIC_PATH"), "静态文件路径未设置");
        return defined("STATIC_PATH") ? STATIC_PATH : "";
    }

    /**
     * 获取系统家族名称
     * @return string
     */
    public static function getSimlpeOs() {
        if (defined("PHP_OS_FAMILY")) {
            switch (PHP_OS_FAMILY) {
                case "Windows":
                    return self::OS_WINDOWS;
                case "BSD":
                case "Linux":
                case "Solaris":
                case "Darwin":
                    return self::OS_UNIX;
                case "Unknown":
                default:
                    return "";

            }
        } else if (defined("PHP_OS")) {
            switch (PHP_OS) {
                case "Linux":
                case "Darwin":
                    return self::OS_UNIX;
                case "WINNT":
                case "WIN32":
                case "Windows":
                    return self::OS_WINDOWS;
                default:
                    return "";
            }
        } else {
            return "";
        }
    }

    public static function isWin() {
        return self::OS_WINDOWS === self::getSimlpeOs();
    }

    public static function isUnix() {
        return self::OS_UNIX === self::getSimlpeOs();
    }

    /**
     * 获取系统根目录
     * @return string
     */
    public static function getRootPath() {
        switch (self::getSimlpeOs()) {
            case self::OS_UNIX:
                return "/";
            case self::OS_WINDOWS:
                return "C:\\";
            default:
                return "";
        }
    }

    /**
     * 设置内存大小限制
     * @param int $byte
     * @return void
     */
    public static function setMemoryLimit(int $byte) {
        ini_set('memory_limit', $byte);
    }

    /**
     * 是否使用带有匹配规则的路由
     * @return true
     */
    public static function useFuzzyRouter() {
        return Config::get("fuzzyrouter")??true;
    }

    /**
     * 默认的配置包位置
     * @return string
     */
    public static function getDefaultConfigPath() {
        return GEAR_PATH.DIRECTORY_SEPARATOR."config";
    }

    public static function isConsole() {
        DBC::assertNonNull(self::$RUN_MODE, "[ENV] Unset RUN MODE!");
        return self::$RUN_MODE == self::RUN_MODE_CONSOLE;
    }

    public static function isScript() {
        DBC::assertNonNull(self::$RUN_MODE, "[ENV] Unset RUN MODE!", 0, GearShutDownException::class);
        return self::$RUN_MODE == self::RUN_MODE_SCRIPT;
    }

    public static function setRunModeScript() {
        self::$RUN_MODE = self::RUN_MODE_SCRIPT;
    }

    public static function setRunModeConsole() {
        self::$RUN_MODE = self::RUN_MODE_CONSOLE;
    }

    public static function eol($os) {
        $hash = [
            self::OS_WINDOWS => "\r\n",
            self::OS_UNIX => "\n",
            self::OS_MAC => "\r"
        ];

        return $hash[$os]??"";
    }
}
