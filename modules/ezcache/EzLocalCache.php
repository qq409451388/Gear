<?php

class EzLocalCache extends EzCache
{
    private const EXCEPTION_PREFIX = "[EzLocalCache Exception] ";
    private const UNSUPPORT_COMMAND = self::EXCEPTION_PREFIX."Unsupport Command %s From %s";
    protected static $ins;
    /**
     * 数据空间
     * @var array<string, EzLocalCacheObject>
     */
    protected $_concurrentHashMap = [];

    private $_hashMapBuffer = [];

    private $transactionSwitch = false;

    private $calledTimeThresold = 10;

    /**
     * @var int 内存空间限制
     */
    public $memoryLimit;

    /**
     * @var int 实际耗费的内存
     */
    public $memoryCost;

    private function has(string $k)
    {
        return isset($this->_concurrentHashMap[$k]);
    }

    /**
     * @param string $k
     * @return EzLocalCacheObject|null
     */
    private function fetch(string $k)
    {
        return $this->_concurrentHashMap[$k]??null;
    }

    /**
     * 当空间占用超限时调用，以释放内存
     * @return void
     */
    public function cleanUpTheRoom() {
        Logger::console("Normal Cleanup is Running!");
        foreach ($this->_concurrentHashMap as $k => $cacheObject) {
            if ($cacheObject->isExpire()
                || $cacheObject->isCalledFrequently($this->calledTimeThresold)) {
                unset($this->_concurrentHashMap[$k]);
            }
        }
    }

    public function cleanUpTheRoomForce() {
        Logger::console("Force Cleanup is Running!");
        $this->_concurrentHashMap = array_slice($this->_concurrentHashMap, 0, count($this->_concurrentHashMap)/2);
    }

    public function tryRelease() {
        $this->memoryCost = memory_get_usage();
        if ($this->memoryCost > $this->memoryLimit) {
            $this->cleanUpTheRoom();
        }
        $this->memoryCost = memory_get_usage();
        if ($this->memoryCost > $this->memoryLimit) {
            $this->cleanUpTheRoomForce();
        }
        $this->memoryCost = memory_get_usage();
        if ($this->memoryCost > $this->memoryLimit) {
            $this->flushAll();
        }
    }

    private function unsupportException($k, $operateDataType, $funcName){
        switch ($operateDataType) {
            case EzLocalCacheObject::T_LIST:
                DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
                    sprintf(self::UNSUPPORT_COMMAND, $funcName, $this->_concurrentHashMap[$k]->getDataType()));
                break;
            case EzLocalCacheObject::T_HASH:
                DBC::assertTrue($this->_concurrentHashMap[$k]->isMap(),
                    sprintf(self::UNSUPPORT_COMMAND, $funcName, $this->_concurrentHashMap[$k]->getDataType()));
                break;
            case EzLocalCacheObject::T_STRING:
            case EzLocalCacheObject::T_INT:
            case EzLocalCacheObject::T_FLOAT:
            default:
                DBC::assertTrue($this->_concurrentHashMap[$k]->isNormal(),
                    sprintf(self::UNSUPPORT_COMMAND, $funcName, $this->_concurrentHashMap[$k]->getDataType()));
                break;
        }
    }

    private function isExpire($k)
    {
        if(!$this->has($k)){
            return true;
        }
        return $this->_concurrentHashMap[$k]->isExpire();
    }

    private function initEmptyList(string $k){
        $this->_concurrentHashMap[$k] = EzLocalCacheObject::create([], null, EzLocalCacheObject::T_LIST);
    }

    /**
     * 存在 或 过期（并删除）
     * @param string $k
     * @return bool
     */
    public function exists(string $k): bool
    {
        if (!$this->has($k) || ($this->isExpire($k) && $this->del($k))) {
            return false;
        }
        return true;
    }

    public function del(string $k): bool
    {
        unset($this->_concurrentHashMap[$k]);
        return true;
    }

    public function keys(string $k): array
    {
        return array_keys($this->_concurrentHashMap);
    }

    public function flushAll():bool {
        $this->_concurrentHashMap = [];
        return true;
    }

    public function expire(string $k, int $expire): bool
    {
        DBC::assertTrue($this->has($k), "[EzLocalCache Exception] No Match Key: $k");
        $this->fetch($k)->setExpire($expire);
        return true;
    }

    public function ttl(string $k):int
    {
        DBC::assertTrue($this->has($k), "[EzLocalCache Exception] No Match Key: $k");
        return $this->fetch($k)->ttl();
    }

    public function set(string $k, string $v): bool
    {
        $this->_concurrentHashMap[$k] = EzLocalCacheObject::create($v);
        return true;
    }

    public function setEX(string $k, $expire, string $v):bool
    {
        $expire = intval($expire);
        $this->_concurrentHashMap[$k] = EzLocalCacheObject::create($v, $expire);
        return true;
    }

    public function setNX(string $k, string $v, int $expire = 7200): bool
    {
        if($this->exists($k)){
            return false;
        }
        return $this->setEX($k, $expire, $v);
    }

    public function setXX(string $k, string $v, int $expire = 7200): bool
    {
        if(!$this->exists($k)){
            return false;
        }
        return $this->setEX($k, $expire, $v);
    }

    public function get(string $k): string
    {
        if (!$this->exists($k)) {
            return "";
        }
        return $this->_concurrentHashMap[$k]->getData();
    }

    public function incr(string $k):int
    {
        $val = $this->get($k);
        if (empty($val)) {
            $val = 0;
        }
        DBC::assertNumeric($val, "[EzLocalCache Exception] value is not an integer");
        $this->set($k, ++$val);
        return $val;
    }

    public function incrBy(string $k, int $by): int
    {
        $val = $this->get($k);
        if (empty($val)) {
            $val = 0;
        }
        DBC::assertNumeric($val, "[EzLocalCache Exception] value is not an integer");
        $val+=$by;
        $this->set($k, $val);
        return $val;
    }

    public function incrByFloat(string $k, string $by): string
    {
        $val = $this->get($k);
        if (empty($val)) {
            $val = "0";
        }
        DBC::assertNumeric($by, "[EzLocalCache Exception] input value $by is not an float");
        DBC::assertNumeric($val, "[EzLocalCache Exception] value is not an float");
        $scale = max(strlen(substr(strrchr($by, "."), 1)), strlen(strrchr($val, "."), 1));
        $val = bcadd($val, $by, $scale);
        $this->set($k, $val);
        return $val;
    }

    public function decr(string $k): int
    {
        $val = $this->get($k);
        if (empty($val)) {
            $val = 0;
        }
        DBC::assertNumeric($val, "[EzLocalCache Exception] value is not an integer");
        $val--;
        $this->set($k, $val);
        return $val;
    }

    public function decrBy(string $k, int $by): int
    {
        $val = $this->get($k);
        if (empty($val)) {
            $val = "0";
        }
        DBC::assertNumeric($by, "[EzLocalCache Exception] input value $by is not an integer");
        DBC::assertNumeric($val, "[EzLocalCache Exception] value is not an integer");
        $val -= $by;
        $this->set($k, $val);
        return $val;
    }

    /**
     * @description build for debug
     * @deprecated only for debug
     * @return EzLocalCacheObject[]
     */
    public function getAll() {
        return Env::isDev() ? $this->_concurrentHashMap : [];
    }

    public function lPop(string $k): string
    {
        if (!$this->exists($k)){
            return "";
        }
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        return array_shift($this->_concurrentHashMap[$k]->dataSource);
    }

    public function rPop(string $k): string
    {
        if (!$this->exists($k)){
            return "";
        }
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        return array_pop($this->_concurrentHashMap[$k]->dataSource);
    }

    public function lPush(string $k, string ...$v): int
    {
        if (!$this->exists($k)) {
            $this->initEmptyList($k);
        }
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        $cnt = 0;
        foreach ($v as $iv) {
            array_unshift($this->_concurrentHashMap[$k]->dataSource, $iv);
            $cnt++;
        }
        return $cnt;
    }

    public function rPush(string $k, string ...$v): int
    {
        if (!$this->exists($k)) {
            $this->initEmptyList($k);
        }
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        $cnt = 0;
        foreach ($v as $iv) {
            array_push($this->_concurrentHashMap[$k]->dataSource, $iv);
            $cnt++;
        }
        return $cnt;
    }

    public function rPopLPush(string $k1, string $k2): string
    {
        DBC::assertTrue($this->exists($k1), "[EzLocalCache Exception] Unset $k1!");
        $this->unsupportException($k1, EzLocalCacheObject::T_LIST, __FUNCTION__);
        $this->unsupportException($k2, EzLocalCacheObject::T_LIST, __FUNCTION__);
        $val = $this->rPop($k1);
        $this->lPush($k2, $val);
        return $val;
    }

    public function lRange(string $k, int $start, int $end): array
    {
        DBC::assertTrue($this->exists($k), "[EzLocalCache Exception] Unset $k!");
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        return array_slice($this->fetch($k)->getData(), $start, $end);
    }

    public function lLen(string $k): int
    {
        if (!$this->exists($k)) {
            return 0;
        }
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        return count($this->fetch($k)->getData());
    }

    public function lPos(string $k, string $elementValue, int $rank = null): int
    {
        if (!$this->exists($k)) {
            return 0;
        }
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        $list = $this->fetch($k)->dataSource;
        $cnt = 0;
        foreach ($list as $index => $value) {
            if ($value == $elementValue) {
                $cnt++;
                if (null === $rank) {
                    return $index;
                } else if ($cnt == $rank) {
                    return $index;
                }
            }
        }
        return -1;
    }

    public function lRem(string $k, int $count, $val): int
    {
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        $list = $this->fetch($k)->dataSource;
        $cnt = 0;
        if ($count < 0) {
            rsort($list);
        }
        $absCount = abs($count);
        foreach ($list as $index => $value) {
            if ($value === $val) {
                $cnt++;
                if (0 === $count) {
                    unset($index);
                } else if ($cnt === $absCount) {
                    unset($index);
                }
            }
        }
        if ($count < 0) {
            rsort($list);
        }
        $this->fetch($k)->dataSource = array_values($list);
        return $cnt;
    }

    public function lIndex(string $k, int $index): string
    {
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        $list = $this->fetch($k)->dataSource;
        return $list[$index]??"";
    }

    public function lSet(string $k, int $index, string $val): bool
    {
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        $size = count($this->fetch($k)->dataSource);
        $trueIndex = $index >= 0 ? $index : $size + $index;
        DBC::assertNonNull($this->fetch($k)->dataSource[$trueIndex], "[EzLocalCache Exception] Out Of Bounds!");
        $this->fetch($k)->dataSource[$trueIndex] = $val;
        return true;
    }

    public function lTrim(string $k, int $start, int $end): bool
    {
        $this->unsupportException($k, EzLocalCacheObject::T_LIST, __FUNCTION__);
        $list = $this->lRange($k, $start, $end);
        $this->fetch($k)->dataSource = $list;
        return true;
    }

    public function hSet(string $k, string $field, string $value): int
    {
        $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
        $map = $this->_concurrentHashMap[$k]->dataSource??[];
        $map[$field] = $value;
        $this->_concurrentHashMap[$k] = EzLocalCacheObject::create($map, null, EzLocalCacheObject::T_HASH);
        return true;
    }

    public function hSetMulti(string $k, string ...$args): int
    {
        return $this->hMSet($k, ...$args);
    }

    public function hSetNx(string $k, string $field, string $value): int
    {
        if (isset($this->fetch($k)->dataSource[$field])) {
            return 0;
        }
        return $this->fetch($k)->dataType[$field] = $value;
    }

    public function hMSet(string $k, string ...$args): bool
    {
        $map = $this->fetch($k)->dataSource??[];
        foreach ($args as $index => $arg) {
            if (0 === $index%2) {
                $map[$arg] = $args[$index+1];
            }
        }
        $this->_concurrentHashMap[$k] = EzLocalCacheObject::create($map, null, EzLocalCacheObject::T_HASH);
        return true;
    }

    public function hExists(string $k, string $field): int
    {
        $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
        return $this->has($k) && array_key_exists($field, $this->fetch($k)->dataSource);
    }

    public function hGet(string $k, string $field): string
    {
        if (!$this->has($k)) {
            return "";
        }
        $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
        return $this->fetch($k)->getData()[$field];
    }

    public function hMGet(string $k, string ...$fields): array
    {
        $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
        return EzCollectionUtils::matchKeys($fields, $this->fetch($k)->getData()??[]);
    }

    public function hGetAll(string $k): array
    {
        $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
        return $this->_concurrentHashMap[$k]->getData()??[];
    }

    public function hIncrBy(string $k, string $field, int $by): int
    {
        $map = $this->fetch($k)->dataSource??[];
        if (empty($map)) {
            $map = [
                $field => $by
            ];
            $this->_concurrentHashMap[$k] = EzLocalCacheObject::create($map, null, EzLocalCacheObject::T_HASH);
        } else {
            $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
            if (!isset($map[$field])) {
                $this->_concurrentHashMap[$k]->dataSource[$field] = 0;
            }
            DBC::assertNumeric($this->_concurrentHashMap[$k]->dataSource[$field],
                self::EXCEPTION_PREFIX." UnSupport Command ".__FUNCTION__." With Data ".$this->_concurrentHashMap[$k]->dataSource[$field]);
            $this->_concurrentHashMap[$k]->dataSource[$field] += $by;
        }
        return true;
    }

    public function hIncrByFloat(string $k, string $field, string $by): string
    {
        $map = $this->fetch($k)->dataSource??[];
        if (empty($map)) {
            $map = [
                $field => $by
            ];
            $this->_concurrentHashMap[$k] = EzLocalCacheObject::create($map, null, EzLocalCacheObject::T_HASH);
        } else {
            $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
            if (!isset($map[$field])) {
                $this->_concurrentHashMap[$k]->dataSource[$field] = "0";
            }
            DBC::assertNumeric($this->_concurrentHashMap[$k]->dataSource[$field],
                self::EXCEPTION_PREFIX." UnSupport Command ".__FUNCTION__." With Data ".$this->_concurrentHashMap[$k]->dataSource[$field]);
            $scale = max(strlen(substr(strrchr($by, "."), 1)),
                    strlen(substr(strrchr($this->_concurrentHashMap[$k]->dataSource[$field], "."), 1)));
            $this->_concurrentHashMap[$k]->dataSource[$field] = bcadd($by, $this->_concurrentHashMap[$k]->dataSource[$field], $scale);
        }
        return true;
    }

    public function hDel(string $k, string ...$fields): int
    {
        if (!$this->has($k)) {
            return true;
        }
        foreach ($fields as $field) {
            unset($this->fetch($k)->dataSource[$field]);
        }
        return true;
    }

    public function hKeys(string $k): array
    {
        $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
        $map = $this->_concurrentHashMap[$k]->getData()??[];
        return array_keys($map);
    }

    public function hVals(string $k): array
    {
        $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
        $map = $this->_concurrentHashMap[$k]->getData()??[];
        return array_values($map);
    }

    public function hLen(string $k): int
    {
        $this->unsupportException($k, EzLocalCacheObject::T_HASH, __FUNCTION__);
        $map = $this->_concurrentHashMap[$k]->getData()??[];
        return count($map);
    }

    public function hello($version) {
        return true;
    }

    public function quit() {
        return true;
    }

    public function putSource(string $k, array $v) {
        if (EzObjectUtils::isList($v)) {
            $this->_concurrentHashMap[$k] = EzLocalCacheObject::create($v, null, EzLocalCacheObject::T_LIST);
        } else {
            $this->_concurrentHashMap[$k] = EzLocalCacheObject::create($v, null, EzLocalCacheObject::T_HASH);
        }
    }

    public function getSource(string $k) {
        if (!$this->has($k)) {
            return null;
        }
        return $this->_concurrentHashMap[$k]->dataSource;
    }
}
