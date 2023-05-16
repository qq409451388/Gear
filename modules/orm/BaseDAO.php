<?php

abstract class BaseDAO implements EzBean
{
    /**
     * @var Clazz $entityClazz
     */
    protected $entityClazz;
    protected $table;
    protected $database;

    public function __construct() {
        $this->entityClazz = $this->bindEntity();
        DBC::assertTrue($this->entityClazz->isSubClassOf(AbstractDO::class),
            "[DAO] create Fail!",0, GearShutDownException::class);
        /**
         * @var AnnoItem $annoItem
         */
        $annoItem = AnnoationRule::searchCertainlyRelationshipAnnoation($this->entityClazz->getName(), EntityBind::class);
        /**
         * @var EntityBind $anno
         */
        $anno = $annoItem->getValue();

        $this->table = $anno->getTable();
        $this->database = $anno->getDb();
    }

    abstract protected function bindEntity():Clazz;

    /**
     * @param $sql
     * @param $params
     * @return AbstractDO
     * @throws ReflectionException
     */
    public function findOne($sql, $params){
        $sql = "select * from `{$this->table}` {$sql} limit 1";
        $res = DB::get($this->database)->queryOne($sql, $params);
        if (empty($res)) {
            return null;
        }
        $className = $this->entityClazz->getName();
        return EzBeanUtils::createObject($res, $className);
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
        $data = $this->findOne("where id = :id", [":id" => $id]);
        if(empty($data)){
            return null;
        }
        null != $localCache && $localCache->set($this->entityClazz->getName().$id, serialize($data));
        return $data;
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
        return DB::get($this->database)->save($this->table, $domain->toArray());
    }

    public function update(BaseDO $domain) {
        if (is_null($domain->id)) {
            return false;
        }
        $domain->updateTime = EzDate::now();
        $ver = $domain->ver;
        $domain->ver++;
        $updateRes = DB::get($this->database)->update($this->table, $domain->toArray(), "id", "ver = $ver");
        /**
         * @var EzLocalCache $localCache
         */
        $localCache = CacheFactory::getInstance(CacheFactory::TYPE_MEM);
        $localCache->del($this->entityClazz->getName().$domain->id);
        return $updateRes;
    }
}
