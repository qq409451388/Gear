<?php
class EzLocalCache extends EzCache
{
    protected static $ins;
    /**
     * 数据空间
     * @var array<string, EzLocalCacheObject>
     */
    private $_concurrentHashMap = [];

    private $_hashMapBuffer = [];

    private $transactionSwitch = false;

    private function has(string $k)
    {
        return isset($this->_concurrentHashMap[$k]);
    }

    private function fetch(string $k): EzLocalCacheObject
    {
        return $this->_concurrentHashMap[$k];
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

    public function setEX(string $k, string $v, int $expire = 7200):bool
    {
        $this->_concurrentHashMap[$k] = EzLocalCacheObject::create($v, $expire);
        return true;
    }

    public function setNX(string $k, string $v, int $expire = 7200): bool
    {
        if($this->exists($k)){
            return false;
        }
        return $this->setEX($k, $v, $expire);
    }

    public function setXX(string $k, string $v, int $expire = 7200): bool
    {
        if(!$this->exists($k)){
            return false;
        }
        return $this->setEX($k, $v, $expire);
    }

    public function get(string $k): string
    {
        if (!$this->exists($k)) {
            return "";
        }
        return $this->_concurrentHashMap[$k]->dataSource;
    }

    public function incr(string $k):int
    {
        $val = $this->get($k);
        if (empty($val)) {
            $val = 0;
        }
        DBC::assertTrue(is_numeric($val), "[EzLocalCache Exception] value is not an integer");
        $val++;
        $this->set($k, $val);
        return $val;
    }

    public function incrBy(string $k, int $by): int
    {
        $val = $this->get($k);
        if (empty($val)) {
            $val = 0;
        }
        DBC::assertTrue(is_numeric($val), "[EzLocalCache Exception] value is not an integer");
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
        DBC::assertTrue(is_numeric($by), "[EzLocalCache Exception] input value $by is not an float");
        DBC::assertTrue(is_numeric($val), "[EzLocalCache Exception] value is not an float");
        $val = bcadd($val, $by);
        $this->set($k, $val);
        return $val;
    }

    public function decr(string $k): int
    {
        $val = $this->get($k);
        if (empty($val)) {
            $val = 0;
        }
        DBC::assertTrue(is_numeric($val), "[EzLocalCache Exception] value is not an integer");
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
        DBC::assertTrue(is_numeric($by), "[EzLocalCache Exception] input value $by is not an integer");
        DBC::assertTrue(is_numeric($val), "[EzLocalCache Exception] value is not an integer");
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
        return $this->_concurrentHashMap;
    }

    public function lPop(string $k): string
    {
        if (!$this->exists($k)){
            return "";
        }
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command lPop From ".$this->_concurrentHashMap[$k]->getDataType());
        //return array_pop($this->_concurrentHashMap[$k]->dataSource);
        return array_shift($this->_concurrentHashMap[$k]->dataSource);
    }

    public function rPop(string $k): string
    {
        if (!$this->exists($k)){
            return "";
        }
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command rPop From ".$this->_concurrentHashMap[$k]->getDataType());
        return array_pop($this->_concurrentHashMap[$k]->dataSource);
    }

    public function lPush(string $k, string ...$v): int
    {
        if (!$this->exists($k)) {
            $this->initEmptyList($k);
        }
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command lPush From ".$this->_concurrentHashMap[$k]->getDataType());
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
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command rPush From ".$this->_concurrentHashMap[$k]->getDataType());
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
        DBC::assertTrue($this->_concurrentHashMap[$k1]->isList(),
            "[EzLocalCache Exception] Unsupport Command rPopLPush From ".$this->_concurrentHashMap[$k1]->getDataType());
        DBC::assertTrue($this->_concurrentHashMap[$k2]->isList(),
            "[EzLocalCache Exception] Unsupport Command rPopLPush From ".$this->_concurrentHashMap[$k2]->getDataType());
        $val = $this->rPop($k1);
        $this->lPush($k2, $val);
        return $val;
    }

    public function lRange(string $k, int $start, int $end): array
    {
        DBC::assertTrue($this->exists($k), "[EzLocalCache Exception] Unset $k!");
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command lRange From ".$this->_concurrentHashMap[$k]->getDataType());
        return array_slice($this->fetch($k)->dataSource, $start, $end);
    }

    public function lLen(string $k): int
    {
        if (!$this->exists($k)) {
            return 0;
        }
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command lLen From ".$this->_concurrentHashMap[$k]->getDataType());
        return count($this->fetch($k)->dataSource);
    }

    public function lPos(string $k, string $elementValue, int $rank = null): int
    {
        if (!$this->exists($k)) {
            return 0;
        }
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command lPos From ".$this->_concurrentHashMap[$k]->getDataType());
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
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command lRem From ".$this->_concurrentHashMap[$k]->getDataType());
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
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command lIndex From ".$this->_concurrentHashMap[$k]->getDataType());
        $list = $this->fetch($k)->dataSource;
        return $list[$index]??"";
    }

    public function lSet(string $k, int $index, string $val): bool
    {
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command lSet From ".$this->_concurrentHashMap[$k]->getDataType());
        $size = count($this->fetch($k)->dataSource);
        $trueIndex = $index >= 0 ? $index : $size + $index;
        DBC::assertTrue(!is_null($this->fetch($k)->dataSource[$trueIndex]), "[EzLocalCache Exception] Out Of Bounds!");
        $this->fetch($k)->dataSource[$trueIndex] = $val;
        return true;
    }

    public function lTrim(string $k, int $start, int $end): bool
    {
        DBC::assertTrue($this->_concurrentHashMap[$k]->isList(),
            "[EzLocalCache Exception] Unsupport Command lTrim From ".$this->_concurrentHashMap[$k]->getDataType());
        $list = $this->lRange($k, $start, $end);
        $this->fetch($k)->dataSource = $list;
        return true;
    }

    public function hSet(string $k, string $field, string $value): int
    {
        // TODO: Implement hSet() method.
    }

    public function hSetMulti(string $k, string ...$args): int
    {
        // TODO: Implement hSetMulti() method.
    }

    public function hSetNx(string $k, string $field, string $value): int
    {
        // TODO: Implement hSetNx() method.
    }

    public function hMSet(string $k, string ...$args): bool
    {
        // TODO: Implement hMSet() method.
    }

    public function hExists(string $k, string $field): int
    {
        // TODO: Implement hExists() method.
    }

    public function hGet(string $k, string $field): string
    {
        // TODO: Implement hGet() method.
    }

    public function hMGet(string $k, string ...$fields): array
    {
        // TODO: Implement hMGet() method.
    }

    public function hGetAll(string $k): array
    {
        // TODO: Implement hGetAll() method.
    }

    public function hIncrBy(string $k, string $field, int $by): int
    {
        // TODO: Implement hIncrBy() method.
    }

    public function hIncrByFloat(string $k, string $field, string $by): string
    {
        // TODO: Implement hIncrByFloat() method.
    }

    public function hDel(string $k, string ...$fields): int
    {
        // TODO: Implement hDel() method.
    }

    public function hKeys(string $k): array
    {
        // TODO: Implement hKeys() method.
    }

    public function hVals(string $k): array
    {
        // TODO: Implement hVals() method.
    }

    public function hLen(string $k): int
    {
        // TODO: Implement hLen() method.
    }
}
