<?php
class EzRedis extends EzCache
{
    protected $tcp = null;

    protected $mode = 1;

    protected $appoint;

    protected $cmd = null;

    protected $command = null;

    protected $auth;

    protected $isAuthed;

    protected $response = '';

    private const MODE_SINGLE = 1;
    private const MODE_CLUSTER = 2;

    private const EXPIRE_WEEK = 604800;

    /**
     * 事务模式，执行命令的结果
     */
    private const REQ_QUEUED = "QUEUED";

    /**
     * 命令执行结果，成功
     */
    private const REQ_OK = "OK";

/*    //是否开启事务
    private $isStartTransaction = false;

    //事务命令集合
    private $transactionCmdSize;*/

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

    private function exec(...$args) {
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

    public function exists(string $k): bool
    {
        return $this->exec("EXISTS", $k);
    }

    public function expire(string $k, int $expire): bool
    {
        return $this->exec("EXPIRE", $k, $expire);
    }

    public function ttl(string $k):int
    {
        return $this->exec("TTL", $k);
    }

    public function flushAll(): bool
    {
        return $this->exec("FLUSHALL");
    }

    public function set(string $key, string $value, int $expire = self::EXPIRE_WEEK):bool {
        $expire = empty($expire) ? self::EXPIRE_WEEK : $expire;
        return $this->exec("SET", $key, $value, "EX", $expire);
    }

    public function setNX(string $key, string $value, int $expire = self::EXPIRE_WEEK):bool {
        $expire = empty($expire) ? self::EXPIRE_WEEK : $expire;
        return $this->exec("SET", $key, $value, "NX", "EX", $expire);
    }

    public function setXX(string $key, string $value, int $expire = self::EXPIRE_WEEK):bool{
        $expire = empty($expire) ? self::EXPIRE_WEEK : $expire;
        return $this->exec("SET", $key, $value, "XX", "EX", $expire);
    }

    public function setEX(string $key, string $value, int $expire = self::EXPIRE_WEEK):bool{
        $expire = empty($expire) ? self::EXPIRE_WEEK : $expire;
        return $this->exec("SET", $key, $value, "EX", $expire);
    }

    public function get(string $key):string {
        return $this->exec("GET", $key);
    }

    public function del(string $key):bool
    {
        if (empty($key)) {
            return false;
        }
        return $this->exec("DEL", $key);
    }

    public function keys(string $pattern):array
    {
        if (empty($pattern)) {
            return [];
        }
        return $this->exec("KEYS", $pattern);
    }

    public function lPop(string $k): string
    {
        return $this->exec("LPOP", $k);
    }

    public function rPop(string $k): string
    {
        return $this->exec("RPOP", $k);
    }

    public function lPush(string $k, ...$v): int
    {
        return $this->exec("LPUSH", $k, ...$v);
    }

    public function rPush(string $k, ...$v): int
    {
        if (empty($k)) {
            return 0;
        }
        return $this->exec("RPUSH", $k, ...$v);
    }

    public function rPopLPush(string $k1, string $k2): string
    {
        return $this->exec("RPOPLPUSH", $k1, $k2);
    }

    public function lRange($k, $start, $end): array
    {
        return $this->exec("LRANGE", $k, $start, $end);
    }

    public function lLen(string $k): int
    {
        return $this->exec("LLEN", $k);
    }

    /**
     * 返回列表中指定元素第一次出现的索引
     * @param string $k
     * @param string $elementValue
     * @param int|null $rank
     * @return int
     * @version redis >= 6.0.6
     */
    public function lPos(string $k, string $elementValue, int $rank = null): int
    {
        return $this->exec("LPOS", $k, $elementValue, $rank);
    }

    public function lRem(string $k, int $count, $val): int
    {
        return $this->exec("LREM", $k, $count, $val);
    }

    public function lIndex(string $k, int $index): string
    {
        return $this->exec("LINDEX", $k, $index);
    }

    public function lSet(string $k, int $index, string $val): bool
    {
        return $this->exec("LSET", $k, $index, $val);
    }

    public function lTrim(string $k, int $start, int $end): bool
    {
        return $this->exec("LTRIM", $k, $start, $end);
    }

    public function startTransaction(): void
    {
        $res = $this->exec("MULTI");
        DBC::assertEquals(self::REQ_OK, $res, "[EzRedis Exception] start transaction fail!");
        $this->isStartTransaction = true;
        $this->transactionCmdSize = 0;
    }

    public function commit(): bool
    {
        $res = $this->exec("EXEC");
        if (!is_array($res)) {
            return false;
        }
        if (count($res) !== $this->transactionCmdSize) {
            return false;
        }
        if (array_count_values($res)[self::REQ_OK] !== $this->transactionCmdSize ) {
            return false;
        }
        return true;
    }

    public function rollBack(): void
    {
        $this->exec("DISCARD");
    }

    public function hExists(string $k, string $field): bool
    {
        // TODO: Implement hExists() method.
    }

    public function hGet(string $k, string $field): string
    {
        // TODO: Implement hGet() method.
    }

    public function hGetAll(string $k): array
    {
        // TODO: Implement hGetAll() method.
    }

    public function hIncrBy(string $k, $field): bool
    {
        // TODO: Implement hIncrBy() method.
    }

    public function hDel(string $k, string ...$fields): bool
    {
        // TODO: Implement hDel() method.
    }

    public function hKeys(string $k): array
    {
        // TODO: Implement hKeys() method.
    }
}
