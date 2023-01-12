<?php
class EzLocalCacheObject
{
    /**
     * @var mixed|null 数据
     */
    public $dataSource;

    /**
     * @var int 数据类型
     */
    public $dataType;

    /**
     * @var int 过期时间
     */
    public $expire;

    public const T_STRING = 1;
    public const T_INT = 2;
    public const T_FLOAT = 3;
    public const T_LIST = 4;
    public const T_HASH = 5;

    private static $dataTypeDesc = [
        self::T_STRING => "基础类型",
        self::T_INT => "基础类型",
        self::T_FLOAT => "基础类型",
        self::T_LIST => "列表类型",
        self::T_HASH => "哈希表类型",
    ];

    public static function create($dataSource, $expire = null, int $dataType = self::T_STRING) {
        $o = new EzLocalCacheObject();
        $o->dataSource = $dataSource;
        $o->dataType = $dataType;
        $o->expire = $expire;
        return $o;
    }

    public function isList():bool {
        return self::T_LIST === $this->dataType;
    }

    public function isMap():bool {
        return self::T_HASH === $this->dataType;
    }

    public function isNormal():bool {
        return self::T_LIST !== $this->dataType && self::T_HASH !== $this->dataType;
    }

    public function getDataType():string
    {
        return self::$dataTypeDesc[$this->dataType]??"[未知]".$this->dataType;
    }

    public function isExpire() {
        if (null === $this->expire) {
            return false;
        }
        return time() >= $this->expire;
    }

    public function setExpire(int $expire) {
        $this->expire = time() + $expire;
    }

    public function ttl():int {
        if (null === $this->expire) {
            return -1;
        }

        $ttl = $this->expire - time();
        DBC::assertTrue($ttl < 0, "[EzLocalCache Exception] data has been expired!");
        return $ttl;
    }
}
