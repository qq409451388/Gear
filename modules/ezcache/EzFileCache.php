<?php
class EzFileCache extends EzLocalCache
{
    private $commandList;
    private $writeMode;

    private const RO_COMMAND_LIST = [
        "get", "lRange", "hGet", "hMGet, getAll"
    ];
    private const AOF_MODE = "AOF";
    private const RDB_MODE = "RDB";

    public function __construct() {
        $this->commandList = [];
        $this->writeMode = self::RDB_MODE;
        $this->initDataBase();
        $this->initExceptionHandler();
        $this->initWorker();
    }

    private function initDataBase() {
        $data = Logger::get(__CLASS__, true);
        $data = explode(PHP_EOL, $data);
        $len = count($data);
        for ($i = 0; $i < $len; $i+=2) {
            if (!isset($data[$i+1])) {
                continue;
            }
            $k = $data[$i];
            $obj = $data[$i+1];
            $this->_concurrentHashMap[$k] = unserialize($obj);
        }
    }

    private function initExceptionHandler() {
        $method = self::AOF_MODE === $this->writeMode ? "writeAof" : "writeRdb";
        /*register_shutdown_function(function($args) {
            list($method) = $args;
            var_dump($args);
            $this->$method();
        }, $method);*/
        set_exception_handler([$this, $method]);
    }

    private function initWorker() {
        if (Env::isUnix()) {
            $this->initWorkerForUnix();
        } else if (Env::isWin()) {
            $this->initWorkerForWindows();
        } else {
            DBC::throwEx("[EzFileCache Exception] Please Run Gear From Unix Or Windows!");
        }
    }

    private function initWorkerForUnix() {

    }

    private function initWorkerForWindows() {

    }

    private function callParent(...$args) {
        $trace = debug_backtrace();
        $method = $trace[1]['function'];
        if ($this->isRwCommand($method)) {
            $this->commandList[] = $method." ".implode(" ", $args);
        }
        return parent::$method(...$args);
    }

    private function isRwCommand($method) {
        return !in_array($method, self::RO_COMMAND_LIST);
    }

    public function writeAof() {
        if (!empty($this->commandList)) {
            foreach ($this->commandList as $command) {
                Logger::save($command, __CLASS__);
            }
        }
    }

    public function writeRdb() {
        $msg = "";
        foreach ($this->_concurrentHashMap as $k => $obj) {
            $msg .= $k.PHP_EOL;
            $msg .= serialize($obj).PHP_EOL;
        }
        Logger::reSave($msg, __CLASS__);
    }

    public function set(string $k, string $v):bool {
        return $this->callParent($k, $v);
    }

    public function __destory() {
        if (self::AOF_MODE === $this->writeMode) {
            $this->writeAof();
        }
        if (self::RDB_MODE === $this->writeMode) {
            $this->writeRdb();
        }
    }
}
