<?php
/**
 * 功能：框架类自动加载
 * 说明：建议将此文件置于项目目录内，与gear框架目录同级
 * 示例:
 * ├── project
 *  ├── autoload.php
 *  ├── src   项目主目录
 *  ├── config   用户配置目录
 *  ├── gear     gear框架根目录
 *  ├── scripts  项目脚本文件目录
 *  └── static   项目静态文件目录
 * 首次使用：将此文件改名为.php文件 并移到与gear框架目录同级
 */
define("PROJECT_PATH", dirname(__FILE__, 1)."/../");
include_once(PROJECT_PATH."/gear/application.php");
Application::runScript(["USER_PATH"=>"src", "CONFIG_PATH"=>"config"]);
