<?php
class EzRedis
{
    protected $tcp = null;

    protected $mode = 1;

    protected $appoint;

    protected $cmd = null;

    protected $command = null;

    protected $isPipeline = false;

    protected $pipelineCmd = '';

    protected $pipelineCount = 0;

    protected $auth;

    protected $isAuthed;

    protected $response = '';

    private const MODE_SINGLE = 1;
    private const MODE_CLUSTER = 2;

    private const EXPIRE_WEEK = 604800;

    public function connect($host = '127.0.0.1', $port = 6379, $timeout = 0){
        $this->tcp = EzTCP::get($host, $port);
        $this->mode = self::MODE_SINGLE;
    }

    public function connectCluster(string $clusterName, $timeout = 0){
        $configs = Config::get("rediscluster")[$clusterName];
        DBC::assertTrue(!empty($configs), "[EzRedis Exception] Config $clusterName is Not Exists!");
        $this->auth($configs['auth']);
        $this->connectWithCluster($configs['server'], $timeout);
    }

    private function connectWithCluster(array $configs, $timeout = 0){
        foreach($configs as $config){
            $configArr = explode(":", $config);
            $this->tcp[$config] = EzTCP::get($configArr[0], $configArr[1]);
        }
        $this->mode = self::MODE_CLUSTER;
    }

    protected function buildCommand($args){
        $this->cmd = $args;
        $cmds = array();
        $cmds[] = '*' . count($args) . "\r\n";
        foreach($args as $arg) {
            $cmds[] = '$' . strlen($arg) . "\r\n$arg\r\n";
        }

        $this->command = implode($cmds);
    }

    protected function buildResult(){
        if(empty($this->response)){
            return "";
        }

        if ($this->response[0] == '-') {
            $this->response = ltrim($this->response, '-');
            list($errstr, $this->response) = explode("\r\n", $this->response, 2);
            DBC::assertTrue($this->isClusterMode(), "[EzRedis Exception] ".$errstr, 500);
            return $this->whenErr($errstr);
        }

        switch($this->response[0]) {
            case '+':
            case ':':
                list($ret, $this->response) = explode("\r\n", $this->response, 2);
                $ret = substr($ret, 1);
                break;
            case '$':
                $this->response = ltrim($this->response, '$');
                list($slen, $this->response) = explode("\r\n", $this->response, 2);
                $ret = substr($this->response, 0, intval($slen));
                $this->response = substr($this->response, 2 + $slen);
                break;
            case '*':
                $ret = $this->toArray();
                break;
            default:
                $ret = "";
                break;
        }
        return $ret;
    }

    protected function toArray(){
        $ret = array();
        $this->response = ltrim($this->response, '*');
        list($count, $this->response) = explode("\r\n", $this->response, 2);
        for($i = 0; $i < $count; $i++) {
            $tmp = $this->buildResult();
            $ret[] = $tmp;
        }
        return $ret;
    }

    public function exec(...$args){
        $tcpIns = $this->getTcp();
        $key = $tcpIns->getIp().":".$tcpIns->getPort();
        if((!isset($this->isAuthed[$key]) || !$this->isAuthed[$key]) && !empty($this->auth)){
            if("AUTH" == $args[0]){
                $this->isAuthed[$key] = true;
            }
            $this->exec("AUTH", $this->auth);
        }
        $this->cmd = null;
        if (func_num_args() == 0) {
            DBC::throwEx("[EzRedis Exception]参数不可以为空", 301);
        }
        $this->buildCommand($args);
        if (true === $this->isPipeline) {
            $this->pipelineCmd .= $this->command;
            $this->pipelineCount++;
            return "";
        }
        $this->response = $tcpIns->send($this->command);
        return $this->buildResult();
    }

    private function getTcp():BaseTcpClient{
        if($this->isSingleMode()){
            return $this->tcp;
        }
        $point = $this->appoint;
        if(empty($point)){
            $index = mt_rand(0, count($this->tcp) - 1);
            $point = array_keys($this->tcp)[$index];
            $this->appoint = $point;
        }
        return $this->tcp[$point];
    }

    public function initPipeline(){
        $this->isPipeline = true;
        $this->pipelineCount = 0;
        $this->pipelineCmd = '';
    }

    public function isSingleMode(){
        return $this->mode == self::MODE_SINGLE;
    }

    public function isClusterMode(){
        return $this->mode == self::MODE_CLUSTER;
    }

    private function redirectAppoint($appoint){
        $this->appoint = $appoint;
        Logger::console("[EzRedis]Redirect To $appoint");
        return $this;
    }

    private function whenErr($error){
        $errArr = explode(" ", $error);
        $errInfo = $errArr[0];
        if("MOVED" == $errInfo){
            $this->redirectAppoint($errArr[2]);
            return call_user_func_array([$this, 'exec'], $this->cmd);
        }else{
            DBC::throwEx("[EzRedis Error] $error");
            return false;
        }
    }

    public function auth($password){
        $this->auth = $password;
        return $this;
    }

    public function set($key, $value, int $expire = self::EXPIRE_WEEK){
        $expire = empty($expire) ? self::EXPIRE_WEEK : $expire;
        return $this->exec("SET", $key, $value, "EX", $expire);
    }

    public function setNx($key, $value, int $expire = self::EXPIRE_WEEK){
        $expire = empty($expire) ? self::EXPIRE_WEEK : $expire;
        return $this->exec("SET", $key, $value, "NX", "EX", $expire);
    }

    public function setXx($key, $value, int $expire = self::EXPIRE_WEEK){
        $expire = empty($expire) ? self::EXPIRE_WEEK : $expire;
        return $this->exec("SET", $key, $value, "XX", "EX", $expire);
    }

    public function get($key){
        return $this->exec("GET", $key);
    }

    public function del($key){
        return $this->exec("DEL", $key);
    }

    public function keys($pattern){
        return $this->exec("KEYS", $pattern);
    }

}