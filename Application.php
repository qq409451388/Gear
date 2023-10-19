<?php

/**
 * 初始化依赖，只调用基础函数
 * @description 使用方式：定义项目路径常量PROJECT_PATH, 在启动文件require once该文件，调用启动方法即可
 */
class Application
{
    const OS_LINUX = "UNIX";
    const OS_WINDOWS = "WINDOWS";
    const OS_MAC = "MACOS";

    private function setPath($k, $v) {
        // rewrite path if $v is only a folder name
        if (self::isWin()) {
            if (false === strpos($v, "/")) {
                $v = PROJECT_PATH.DIRECTORY_SEPARATOR.$v;
            }
        } else {
            if (false === strpos($v, DIRECTORY_SEPARATOR)) {
                $v = PROJECT_PATH.DIRECTORY_SEPARATOR.$v;
            }
        }
        $v = self::rewritePathForUnix($v);
        if (!is_dir($v)) {
            exit("The constants $k <$v> path is not exists!");
        }
        define($k, $v);
    }

    private function envConstants($constants = null) {
        $this->envCheck("PROJECT_PATH");
        if (!empty($constants)) {
            foreach ($constants as $k => $v) {
                $this->setPath($k, $v);
            }
        }
        if (!defined("GEAR_PATH")) {
            echo "[".date("Y-m-d H:i:s")."][WARN]Gear framework path not specified, loading default[project_path/Gear] configuration".PHP_EOL;
            $this->setPath("GEAR_PATH", PROJECT_PATH."/Gear");
            $this->setPath("CORE_PATH", GEAR_PATH."/core");
        }
    }

    /**
     * Support Unix-style paths starting with “~”
     * @param string $path
     * @return string
     */
    public static function rewritePathForUnix($path) {
        if (self::isUnix() && 0 === strpos($path, "~/")) {
            $home = self::getHome();
            return str_replace("~/", $home."/", $path);
        }
        return $path;
    }

    private function envCheck($envKey) {
        if (!defined($envKey)) {
            exit("[error] Please define $envKey for init Gear framework!");
        }
    }

    private function envCheck2($envKey) {
        if (!defined($envKey)) {
            echo "[warning] You May Need define $envKey for init Gear framework!".PHP_EOL;
        }
    }

    private function loadSchduleTaskModule() {

    }

    // todo 类加载 区分场景，http、tcp等
    protected function loadWebServerContainer() {
        if (!defined("USER_PATH")) {
            $this->setPath("USER_PATH", PROJECT_PATH."/src");
        }
        $hash = $this->getFilePaths(USER_PATH);
        $this->register($hash);
        Config::set(["GLOBAL_USER_CLASS"=>array_keys($hash)]);
    }

    protected function loadCore() {
        $hash = $this->getFilePaths(CORE_PATH);
        $this->register($hash);
        Config::set(["GLOBAL_CORE_CLASS"=>array_keys($hash)]);
    }

    protected function initConfig() {
        Config::init();
    }

    protected function loadModulePackages() {
        $dependencies = Config::get("dependency");
        if (is_null($dependencies)) {
            return;
        }
        $this->loadModules($dependencies);
    }

    public function loadModules(array $modules) {
        $packages = Config::get("package");
        $dependencies = EzCollectionUtils::matchKeys($modules, $packages);
        $hash = SysUtils::searchModules($dependencies);
        $this->register($hash);
        foreach ($hash as $className => $classPath) {
            if (is_subclass_of($className, EzComponent::class)) {
                Config::add("GLOBAL_COMPONENTS", $className);
            }
        }
    }

    private function getFilePaths($path)
    {
        $hash = [];
        //过滤掉点和点点
        $map = array_filter(scandir($path), function($var) {
            return $var[0] != '.';
        });
        foreach ($map as $item) {
            $curPath = $path.'/'.$item;
            if(is_dir($curPath)){
                if($item == '.' || $item == '..'){
                    continue;
                }
                $hash += $this->getFilePaths($curPath);
            }
            if(false === strpos($item,".php")){
                continue;
            }
            if(is_file($curPath)){
                $className = str_replace('.php','',$item);
                //$curPath = str_replace("/", "\\", $curPath);
                $hash[$className] = $curPath;
            }
        }
        return $hash;
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

    protected static function getHome() {
        if (self::isWin()) {
            return getenv("HOMEDRIVE").getenv("HOMEPATH");
        } else {
            return getenv("HOME");
        }
    }

    public static function isWin() {
        return self::OS_WINDOWS === self::getSimlpeOs();
    }

    public static function isLinux() {
        return self::OS_LINUX === self::getSimlpeOs();
    }

    public static function isUnix() {
        return self::isLinux() || self::isMac();
    }

    public static function isMac() {
        return self::OS_MAC === self::getSimlpeOs();
    }

    protected function register($hash) {
        spl_autoload_register(function ($className) use($hash){
            $filePath = $hash[$className] ?? "";
            if(file_exists($filePath)){
                include($filePath);
            }
        });
    }

    /**
     * The Script Mode Startup
     * 1.Environment Variable Configuration
     * 2.Core Loading
     * 3.Configuration Injection
     * 4.Dependency Package Loading
     * @param $constants
     * @return self
     */
    public static function runScript($constants = null) {
        $app = new self();
        $app->envConstants($constants);
        $app->loadCore();
        Env::setRunModeScript();
        $app->initConfig();
        $app->loadModulePackages();
        return $app;
    }

    /**
     * The WebServer Mode Startup
     * 1.Environment Variable Configuration
     * 2.Core Loading
     * 3.Configuration Injection
     * 4.Dependency Package Loading
     * 5.User Bean Injection & Web Container Loading
     * @param $constants
     * @return self
     */
    public static function runWebServer($constants = null) {
        $app = new self();
        $app->envConstants($constants);
        $app->loadCore();
        Env::setRunModeConsole();
        $app->initConfig();
        $app->loadModulePackages();
        $app->loadWebServerContainer();
        return $app;
    }

    /**
     * The Schedule Task Mode Startup
     * 1.Environment Variable Configuration
     * 2.Core Loading
     * 3.Configuration Injection
     * 4.Dependency Package Loading
     * @param $constants
     * @return SchduleTaskApplication
     */
    public static function runSchduleTask($constants = null) {
        $app = new self();
        $app->envConstants($constants);
        $app->loadCore();
        Env::setRunModeConsole();
        $app->initConfig();
        $app->loadModulePackages();
        $app->loadSchduleTaskModule();
        return new SchduleTaskApplication($app);
    }
}
