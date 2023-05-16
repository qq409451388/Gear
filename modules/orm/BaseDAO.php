<?php

abstract class BaseDAO implements EzBean
{
    private $ezDbDAO;

    /**
     * @var Clazz $entityClazz
     */
    protected $entityClazz;
    protected $table;
    protected $database;

    /**
     * @var bool 是否存在分表策略
     */
    private $hasSplit;
    private $splitCnt;
    private $splitColumn;
    private $splitModel;

    public function __construct() {
        $this->entityClazz = $this->bindEntity();
        $this->hasSplit = false;
        DBC::assertTrue($this->entityClazz->isSubClassOf(AbstractDO::class),
            "[DAO] create Fail!",0, GearShutDownException::class);

        if (StockCheckRecordDAO::class == get_class($this)) {
            var_dump($this);
        }
        /**
         * @var AnnoItem $annoItem
         */
        $annoItem = AnnoationRule::searchCertainlyRelationshipAnnoation($this->entityClazz->getName(), EntityBind::class);
        if ($annoItem instanceof AnnoItem) {
            /**
             * @var EntityBind $anno
             */
            $anno = $annoItem->getValue();

            $this->table = $anno->getTable();
            $this->database = $anno->getDb();
        } else {
            /**
             * @var AnnoItem $annoItem
             */
            $annoItem = AnnoationRule::searchCertainlyRelationshipAnnoation($this->entityClazz->getName(), EntityBindSplit::class);
            DBC::assertTrue($annoItem instanceof AnnoItem, "[DAO] create Fail!", 0, GearShutDownException::class);
            /**
             * @var EntityBindSplit $anno
             */
            $anno = $annoItem->getValue();
            $this->table = $anno->getTable();
            $this->database = $anno->getDb();
            $this->splitCnt = $anno->getSplit();
            $this->splitColumn = $anno->getSplitColumn();
            $this->splitModel = $anno->getSplitModel();
            $this->hasSplit = true;
        }
        $this->ezDbDAO = new EzDbDAO($this->database);
    }

    abstract protected function bindEntity():Clazz;

    /**
     * @param $whereSql
     * @param $params
     * @return AbstractDO
     * @throws ReflectionException
     */
    public function findOne($whereSql, $params){
        $sql = "select * from `{$this->getTable($params[$this->splitColumn]??null)}` {$whereSql} limit 1";
        return $this->ezDbDAO->findOne($this->entityClazz, $sql, $params);
    }

    /**
     * @param $id
     * @return AbstractDO
     * @throws ReflectionException
     */
    public function findById($id) {
        /**
         * @var EzLocalCache $localCache
         */
        $localCache = CacheFactory::getInstance(CacheFactory::TYPE_MEM);
        if(null != $localCache && $data = $localCache->get($this->entityClazz->getName().$id)){
            return unserialize($data);
        }
        $params = [":id" => $id];
        if ($this->hasSplit) {
            DBC::assertEquals("id", $this->splitColumn,
                "[DAO] this table has split strategy and split column is not 'id'.", 0, GearRunTimeException::class);
            $params["id"] = $id;
        }
        $data = $this->findOne("where id = :id", $params);
        if(empty($data)){
            return null;
        }
        null != $localCache && $localCache->set($this->entityClazz->getName().$id, serialize($data));
        return $data;
    }

    private function getSql4Split($whereSql, $params, $column) {
        $tableList = [];
        foreach ($params[$column] as $id) {
            $tmpTableName = $this->getTable($id);
            if (!isset($tableList[$tmpTableName])) {
                $tableList[$tmpTableName] = [];
            }
            $tableList[$tmpTableName][] = $id;
        }
        $sql = [];
        foreach ($tableList as $tableName => $ids) {
            $tmpParams = $params;
            $tmpParams[$column] = $ids;
            $sql[] = DB::get()->getSql("select * from $tableName $whereSql", $tmpParams);
        }
        $sql = implode(SqlPatternChunk::EOL, $sql);
        return $sql;
    }

    private function findList4Split($whereSql, $params) {
        // 来源是 findByIds
        if ("id" == $this->splitColumn && isset($params[':ids'])) {
            $sql = $this->getSql4Split($whereSql, $params, ":ids");
            $res = DB::get($this->database)->query($sql, [], SqlOptions::new()->isChunk(true));
            $className = $this->entityClazz->getName();
            foreach ($res as &$item) {
                $item = EzBeanUtils::createObject($item, $className);
            }
            return $res;
        }
        return [];
    }

    /**
     * @param $whereSql
     * @param $params
     * @return array<AbstractDO>
     * @throws ReflectionException
     */
    public function findList($whereSql, $params) {
        if ($this->hasSplit) {
            return $this->findList4Split($whereSql, $params);
        } else {
            $sql = "select * from `{$this->getTable()}` {$whereSql}";
            return $this->ezDbDAO->findList($this->entityClazz, $sql, $params);
        }
    }

    /**
     * @param $ids
     * @return array<AbstractDO>
     * @throws ReflectionException
     */
    public function findByIds($ids) {
        /**
         * @var EzLocalCache $localCache
         */
        $localCache = CacheFactory::getInstance(CacheFactory::TYPE_MEM);
        $res = [];
        $idsNoCache = [];
        foreach ($ids as $id) {
            if(null != $localCache && $data = $localCache->get($this->entityClazz->getName().$id)){
                $res[] = unserialize($data);
            } else {
                $idsNoCache[] = $id;
            }
        }

        $resNoCache = [];
        if (!empty($idsNoCache)) {
            $resNoCache = $this->findList("where id in (:ids)", [":ids" => $idsNoCache]);
        }
        /**
         * @var AbstractDO $item
         */
        foreach ($resNoCache as $item) {
            null != $localCache && $localCache->set($this->entityClazz->getName().$item->id, serialize($item));
        }
        $res = array_merge($res, $resNoCache);
        array_multisort($res, array_column($res, "id"), SORT_ASC);
        return $res;
    }

    public function save(BaseDO $domain) {
        $refClass = new EzReflectionClass($domain);
        $annoItme = $refClass->getAnnoation(Clazz::get(IdGenerator::class));
        if ($annoItme instanceof AnnoItem) {
            /**
             * @var EzIdClient $idClient
             */
            $idClient = BeanFinder::get()->pull($annoItme->value);
            $domain->id = $idClient->nextId();
        }
        $domain->ver = 1;
        $date = EzDate::now();
        $domain->createTime = $date;
        $domain->updateTime = $date;
        $splitColumn = $this->splitColumn;
        return DB::get($this->database)->save($this->getTable($domain->$splitColumn ?? null), $domain->toArray());
    }

    public function update(BaseDO $domain) {
        if (is_null($domain->id)) {
            return false;
        }
        $domain->updateTime = EzDate::now();
        $ver = $domain->ver;
        $domain->ver++;
        $splitColumn = $this->splitColumn;
        $updateRes = DB::get($this->database)->update($this->getTable($domain->$splitColumn ?? null), $domain->toArray(), "id", "ver = $ver");
        /**
         * @var EzLocalCache $localCache
         */
        $localCache = CacheFactory::getInstance(CacheFactory::TYPE_MEM);
        $localCache->del($this->entityClazz->getName().$domain->id);
        return $updateRes;
    }

    private function getTable($splitValue = null) {
        if ($this->hasSplit) {
            DBC::assertNonNull($splitValue, "[DAO] getTableFail! this table has split strategy, but no splitvalue input!");
            if ("mod" == $this->splitModel) {
                return sprintf($this->table, $splitValue%$this->splitCnt);
            }
            DBC::assertTrue(true, "[DAO] getTableFail! this table has split strategy, but no splitmodel input!");
        }
        return $this->table;
    }
}
