<?php
class Env
{
    public const PROD = "PROD";
    public const DEV = "DEV";
    public const TEST = "TEST";

    const OS_LINUX = "LINUX";
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

    public const LOCAL_HOST = "127.0.0.1";

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
        $schema = Config::get("schema");
        $host = self::getIp();
        $port = Config::get('port');
        return $schema.'://'.$host.':'.$port.'/';
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
        if (!empty($ipAddress)) {
            return $ipAddress;
        }
        if (self::isUnix()) {
            $interface = 'eth0'; // 网卡名称
            $ifconfigInfo = shell_exec('/sbin/ifconfig ' . $interface);
            if (preg_match('/inet\s+([0-9\.]+)/', $ifconfigInfo, $matches)) {
                $ipAddress = $matches[1];
            }
        } else if (self::isMac()) {
            return self::LOCAL_HOST;
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
                    return self::OS_LINUX;
                case "Darwin":
                    return self::OS_MAC;
                case "Unknown":
                default:
                    return "";

            }
        } else if (defined("PHP_OS")) {
            switch (PHP_OS) {
                case "Linux":
                    return self::OS_LINUX;
                case "Darwin":
                    return self::OS_MAC;
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
        return self::isLinux() || self::isMac();
    }

    public static function isLinux() {
        return self::OS_LINUX === self::getSimlpeOs();
    }

    public static function isMac() {
        return self::OS_MAC === self::getSimlpeOs();
    }

    /**
     * 获取系统根目录
     * @return string
     */
    public static function getRootPath() {
        switch (self::getSimlpeOs()) {
            case self::OS_LINUX:
            case self::OS_MAC:
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
        return PROJECT_PATH.DIRECTORY_SEPARATOR."config";
    }

    public static function isConsole() {
        DBC::assertNonNull(self::$RUN_MODE, "[ENV] Unset RUN MODE!", 0, GearShutDownException::class);
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
            self::OS_LINUX => "\n",
            self::OS_MAC => "\r"
        ];

        return $hash[$os]??"";
    }

    public static function getHome() {
        $home =  self::isWin() ? getenv("HOMEDRIVE").getenv("HOMEPATH") : getenv("HOME");
        DBC::assertNotEmpty($home, "[Env] Failed to obtain the home path.", 0, GearShutDownException::class);
        return $home;
    }

    /**
     * Check the path is valid or not
     * @param string $path
     * @param bool $absoluteOnly
     * @return bool
     */
    public static function checkPath($path, $absoluteOnly = true) {
        $firstChar = substr($path, 0, 1);
        if ($absoluteOnly) {
            if (self::isUnix()) {
                return "/" === $firstChar || "~" === $firstChar;
            } else if (self::isWin()) {
                return preg_match("/^[a-zA-Z]:/", $path);
            }
            return false;
        }
    }

    /**
     * Support Unix-style paths starting with “~”
     * @param string $path
     * @return string
     */
    public static function rewritePathForUnix($path) {
        if (Env::isUnix() && 0 === strpos($path, "~/")) {
            $home = Env::getHome();
            return str_replace("~/", $home."/", $path);
        }
        return $path;
    }

}
