<?php

/**
 * 初始化依赖，只调用基础函数
 * @description 使用方式：定义项目路径常量PROJECT_PATH, 在启动文件require once该文件，调用启动方法即可
 */
class Application
{
    private function envConstants($constants = null) {
        $this->envCheck("PROJECT_PATH");
        if (!defined("GEAR_PATH")) {
            define("GEAR_PATH", PROJECT_PATH."/gear");
        }
        if (!defined("CORE_PATH")) {
            define("CORE_PATH", GEAR_PATH."/core");
        }
        if (!empty($constants)) {
            foreach ($constants as $k => $v) {
                define($k, PROJECT_PATH.DIRECTORY_SEPARATOR.$v);
            }
        }
        $this->envCheck2("USER_PATH");
        $this->envCheck2("CONFIG_PATH");
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

    protected function loadUserDefined() {
        if (!defined("USER_PATH")) {
            return;
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

    protected function register($hash) {
        spl_autoload_register(function ($className) use($hash){
            $filePath = $hash[$className] ?? "";
            if(file_exists($filePath)){
                include($filePath);
            }
        });
    }

    public static function runScript($constants = null) {
        $app = new self();
        $app->envConstants($constants);
        $app->loadCore();
        $app->initConfig();
        $app->loadModulePackages();
        $app->loadUserDefined();
        Env::setRunModeScript();
        return $app;
    }

    public static function runWebServer($constants = null) {
        $app = new self();
        $app->envConstants($constants);
        $app->loadCore();
        $app->initConfig();
        $app->loadModulePackages();
        $app->loadUserDefined();
        Env::setRunModeConsole();
        return $app;
    }
}
